<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\TareaNotificationService;

class TareaController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva tarea.
     */
    public function create(Request $request)
    {
        $plantilla_id = $request->input('plantilla_id');
        
        $plantilla_titulo = '';
        $plantilla_descripcion = '';
        $plantilla_prioridad = 'media';

        if ($plantilla_id && is_numeric($plantilla_id)) {
            $plantilla = DB::table('tarea_plantillas')->where('id', $plantilla_id)->first();
            if ($plantilla) {
                $plantilla_titulo = $plantilla->titulo ?? '';
                $plantilla_descripcion = $plantilla->descripcion ?? '';
                $plantilla_prioridad = $plantilla->prioridad_default ?? 'media';
            }
        }

        // Obtener listas de apoyo
        $usuarios = DB::table('usuarios')->orderBy('nombre', 'asc')->get();
        $etiquetas = DB::table('etiquetas')->orderBy('nombre', 'asc')->get();
        
        $plantillas = [];
        if (Schema::hasTable('tarea_plantillas')) {
            $plantillas = DB::table('tarea_plantillas')->orderBy('titulo', 'asc')->get();
        }

        $cursos_activos = [];
        if (Schema::hasTable('cursos_activos')) {
            $cursos_activos = DB::table('cursos_activos as ca')
                ->join('plan_estudios as pe', 'ca.id_plan', '=', 'pe.id_plan')
                ->select('ca.id_curso_activo', 'pe.materia', 'pe.codigo', 'ca.periodo')
                ->orderBy('pe.materia', 'asc')
                ->get();
        }

        return view('tareas.crear', compact(
            'plantilla_id',
            'plantilla_titulo',
            'plantilla_descripcion',
            'plantilla_prioridad',
            'usuarios',
            'etiquetas',
            'plantillas',
            'cursos_activos'
        ));
    }

    /**
     * Procesa la creación de una nueva tarea.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridad' => 'required|string',
        ]);

        $titulo = trim($request->input('titulo'));
        $descripcion = trim($request->input('descripcion', ''));
        $prioridad = $request->input('prioridad', 'media');
        $fecha_creacion = $request->input('fecha_creacion') ?: date('Y-m-d H:i:s');
        $fecha_vencimiento = $request->input('fecha_vencimiento') ?: null;
        $id_curso_activo = $request->input('id_curso_activo') ?: null;
        $id_creador = Auth::id();

        // 1. Insertar tarea
        $taskData = [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'estado' => 'pendiente',
            'fecha_creacion' => $fecha_creacion,
            'fecha_vencimiento' => $fecha_vencimiento,
            'id_creador' => $id_creador,
        ];

        if ($id_curso_activo) {
            $taskData['id_curso_activo'] = $id_curso_activo;
        }

        $id_tarea = DB::table('tareas')->insertGetId($taskData);

        // 2. Asignar responsables
        $asignados = [];
        if ($request->has('id_asignado')) {
            $asignados = (array) $request->input('id_asignado');
            foreach ($asignados as $id_user) {
                if (!empty($id_user)) {
                    DB::table('tarea_asignaciones')->insert([
                        'id_tarea' => $id_tarea,
                        'id_usuario' => $id_user,
                    ]);
                }
            }
        }

        // 3. Asignar etiquetas / categorías
        if ($request->has('etiquetas')) {
            $tags = (array) $request->input('etiquetas');
            foreach ($tags as $id_tag) {
                if (!empty($id_tag)) {
                    DB::table('tarea_etiquetas')->insert([
                        'id_tarea' => $id_tarea,
                        'id_etiqueta' => $id_tag,
                    ]);
                }
            }
        }

        // 4. Subida de archivos adjuntos
        if ($request->hasFile('adjuntos')) {
            foreach ($request->file('adjuntos') as $file) {
                if ($file->isValid()) {
                    $nombreOriginal = $file->getClientOriginalName();
                    $mimeType = $file->getClientMimeType();
                    $tamano = $file->getSize();
                    $nombreServidor = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $destDir = public_path('uploads/tareas');
                    if (!is_dir($destDir)) {
                        @mkdir($destDir, 0755, true);
                    }
                    $file->move($destDir, $nombreServidor);

                    if (Schema::hasTable('adjuntos')) {
                        DB::table('adjuntos')->insert([
                            'id_tarea' => $id_tarea,
                            'nombre_original' => $nombreOriginal,
                            'nombre_servidor' => $nombreServidor,
                            'ruta_archivo' => 'uploads/tareas/' . $nombreServidor,
                            'tipo_mime' => $mimeType,
                            'tamano' => $tamano,
                            'id_usuario_subida' => $id_creador,
                        ]);
                    }
                }
            }
        }

        // 5. Notificar por correo electrónico a los responsables asignados y al creador
        $notifResult = TareaNotificationService::notificarCreacion((int) $id_tarea, $asignados, Auth::user());

        $mensajeExito = 'Tarea creada con éxito.';
        $details = $notifResult['details'] ?? [];
        if (count($details) === 1) {
            $d = $details[0];
            $mensajeExito .= " El usuario {$d['nombre']} ha sido notificado a su correo {$d['email']} con éxito.";
        } elseif (count($details) > 1) {
            $lista = array_map(fn ($d) => "{$d['nombre']} ({$d['email']})", $details);
            $mensajeExito .= ' Los siguientes usuarios han sido notificados a sus correos con éxito: ' . implode(', ', $lista) . '.';
        } elseif (!empty($notifResult['errors'])) {
            return redirect()->route('dashboard')
                ->with('success', $mensajeExito)
                ->with('warning', 'La tarea se guardó, pero hubo un problema al enviar el correo SMTP: ' . $notifResult['errors'][0]);
        }

        return redirect()->route('dashboard')->with('success', $mensajeExito);
    }

    /**
     * Vista para seleccionar plantilla de tarea antes de crear.
     */
    public function seleccionarPlantilla(Request $request)
    {
        $plantillas = [];
        if (Schema::hasTable('tarea_plantillas')) {
            $plantillas = DB::table('tarea_plantillas')->orderBy('titulo', 'asc')->get();
        }

        return view('tareas.seleccionar_plantilla', compact('plantillas'));
    }

    /**
     * Muestra la vista para gestionar plantillas de tareas.
     */
    public function gestionarPlantillas(Request $request)
    {
        $plantillas = [];
        if (Schema::hasTable('tarea_plantillas')) {
            $plantillas = DB::table('tarea_plantillas')->orderBy('fecha_creacion', 'desc')->get();
        }

        return view('tareas.gestionar_plantillas', compact('plantillas'));
    }

    /**
     * Guarda una nueva plantilla de tarea.
     */
    public function storePlantilla(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridad_default' => 'required|string',
        ]);

        $titulo = trim($request->input('titulo'));
        $descripcion = trim($request->input('descripcion', ''));
        $prioridad = $request->input('prioridad_default', 'media');
        $id_creador = Auth::id();

        DB::table('tarea_plantillas')->insert([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad_default' => $prioridad,
            'id_creador' => $id_creador,
        ]);

        return redirect()->route('tareas.plantillas.gestionar')->with('success', 'Plantilla creada con éxito.');
    }

    /**
     * Muestra el formulario para editar una plantilla de tarea existente.
     */
    public function editarPlantilla($id)
    {
        $plantilla = DB::table('tarea_plantillas')->where('id', $id)->first();
        if (!$plantilla) {
            return redirect()->route('tareas.plantillas.gestionar')->with('error', 'Plantilla no encontrada.');
        }

        return view('tareas.editar_plantilla', compact('plantilla'));
    }

    /**
     * Actualiza una plantilla de tarea existente.
     */
    public function updatePlantilla(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'prioridad_default' => 'required|string',
        ]);

        $titulo = trim($request->input('titulo'));
        $descripcion = trim($request->input('descripcion', ''));
        $prioridad = $request->input('prioridad_default', 'media');

        DB::table('tarea_plantillas')->where('id', $id)->update([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad_default' => $prioridad,
        ]);

        return redirect()->route('tareas.plantillas.gestionar')->with('success', 'Plantilla actualizada exitosamente.');
    }

    /**
     * Elimina una plantilla de tarea.
     */
    public function destroyPlantilla(Request $request, $id)
    {
        DB::table('tarea_plantillas')->where('id', $id)->delete();
        return redirect()->route('tareas.plantillas.gestionar')->with('success', 'Plantilla eliminada correctamente.');
    }

    /**
     * Vista General Interactiva de Diagrama de Gantt Colaborativo (Gantt Tareas).
     */
    public function gantt(Request $request)
    {
        $filtroAnio      = $request->input('anio', date('Y'));
        $filtroEstado    = $request->input('estado', '');
        $filtroPrioridad = $request->input('prioridad', '');
        $filtroUsuario   = $request->input('usuario', '');
        $filtroEtiqueta  = $request->input('etiqueta', '');

        $usuarios  = Schema::hasTable('usuarios') ? DB::table('usuarios')->orderBy('nombre')->get() : collect();
        $etiquetas = Schema::hasTable('etiquetas') ? DB::table('etiquetas')->orderBy('nombre')->get() : collect();

        $query = DB::table('tareas as t')
            ->leftJoin('usuarios as u_creador', 't.id_creador', '=', 'u_creador.id')
            ->where(function ($q) {
                $q->whereNull('t.descripcion')
                  ->orWhere('t.descripcion', 'not like', 'Registro de asistencia correspondiente a la Clase%');
            })
            ->select('t.*', 'u_creador.nombre as creador_nombre', 'u_creador.apellidos as creador_apellidos');

        if ($filtroAnio !== '' && $filtroAnio !== 'todos') {
            $query->where(function ($q) use ($filtroAnio) {
                $q->whereYear('t.fecha_creacion', $filtroAnio)
                  ->orWhereYear('t.fecha_vencimiento', $filtroAnio);
            });
        }
        if ($filtroEstado !== '') {
            $query->where('t.estado', $filtroEstado);
        }
        if ($filtroPrioridad !== '') {
            $query->where('t.prioridad', $filtroPrioridad);
        }
        if ($filtroUsuario !== '' && Schema::hasTable('tarea_asignaciones')) {
            $query->whereExists(function ($sub) use ($filtroUsuario) {
                $sub->select(DB::raw(1))
                    ->from('tarea_asignaciones as ta')
                    ->whereColumn('ta.id_tarea', 't.id')
                    ->where('ta.id_usuario', $filtroUsuario);
            });
        }
        if ($filtroEtiqueta !== '' && Schema::hasTable('tarea_etiquetas')) {
            $query->whereExists(function ($sub) use ($filtroEtiqueta) {
                $sub->select(DB::raw(1))
                    ->from('tarea_etiquetas as te')
                    ->whereColumn('te.id_tarea', 't.id')
                    ->where('te.id_etiqueta', $filtroEtiqueta);
            });
        }

        $tareasRaw = $query->orderBy('t.fecha_creacion', 'asc')->get();

        // Mapear asignados y etiquetas por tarea
        $asignadosPorTarea = [];
        if (Schema::hasTable('tarea_asignaciones')) {
            $rows = DB::table('tarea_asignaciones as ta')
                ->join('usuarios as u', 'ta.id_usuario', '=', 'u.id')
                ->select('ta.id_tarea', 'u.id', 'u.nombre', 'u.apellidos', 'u.email')
                ->get();
            foreach ($rows as $r) {
                $asignadosPorTarea[$r->id_tarea][] = $r;
            }
        }

        $etiquetasPorTarea = [];
        if (Schema::hasTable('tarea_etiquetas') && Schema::hasTable('etiquetas')) {
            $rows = DB::table('tarea_etiquetas as te')
                ->join('etiquetas as e', 'te.id_etiqueta', '=', 'e.id')
                ->select('te.id_tarea', 'e.id', 'e.nombre')
                ->get();
            foreach ($rows as $r) {
                $etiquetasPorTarea[$r->id_tarea][] = $r;
            }
        }

        $tareas = $tareasRaw->map(function ($t) use ($asignadosPorTarea, $etiquetasPorTarea) {
            $inicio = !empty($t->fecha_creacion) ? substr((string)$t->fecha_creacion, 0, 10) : date('Y-m-d');
            $fin    = !empty($t->fecha_vencimiento) ? substr((string)$t->fecha_vencimiento, 0, 10) : date('Y-m-d', strtotime($inicio . ' +7 days'));
            if ($fin < $inicio) {
                $fin = $inicio;
            }
            $t->fecha_inicio_gantt = $inicio;
            $t->fecha_fin_gantt    = $fin;
            $t->asignados          = $asignadosPorTarea[$t->id] ?? [];
            $t->etiquetas_lista    = $etiquetasPorTarea[$t->id] ?? [];
            $t->progreso           = $t->estado === 'completada' ? 100 : ($t->estado === 'en_proceso' ? 55 : 15);
            return $t;
        });

        return view('tareas.gantt', compact(
            'tareas',
            'usuarios',
            'etiquetas',
            'filtroAnio',
            'filtroEstado',
            'filtroPrioridad',
            'filtroUsuario',
            'filtroEtiqueta'
        ));
    }

    /**
     * Gestión de Etiquetas de Tareas.
     */
    public function etiquetas()
    {
        if (!Schema::hasTable('etiquetas')) {
            Schema::create('etiquetas', function ($table) {
                $table->increments('id');
                $table->string('nombre', 100);
                $table->string('color', 30)->default('#3b82f6');
            });
        }

        $etiquetas = DB::table('etiquetas as e')
            ->leftJoin('tarea_etiquetas as te', 'e.id', '=', 'te.id_etiqueta')
            ->select('e.*', DB::raw('COUNT(te.id_tarea) as total_tareas'))
            ->groupBy('e.id', 'e.nombre')
            ->orderBy('e.nombre', 'asc')
            ->get();

        return view('tareas.etiquetas', compact('etiquetas'));
    }

    public function storeEtiqueta(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:100']);
        DB::table('etiquetas')->insert([
            'nombre' => trim($request->input('nombre')),
        ]);
        return redirect()->route('tareas.etiquetas.index')->with('success', 'Etiqueta creada correctamente.');
    }

    public function updateEtiqueta(Request $request, $id)
    {
        $request->validate(['nombre' => 'required|string|max:100']);
        DB::table('etiquetas')->where('id', $id)->update([
            'nombre' => trim($request->input('nombre')),
        ]);
        return redirect()->route('tareas.etiquetas.index')->with('success', 'Etiqueta actualizada correctamente.');
    }

    public function destroyEtiqueta($id)
    {
        if (Schema::hasTable('tarea_etiquetas')) {
            DB::table('tarea_etiquetas')->where('id_etiqueta', $id)->delete();
        }
        DB::table('etiquetas')->where('id', $id)->delete();
        return redirect()->route('tareas.etiquetas.index')->with('success', 'Etiqueta eliminada correctamente.');
    }
}
