<?php

namespace App\Services;

use App\Models\CursoActivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

class MoodleGradebookService
{
    /**
     * Sincroniza las calificaciones desde el Gradebook de Moodle hacia Inbox BPM.
     *
     * @param int $id_curso ID del curso activo
     * @return array
     * @throws Exception
     */
    public static function sincronizar(int $id_curso): array
    {
        // 1. Obtener datos del curso activo
        $curso = CursoActivo::with(['planEstudio', 'profesor'])->findOrFail($id_curso);

        $moodle_course_id = intval($curso->id_moodle ?? ($curso->moodle_course_id ?? 0));
        if ($moodle_course_id <= 0) {
            throw new Exception("Este curso aún no tiene un Aula Virtual sincronizada en Moodle (ID de curso Moodle ausente). Por favor sincronice el curso primero con el botón de Moodle.");
        }

        // 2. Obtener Rubros de Evaluación del Sílabo Activo
        $prof_id = intval($curso->id_profesor ?? 0);
        $silabo = DB::table('silabos')
            ->where('id_plan', $curso->id_plan)
            ->orderByRaw('(id_profesor = ?) DESC', [$prof_id])
            ->orderBy('id_silabo', 'desc')
            ->first();

        if (!$silabo) {
            throw new Exception("No se encontró un sílabo registrado para este curso.");
        }

        $rubros = DB::table('silabo_evaluacion')
            ->where('id_silabo', $silabo->id_silabo)
            ->orderBy('id_evaluacion', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id_rubro' => intval($r->id_evaluacion),
                    'rubro' => trim($r->rubro),
                    'porcentaje' => floatval($r->porcentaje),
                    'tipo_moodle' => trim($r->tipo_moodle ?? ''),
                    'cantidad' => intval($r->cantidad ?? 1)
                ];
            })
            ->toArray();

        if (empty($rubros)) {
            throw new Exception("El sílabo de este curso no tiene rubros de evaluación configurados.");
        }

        // 3. Consultar Calificaciones en Moodle Bridge
        $bridge_url = env('MOODLE_BRIDGE_URL', 'https://unela.ac.cr/virtual/webservice/moodle_bridge.php');
        $auth_token = env('MOODLE_TOKEN', 'ef5bde9afb1fdd026330058ec405a30e');

        $response = Http::asForm()->timeout(60)->post($bridge_url, [
            'token' => $auth_token,
            'action' => 'get_course_grades',
            'courseid' => $moodle_course_id
        ]);

        if ($response->failed()) {
            throw new Exception("Error de red al conectar con Moodle Bridge: HTTP " . $response->status());
        }

        $moodle_data = $response->json();
        if (!$moodle_data || !isset($moodle_data['success']) || !$moodle_data['success']) {
            $msg = $moodle_data['message'] ?? 'Respuesta no válida del aula virtual.';
            throw new Exception("Moodle devolvió un mensaje: " . $msg);
        }

        $student_grades = $moodle_data['student_grades'] ?? [];
        if (empty($student_grades)) {
            throw new Exception("No se encontraron estudiantes matriculados o con calificaciones en Moodle para este curso.");
        }

        // 4. Cargar Matrículas Locales de Inbox
        $alumnos_inbox = DB::table('matriculas as m')
            ->join('usuarios as u', 'm.id_estudiante', '=', 'u.id')
            ->select('m.id_matricula', 'm.id_estudiante', 'u.nombre', 'u.apellidos', 'u.email', 'u.id_moodle', 'u.cedula')
            ->where('m.id_curso_activo', $id_curso)
            ->where(function ($q) use ($prof_id) {
                if ($prof_id > 0) {
                    $q->where('u.id', '!=', $prof_id);
                }
            })
            ->get();

        if ($alumnos_inbox->isEmpty()) {
            throw new Exception("No hay estudiantes matriculados en Inbox para este curso.");
        }

        // Helper de normalización para comparación de textos
        $normalizar = function ($str) {
            $str = mb_strtolower(trim((string)$str), 'UTF-8');
            return str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', '.'], ['a', 'e', 'i', 'o', 'u', 'n', ''], $str);
        };

        $alumnos_procesados = 0;
        $notas_insertadas = 0;
        $detalles_alumnos = [];

        foreach ($alumnos_inbox as $alum) {
            $email_inbox = strtolower(trim($alum->email ?? ''));
            $id_moodle_inbox = intval($alum->id_moodle ?? 0);
            $cedula_inbox = trim($alum->cedula ?? '');
            $id_mat = intval($alum->id_matricula);

            // Emparejar estudiante en Moodle
            $moodle_student = null;
            foreach ($student_grades as $m_stu) {
                if (!empty($email_inbox) && strtolower(trim($m_stu['email'] ?? '')) === $email_inbox) {
                    $moodle_student = $m_stu;
                    break;
                }
                if ($id_moodle_inbox > 0 && intval($m_stu['userid'] ?? 0) === $id_moodle_inbox) {
                    $moodle_student = $m_stu;
                    break;
                }
                if (!empty($cedula_inbox) && trim($m_stu['idnumber'] ?? '') === $cedula_inbox) {
                    $moodle_student = $m_stu;
                    break;
                }
            }

            if (!$moodle_student) {
                continue;
            }

            $alumnos_procesados++;
            $suma_final_obtenida = 0.0;
            $notas_del_alumno = [];

            // Mapear cada rubro del sílabo
            foreach ($rubros as $rubro) {
                $id_rubro = $rubro['id_rubro'];
                $porcentaje_max = $rubro['porcentaje'];
                $rubro_nombre_norm = $normalizar($rubro['rubro']);
                $tipo_moodle_rubro = strtolower(trim($rubro['tipo_moodle']));

                $calificaciones_coincidentes = [];

                if (!empty($moodle_student['grades'])) {
                    foreach ($moodle_student['grades'] as $item_id => $grade_info) {
                        if (($grade_info['itemtype'] ?? '') === 'course') continue;

                        $item_name_norm = $normalizar($grade_info['itemname'] ?? '');
                        $es_coincidencia = false;

                        // 1. Por tipo de actividad Moodle
                        if (!empty($tipo_moodle_rubro) && strpos($item_name_norm, $tipo_moodle_rubro) !== false) {
                            $es_coincidencia = true;
                        }

                        // 2. Por coincidencia exacta o substring del rubro
                        if (!$es_coincidencia) {
                            if (strpos($item_name_norm, $rubro_nombre_norm) !== false || strpos($rubro_nombre_norm, $item_name_norm) !== false) {
                                $es_coincidencia = true;
                            }
                        }

                        // 3. Por palabras clave
                        if (!$es_coincidencia) {
                            $keywords = explode(' ', $rubro_nombre_norm);
                            foreach ($keywords as $kw) {
                                if (strlen($kw) >= 4 && strpos($item_name_norm, $kw) !== false) {
                                    $es_coincidencia = true;
                                    break;
                                }
                            }
                        }

                        if ($es_coincidencia && isset($grade_info['percentage']) && $grade_info['percentage'] !== null) {
                            $calificaciones_coincidentes[] = floatval($grade_info['percentage']);
                        }
                    }
                }

                $nota_obtenida_rubro = 0.0;

                if (!empty($calificaciones_coincidentes)) {
                    $promedio_porcentaje = array_sum($calificaciones_coincidentes) / count($calificaciones_coincidentes);
                    $nota_obtenida_rubro = round(($promedio_porcentaje / 100.0) * $porcentaje_max, 2);
                } else {
                    if (count($rubros) === 1 && isset($moodle_student['course_total_percentage']) && $moodle_student['course_total_percentage'] !== null) {
                        $nota_obtenida_rubro = round((floatval($moodle_student['course_total_percentage']) / 100.0) * $porcentaje_max, 2);
                    } else {
                        // Preservar nota previamente ingresada en Inbox si existe
                        $prev = DB::table('notas_rubros')
                            ->where('id_matricula', $id_mat)
                            ->where('id_rubro', $id_rubro)
                            ->value('calificacion_obtenida');
                        if ($prev !== null) {
                            $nota_obtenida_rubro = floatval($prev);
                        }
                    }
                }

                $nota_obtenida_rubro = max(0.0, min($porcentaje_max, $nota_obtenida_rubro));
                $suma_final_obtenida += $nota_obtenida_rubro;
                $notas_del_alumno[$id_rubro] = $nota_obtenida_rubro;

                // Guardar en notas_rubros (upsert)
                DB::table('notas_rubros')->updateOrInsert(
                    ['id_matricula' => $id_mat, 'id_rubro' => $id_rubro],
                    ['calificacion_obtenida' => $nota_obtenida_rubro, 'fecha_registro' => now()]
                );
                $notas_insertadas++;
            }

            // Actualizar nota final oficial en matriculas
            $suma_final_obtenida = round($suma_final_obtenida, 2);
            DB::table('matriculas')
                ->where('id_matricula', $id_mat)
                ->update(['calificacion' => $suma_final_obtenida]);

            $detalles_alumnos[] = [
                'id_matricula' => $id_mat,
                'nombre' => trim($alum->nombre . ' ' . ($alum->apellidos ?? '')),
                'nota_final' => $suma_final_obtenida,
                'condicion' => ($suma_final_obtenida >= 70.0) ? 'Aprobado' : 'Reprobado',
                'rubros' => $notas_del_alumno
            ];
        }

        // Actualizar banderas de cierre en cursos_activos
        DB::table('cursos_activos')
            ->where('id_curso_activo', $id_curso)
            ->update([
                'check_calificaciones' => 1,
                'check_promedios' => 1
            ]);

        return [
            'total_procesados' => $alumnos_procesados,
            'notas_insertadas' => $notas_insertadas,
            'detalles' => $detalles_alumnos
        ];
    }
}
