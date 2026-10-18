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

class PortalEstudianteController extends Controller
{
    /**
     * Vista principal del Portal de Autogestión Estudiantil (Fase 6 CEFI).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $idEstudiante = $user->id;

        // Permite a los administradores previsualizar el portal de cualquier estudiante
        if ($user->id_rol == 1 && $request->has('preview_id')) {
            $idEstudiante = $request->get('preview_id');
            $user = Usuario::find($idEstudiante) ?: $user;
        }

        // 1. Cursos Matriculados Actuales (Activos)
        $matriculas = Matricula::with(['cursoActivo.planEstudio.programa', 'cursoActivo.profesor'])
            ->where('id_estudiante', $idEstudiante)
            ->where(function ($q) {
                $q->whereNull('estado')
                  ->orWhere('estado', '!=', 'Retirado');
            })
            ->get();

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

            if ($m->estado === 'Retirado') {
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
                    'profesor'        => $conv->institucion_origen,
                    'enlace_zoom'     => null,
                    'horario'         => 'Resolución: ' . ($conv->numero_resolucion ?: 'Aprobada'),
                    'moodle_course_id'=> null,
                ];
            }
        }

        $promedioPonderado = ($totalCreditosCursados > 0) ? round($sumaPonderada / $totalCreditosCursados, 2) : 0.00;

        // 3. Finanzas y Boletas de Matrícula
        $boletas = Boleta::where('id_usuario', $idEstudiante)
            ->where('estado', '!=', 'Anulada')
            ->orderBy('id', 'desc')
            ->get();

        $saldoPendienteTotal = $boletas->sum('saldo');
        $boletasPorFirmar = $boletas->where('requiere_firma', true)->where('firma_estudiante_aceptada', false);

        // 4. Expediente Digital del Estudiante
        $expediente = ExpedienteDigital::with('archivos')->where('id_usuario', $idEstudiante)->first();

        // 5. Bitácora de TCU
        $tcu = TcuBitacora::with('actividades')->where('usuario_id', $idEstudiante)->first();
        $horasTcuCompletadas = 0;
        if ($tcu && $tcu->actividades) {
            $horasTcuCompletadas = $tcu->actividades->sum('horas');
        }

        // 6. Oferta Académica del Período
        $ofertaCursos = CursoActivo::with(['planEstudio.programa', 'profesor'])
            ->where(function ($q) {
                $q->whereNull('check_terminado')
                  ->orWhere('check_terminado', false);
            })
            ->orderBy('id_curso_activo', 'desc')
            ->take(8)
            ->get();

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
        $expediente = ExpedienteDigital::with('usuario')->where('id_usuario', $idEstudiante)->first();

        if (!$expediente) {
            return back()->with('error', 'Aún no posees un Expediente Digital aperturado. Consulta con Registro Académico.');
        }

        $expController = app(ExpedienteController::class);
        return $expController->generarRecordPdf($expediente->id_expediente);
    }
}
