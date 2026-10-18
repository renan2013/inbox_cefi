<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Matricula;
use App\Models\CursoActivo;
use App\Models\PlanEstudio;
use App\Models\Programa;
use App\Models\Boleta;
use App\Models\ExpedienteDigital;
use App\Models\TcuBitacora;
use App\Models\Convalidacion;
use App\Services\RecordAcademicoPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class PortalEstudianteController extends Controller
{
    /**
     * Asegura que las tablas y columnas necesarias existan en el servidor.
     */
    protected function asegurarTablas()
    {
        try {
            if (!Schema::hasTable('convalidaciones') || !Schema::hasColumn('matriculas', 'estado')) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning("Auto-migrate warning in Portal: " . $e->getMessage());
        }
    }

    /**
     * Vista principal del Portal de Autogestión Estudiantil (Fase 6 CEFI).
     */
    public function index(Request $request)
    {
        $this->asegurarTablas();

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $idEstudiante = $user->id;

        // Permite a los administradores previsualizar el portal de cualquier estudiante
        if ((int)$user->id_rol === 1 && $request->has('preview_id')) {
            $previewUser = Usuario::find($request->get('preview_id'));
            if ($previewUser) {
                $user = $previewUser;
                $idEstudiante = $user->id;
            }
        }

        // 1. Cursos Matriculados Actuales (Activos)
        $matriculas = collect();
        try {
            if (Schema::hasTable('matriculas')) {
                $matriculaQuery = Matricula::with(['cursoActivo.planEstudio.programa', 'cursoActivo.profesor'])
                    ->where('id_estudiante', $idEstudiante);

                if (Schema::hasColumn('matriculas', 'estado')) {
                    $matriculaQuery->where(function ($q) {
                        $q->whereNull('estado')
                          ->orWhere('estado', '!=', 'Retirado');
                    });
                }

                $matriculas = $matriculaQuery->get();
            }
        } catch (\Throwable $e) {
            Log::error("Error cargando matriculas estudiante: " . $e->getMessage());
            $matriculas = collect();
        }

        // 2. Historial de Calificaciones y Récord
        $cursosAprobados = 0;
        $cursosReprobados = 0;
        $cursosEnCurso = 0;
        $totalCreditosAprobados = 0;
        $totalCreditosCursados = 0;
        $sumaPonderada = 0;

        $historialCursos = [];
        foreach ($matriculas as $m) {
            $ca = $m->cursoActivo;
            $pe = $ca ? $ca->planEstudio : null;
            $cred = (float)($pe ? ($pe->creditos ?: 3) : 3);
            $nota = $m->calificacion;
            $estadoMat = $m->estado ?? null;

            if ($estadoMat === 'Retirado') {
                $estado = 'RETIRADO';
            } elseif ($nota !== null && $nota !== '') {
                $notaNum = (float)$nota;
                $totalCreditosCursados += $cred;
                $sumaPonderada += ($notaNum * $cred);

                if ($notaNum >= 70.0) {
                    $estado = 'APROBADO';
                    $totalCreditosAprobados += $cred;
                    $cursosAprobados++;
                } else {
                    $estado = 'REPROBADO';
                    $cursosReprobados++;
                }
            } else {
                $estado = 'EN CURSO';
                $cursosEnCurso++;
            }

            $historialCursos[] = [
                'materia'         => $pe ? $pe->materia : "Curso #{$m->id_curso_activo}",
                'codigo'          => $pe ? $pe->codigo : 'S/C',
                'periodo'         => $ca ? $ca->periodo : 'Activo',
                'creditos'        => $cred,
                'calificacion'    => $nota,
                'estado'          => $estado,
                'profesor'        => ($ca && $ca->profesor) ? "{$ca->profesor->nombre} {$ca->profesor->apellidos}" : 'Docente asignado',
                'enlace_zoom'     => $ca ? $ca->enlace_zoom : null,
                'horario'         => $ca ? ($ca->horario ?: ($ca->hora_inicio ? "{$ca->hora_inicio} - {$ca->hora_final}" : 'Virtual Asincrónico')) : 'Virtual',
                'moodle_course_id'=> $ca ? $ca->moodle_course_id : null,
            ];
        }

        // Convalidaciones Aprobadas del Estudiante
        $convalidaciones = collect();
        try {
            if (Schema::hasTable('convalidaciones')) {
                $convalidaciones = Convalidacion::with('planEstudio')
                    ->where('id_estudiante', $idEstudiante)
                    ->where('estado', 'Aprobada')
                    ->get();

                foreach ($convalidaciones as $conv) {
                    $credConv = (float)($conv->creditos_convalidados ?: 3.0);
                    $totalCreditosAprobados += $credConv;
                    $cursosAprobados++;

                    $historialCursos[] = [
                        'materia'         => ($conv->planEstudio ? $conv->planEstudio->materia : $conv->materia_origen) . ' (Homologado)',
                        'codigo'          => $conv->planEstudio ? $conv->planEstudio->codigo : ($conv->codigo_origen ?: 'CONV'),
                        'periodo'         => 'CONVALIDACIÓN',
                        'creditos'        => $credConv,
                        'calificacion'    => $conv->calificacion_homologada ? number_format($conv->calificacion_homologada, 1) : 'AP',
                        'estado'          => 'CONVALIDADO',
                        'profesor'        => $conv->institucion_origen ?: 'Institución Universitaria Externa',
                        'enlace_zoom'     => null,
                        'horario'         => 'Resolución: ' . ($conv->numero_resolucion ?: 'Aprobada'),
                        'moodle_course_id'=> null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Error convalidaciones estudiante: " . $e->getMessage());
            $convalidaciones = collect();
        }

        $promedioPonderado = ($totalCreditosCursados > 0) ? round($sumaPonderada / $totalCreditosCursados, 2) : 0.00;

        // 3. Finanzas y Boletas de Matrícula (según id_estudiante en boletas)
        $boletas = collect();
        $saldoPendienteTotal = 0.0;
        $boletasPorFirmar = collect();
        try {
            if (Schema::hasTable('boletas')) {
                $boletaQuery = Boleta::where(function ($q) use ($idEstudiante) {
                    if (Schema::hasColumn('boletas', 'id_estudiante')) {
                        $q->where('id_estudiante', $idEstudiante);
                    } elseif (Schema::hasColumn('boletas', 'id_usuario')) {
                        $q->where('id_usuario', $idEstudiante);
                    }
                });

                if (Schema::hasColumn('boletas', 'estado')) {
                    $boletaQuery->where('estado', '!=', 'Anulada');
                }

                $boletas = $boletaQuery->orderBy('id', 'desc')->get();
                $saldoPendienteTotal = (float)$boletas->sum(function ($b) {
                    return (float)($b->saldo_pendiente ?? $b->saldo ?? 0);
                });

                $boletasPorFirmar = $boletas->filter(function ($b) {
                    return !empty($b->token_firma) && empty($b->ruta_firma) && empty($b->fecha_firma);
                });
            }
        } catch (\Throwable $e) {
            Log::warning("Error boletas estudiante: " . $e->getMessage());
            $boletas = collect();
            $boletasPorFirmar = collect();
        }

        // 4. Expediente Digital del Estudiante
        $expediente = null;
        try {
            if (Schema::hasTable('expedientes_digitales')) {
                $expediente = ExpedienteDigital::with('archivos')->where('id_usuario', $idEstudiante)->first();
            }
        } catch (\Throwable $e) {
            Log::warning("Error expediente estudiante: " . $e->getMessage());
            $expediente = null;
        }

        // 5. Bitácora de TCU
        $tcu = null;
        $horasTcuCompletadas = 0;
        try {
            if (Schema::hasTable('tcu_bitacoras')) {
                $tcu = TcuBitacora::with('actividades')->where('usuario_id', $idEstudiante)->first();
                if ($tcu && $tcu->actividades) {
                    $horasTcuCompletadas = (float)$tcu->actividades->sum(function($a) {
                        return $a->cantidad_horas ?? $a->horas ?? 0;
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Error TCU estudiante: " . $e->getMessage());
            $tcu = null;
        }

        // 6. Oferta Académica del Período
        $ofertaCursos = collect();
        try {
            if (Schema::hasTable('cursos_activos')) {
                $ofertaQuery = CursoActivo::with(['planEstudio.programa', 'profesor']);
                if (Schema::hasColumn('cursos_activos', 'check_terminado')) {
                    $ofertaQuery->where(function ($q) {
                        $q->whereNull('check_terminado')
                          ->orWhere('check_terminado', false);
                    });
                }
                $ofertaCursos = $ofertaQuery->orderBy('id_curso_activo', 'desc')->take(8)->get();
            }
        } catch (\Throwable $e) {
            Log::warning("Error oferta cursos: " . $e->getMessage());
            $ofertaCursos = collect();
        }

        return view('estudiantes.portal', compact(
            'user',
            'matriculas',
            'historialCursos',
            'cursosAprobados',
            'cursosReprobados',
            'cursosEnCurso',
            'totalCreditosAprobados',
            'promedioPonderado',
            'boletas',
            'saldoPendienteTotal',
            'boletasPorFirmar',
            'expediente',
            'tcu',
            'horasTcuCompletadas',
            'convalidaciones',
            'ofertaCursos'
        ));
    }

    /**
     * Permite al estudiante descargar directamente su Récord Académico oficial en PDF.
     */
    public function descargarMiRecordPdf()
    {
        $idEstudiante = Auth::id();
        $expediente = null;
        if (Schema::hasTable('expedientes_digitales')) {
            $expediente = ExpedienteDigital::with('usuario')->where('id_usuario', $idEstudiante)->first();
        }

        if (!$expediente) {
            return back()->with('error', 'Aún no posees un Expediente Digital aperturado. Consulta con Registro Académico.');
        }

        $expController = app(ExpedienteController::class);
        return $expController->generarRecordPdf($expediente->id_expediente);
    }

    /**
     * Permite al estudiante descargar directamente su Ficha de Expediente Digital oficial en PDF.
     */
    public function descargarMiExpedientePdf()
    {
        $idEstudiante = Auth::id();
        $expediente = null;
        if (Schema::hasTable('expedientes_digitales')) {
            $expediente = ExpedienteDigital::with(['usuario', 'archivos'])->where('id_usuario', $idEstudiante)->first();
        }

        if (!$expediente) {
            return back()->with('error', 'Aún no posees un Expediente Digital aperturado. Consulta con Registro Académico.');
        }

        $expController = app(ExpedienteController::class);
        return $expController->generarExpedientePdf($expediente->id_expediente);
    }
}
