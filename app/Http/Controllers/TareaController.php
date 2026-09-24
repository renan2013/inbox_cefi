<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
                    $path = $file->storeAs('uploads/tareas', $nombreServidor, 'public');

                    if (Schema::hasTable('adjuntos')) {
                        DB::table('adjuntos')->insert([
                            'id_tarea' => $id_tarea,
                            'nombre_original' => $nombreOriginal,
                            'nombre_servidor' => $nombreServidor,
                            'ruta_archivo' => 'storage/' . $path,
                            'tipo_mime' => $mimeType,
                            'tamano' => $tamano,
                            'id_usuario_subida' => $id_creador,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Tarea Creada con Éxito');
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
}
