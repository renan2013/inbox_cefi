<?php

namespace App\Http\Controllers;

use App\Models\Graduacion;
use App\Models\Usuario;
use App\Models\Programa;
use App\Models\PlanEstudio;
use App\Models\Matricula;
use App\Models\Convalidacion;
use App\Models\TcuBitacora;
use App\Models\Boleta;
use App\Models\ExpedienteDigital;
use App\Services\DictamenGraduacionPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Pagination\LengthAwarePaginator;

class GraduacionController extends Controller
{
    /**
     * Asegura que las tablas necesarias existan antes de ejecutar consultas.
     */
    protected function asegurarTablas()
    {
        if (!Schema::hasTable('graduaciones')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Auto-migrate error: " . $e->getMessage());
            }
        }
    }

    /**
     * Listado general de postulaciones a graduación y estado de auditoría.
     */
    public function index(Request $request)
    {
        $this->asegurarTablas();

        $busqueda = trim($request->get('q', ''));
        $estadoFiltro = $request->get('estado', '');
        $convocatoriaFiltro = $request->get('convocatoria', '');

        if (!Schema::hasTable('graduaciones')) {
            return view('graduaciones.index', [
                'graduaciones'       => new LengthAwarePaginator([], 0, 20),
                'kpiTotal'           => 0,
                'kpiAprobados'       => 0,
                'kpiAuditoria'       => 0,
                'kpiCumLaude'        => 0,
                'convocatorias'      => collect(),
                'busqueda'           => '',
                'estadoFiltro'       => '',
                'convocatoriaFiltro' => ''
            ]);
        }

        $query = Graduacion::with(['estudiante', 'programa', 'expediente', 'autorizador']);

        if (!empty($estadoFiltro)) {
            $query->where('estado', $estadoFiltro);
        }

        if (!empty($convocatoriaFiltro)) {
            $query->where('convocatoria', $convocatoriaFiltro);
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('convocatoria', 'like', "%{$busqueda}%")
                  ->orWhere('numero_titulo', 'like', "%{$busqueda}%")
                  ->orWhereHas('estudiante', function ($u) use ($busqueda) {
                      $u->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('cedula', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('programa', function ($p) use ($busqueda) {
                      $p->where('nombre_programa', 'like', "%{$busqueda}%");
                  });
            });
        }

        $graduaciones = $query->orderBy('id_graduacion', 'desc')->paginate(20)->withQueryString();

        // KPIs
        $kpiTotal = Graduacion::count();
        $kpiAprobados = Graduacion::where('estado', 'Aprobado para Graduación')->orWhere('estado', 'Graduado')->count();
        $kpiAuditoria = Graduacion::where('estado', 'En Auditoría')->count();
        $kpiCumLaude = Graduacion::where('mencion_honor', '!=', 'Ninguna')->count();

        $convocatorias = Graduacion::distinct()->pluck('convocatoria')->filter()->values();

        return view('graduaciones.index', compact(
            'graduaciones', 'kpiTotal', 'kpiAprobados', 'kpiAuditoria', 'kpiCumLaude',
            'convocatorias', 'busqueda', 'estadoFiltro', 'convocatoriaFiltro'
        ));
    }

    /**
     * Formulario para postular a un estudiante para graduación.
     */
    public function create(Request $request)
    {
        $estudiantes = Usuario::where('id_rol', 3)
            ->orWhereHas('expediente')
            ->orderBy('apellidos', 'asc')
            ->get();

        $programas = Programa::orderBy('nombre_programa', 'asc')->get();

        return view('graduaciones.create', compact('estudiantes', 'programas'));
    }

    /**
     * Guarda la postulación y ejecuta la auditoría automática inicial.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_estudiante'    => 'required|exists:usuarios,id',
            'id_programa'      => 'required|exists:programas,id_programa',
            'convocatoria'     => 'required|string|max:100',
            'fecha_graduacion' => 'nullable|date',
        ]);

        $estudiante = Usuario::with('expediente')->findOrFail($request->id_estudiante);
        $idExpediente = $estudiante->expediente ? $estudiante->expediente->id_expediente : null;

        $graduacion = Graduacion::create([
            'id_estudiante'    => $request->id_estudiante,
            'id_programa'      => $request->id_programa,
            'id_expediente'    => $idExpediente,
            'convocatoria'     => $request->convocatoria,
            'fecha_graduacion' => $request->fecha_graduacion,
            'mencion_honor'    => $request->mencion_honor ?: 'Ninguna',
            'estado'           => 'En Auditoría',
            'observaciones'    => $request->observaciones,
        ]);

        // Ejecutar auditoría inicial
        $this->ejecutarAuditoriaCalculada($graduacion);

        return redirect()->route('graduaciones.auditar', $graduacion->id_graduacion)
            ->with('success', 'Postulación registrada. Se ha generado la auditoría preliminar de requisitos.');
    }

    /**
     * Vista 360° de auditoría académica con semáforo de requisitos.
     */
    public function auditar($id)
    {
        $graduacion = Graduacion::with(['estudiante.expediente', 'programa', 'autorizador'])->findOrFail($id);
        $auditoria = $this->ejecutarAuditoriaCalculada($graduacion);

        return view('graduaciones.auditar', compact('graduacion', 'auditoria'));
    }

    /**
     * Aprueba o formaliza la graduación, asignando folios y títulos.
     */
    public function aprobar(Request $request, $id)
    {
        $request->validate([
            'estado'        => 'required|in:Aprobado para Graduación,Graduado,Rechazado,En Auditoría',
            'numero_titulo' => 'nullable|string|max:50',
            'numero_folio'  => 'nullable|string|max:50',
            'numero_tomo'   => 'nullable|string|max:50',
            'numero_acta'   => 'nullable|string|max:50',
            'mencion_honor' => 'required|in:Ninguna,Cum Laude,Magna Cum Laude,Summa Cum Laude',
        ]);

        $graduacion = Graduacion::findOrFail($id);
        $graduacion->estado = $request->estado;
        $graduacion->numero_titulo = $request->numero_titulo;
        $graduacion->numero_folio = $request->numero_folio;
        $graduacion->numero_tomo = $request->numero_tomo;
        $graduacion->numero_acta = $request->numero_acta;
        $graduacion->mencion_honor = $request->mencion_honor;
        $graduacion->observaciones = $request->observaciones;

        if ($request->estado === 'Aprobado para Graduación' || $request->estado === 'Graduado') {
            $graduacion->autorizado_por = Auth::id();
            $graduacion->fecha_autorizacion = now();
        }

        $graduacion->save();

        return back()->with('success', "Estado de graduación actualizado a: {$request->estado}");
    }

    /**
     * Genera el Dictamen Oficial de Graduación en PDF.
     */
    public function generarDictamenPdf($id)
    {
        $graduacion = Graduacion::with(['estudiante', 'programa'])->findOrFail($id);
        $auditoria = $this->ejecutarAuditoriaCalculada($graduacion);

        $pdfContent = DictamenGraduacionPdfService::generarPdf($graduacion, $auditoria);
        $fileName = "Dictamen_Graduacion_CEFI_{$graduacion->id_graduacion}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Elimina el trámite de graduación.
     */
    public function destroy($id)
    {
        $graduacion = Graduacion::findOrFail($id);
        $graduacion->delete();

        return redirect()->route('graduaciones.index')
            ->with('success', 'Trámite de graduación eliminado con éxito.');
    }

    /**
     * Método central de auditoría de los 4 requisitos.
     */
    public function ejecutarAuditoriaCalculada(Graduacion $graduacion): array
    {
        $idEstudiante = $graduacion->id_estudiante;
        $idPrograma = $graduacion->id_programa;

        // 1. Auditoría del Plan de Estudios
        $materiasPlan = PlanEstudio::where('id_programa', $idPrograma)->get();
        $totalMaterias = $materiasPlan->count();
        $totalCreditosRequeridos = $materiasPlan->sum('creditos');

        // Materias aprobadas en cursos ordinarios
        $matriculasAprobadas = Matricula::where('id_estudiante', $idEstudiante)
            ->where(function($q) {
                $q->where('calificacion', '>=', 70.0)
                  ->orWhere('estado', 'Aprobado');
            })
            ->with('cursoActivo')
            ->get();

        $idsPlanAprobados = $matriculasAprobadas->pluck('cursoActivo.id_plan')->filter()->toArray();

        // Convalidaciones aprobadas
        $convalidacionesAprobadas = Convalidacion::where('id_estudiante', $idEstudiante)
            ->where('estado', 'Aprobada')
            ->whereNotNull('id_plan')
            ->get();

        $idsPlanConvalidados = $convalidacionesAprobadas->pluck('id_plan')->toArray();
        $totalCreditosConvalidados = $convalidacionesAprobadas->sum('creditos_convalidados');

        // Unión de aprobadas + convalidadas
        $todosAprobados = array_unique(array_merge($idsPlanAprobados, $idsPlanConvalidados));
        $materiasAprobadasCount = 0;
        $creditosAprobados = 0;
        $sumaNotas = 0;
        $totalNotasCount = 0;

        foreach ($materiasPlan as $m) {
            if (in_array($m->id_plan, $todosAprobados)) {
                $materiasAprobadasCount++;
                $creditosAprobados += ($m->creditos ?: 3);
            }
        }

        foreach ($matriculasAprobadas as $ma) {
            if ($ma->calificacion !== null) {
                $sumaNotas += (float)$ma->calificacion;
                $totalNotasCount++;
            }
        }

        $promedioCalculado = ($totalNotasCount > 0) ? round($sumaNotas / $totalNotasCount, 2) : 0.00;
        $cumplePlan = ($totalMaterias > 0) ? ($materiasAprobadasCount >= $totalMaterias) : false;

        // 2. Auditoría de TCU
        $tcu = TcuBitacora::where('usuario_id', $idEstudiante)->first();
        $cumpleTcu = false;
        $tcuDetalle = 'Sin registro de TCU';
        if ($tcu) {
            $cumpleTcu = ($tcu->estado === 'Finalizado' || $tcu->estado === 'Aprobado');
            $tcuDetalle = "Proyecto: {$tcu->nombre_proyecto} (Estado: {$tcu->estado})";
        }

        // 3. Auditoría Financiera
        $boletasPendientes = Boleta::where('id_usuario', $idEstudiante)
            ->where('estado', '!=', 'Anulada')
            ->where(function($q) {
                $q->where('saldo', '>', 0)
                  ->orWhere('estado_pago', '!=', 'Pagada');
            })
            ->count();

        $cumpleFinanzas = ($boletasPendientes === 0);
        $finanzasDetalle = $cumpleFinanzas 
            ? 'Solvencia confirmada: Sin saldos ni morosidad' 
            : "Tiene {$boletasPendientes} boleta(s) con saldo pendiente";

        // 4. Auditoría de Expediente Digital
        $expediente = ExpedienteDigital::where('id_usuario', $idEstudiante)->first();
        $cumpleExpediente = false;
        $expedienteDetalle = 'Expediente digital no aperturado';
        if ($expediente) {
            $cumpleExpediente = ($expediente->estado === 'Aprobado' || $expediente->porcentaje_completitud >= 80);
            $expedienteDetalle = "Expediente #{$expediente->id_expediente} al {$expediente->porcentaje_completitud}% ({$expediente->estado})";
        }

        // Actualizar el modelo graduación con la auditoría
        $graduacion->cumplio_plan_estudios = $cumplePlan;
        $graduacion->cumplio_tcu = $cumpleTcu;
        $graduacion->cumplio_financiero = $cumpleFinanzas;
        $graduacion->cumplio_expediente = $cumpleExpediente;
        $graduacion->promedio_ponderado_final = $promedioCalculado;
        $graduacion->total_creditos_aprobados = $creditosAprobados;
        $graduacion->save();

        return [
            'materias_totales'    => $totalMaterias,
            'materias_aprobadas'  => $materiasAprobadasCount,
            'creditos_requeridos' => $totalCreditosRequeridos,
            'creditos_aprobados'  => $creditosAprobados,
            'promedio'            => $promedioCalculado,
            'tcu_detalle'         => $tcuDetalle,
            'finanzas_detalle'    => $finanzasDetalle,
            'expediente_detalle'  => $expedienteDetalle,
            'todos_los_requisitos'=> ($cumplePlan && $cumpleTcu && $cumpleFinanzas && $cumpleExpediente),
        ];
    }
}
