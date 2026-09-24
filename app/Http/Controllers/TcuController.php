<?php

namespace App\Http\Controllers;

use App\Models\TcuBitacora;
use App\Models\TcuActividad;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TcuController extends Controller
{
    /**
     * Muestra la lista de bitácoras del estudiante logueado o de todos (si es admin).
     */
    public function index()
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        if ($es_admin) {
            $bitacoras = TcuBitacora::with('estudiante')
                                    ->orderBy('fecha_creacion', 'desc')
                                    ->paginate(20);
        } else {
            $bitacoras = TcuBitacora::where('usuario_id', $usuario_id)
                                    ->orderBy('fecha_creacion', 'desc')
                                    ->paginate(20);
        }

        return view('tcu.index', compact('bitacoras', 'es_admin'));
    }

    /**
     * Formulario de creación de una nueva bitácora.
     */
    public function create()
    {
        if (Auth::user()->id_rol == 1) {
            return redirect()->route('tcu.index')->with('error', 'Los administradores no pueden crear bitácoras personales.');
        }

        $bitacora = new TcuBitacora();
        return view('tcu.create', compact('bitacora'));
    }

    /**
     * Guarda la cabecera de la bitácora.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
            'lugar_realizacion' => 'required|string|max:255',
            'institucion_beneficiada' => 'required|string|max:255',
            'nombre_supervisor' => 'required|string|max:255',
            'cedula_supervisor' => 'required|string|max:50',
            'email_institucion' => 'required|email|max:100',
            'telefono_institucion' => 'required|string|max:50',
            'carrera' => 'required|string|max:100'
        ]);

        $bitacora = TcuBitacora::create([
            'usuario_id' => Auth::id(),
            'nombre_proyecto' => $request->nombre_proyecto,
            'lugar_realizacion' => $request->lugar_realizacion,
            'institucion_beneficiada' => $request->institucion_beneficiada,
            'nombre_supervisor' => $request->nombre_supervisor,
            'cedula_supervisor' => $request->cedula_supervisor,
            'email_institucion' => $request->email_institucion,
            'telefono_institucion' => $request->telefono_institucion,
            'carrera' => $request->carrera,
            'estado' => 'borrador',
            'fecha_creacion' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'bitacora_id' => $bitacora->id,
            'message' => 'Cabecera de bitácora creada con éxito.'
        ]);
    }

    /**
     * Formulario de edición o visualización de bitácora y sus actividades.
     */
    public function edit($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        if ($es_admin) {
            $bitacora = TcuBitacora::with('estudiante')->findOrFail($id);
        } else {
            $bitacora = TcuBitacora::where('id', $id)
                                    ->where('usuario_id', $usuario_id)
                                    ->firstOrFail();
        }

        $actividades = TcuActividad::where('bitacora_id', $id)
                                    ->orderBy('fecha', 'asc')
                                    ->orderBy('hora_entrada', 'asc')
                                    ->get();

        $total_horas = $actividades->sum('cantidad_horas');
        $es_lectura = ($bitacora->estado !== 'borrador' || $es_admin);

        return view('tcu.edit', compact('bitacora', 'actividades', 'total_horas', 'es_lectura', 'es_admin'));
    }

    /**
     * Actualiza la cabecera de la bitácora.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_proyecto' => 'required|string|max:255',
            'lugar_realizacion' => 'required|string|max:255',
            'institucion_beneficiada' => 'required|string|max:255',
            'nombre_supervisor' => 'required|string|max:255',
            'cedula_supervisor' => 'required|string|max:50',
            'email_institucion' => 'required|email|max:100',
            'telefono_institucion' => 'required|string|max:50',
            'carrera' => 'required|string|max:100'
        ]);

        $bitacora = TcuBitacora::where('id', $id)
                                ->where('usuario_id', Auth::id())
                                ->firstOrFail();

        if ($bitacora->estado !== 'borrador') {
            return response()->json(['success' => false, 'error' => 'Esta bitácora ya está cerrada y no se puede editar.']);
        }

        $bitacora->update($request->all());

        return response()->json(['success' => true, 'message' => 'Cabecera de bitácora actualizada con éxito.']);
    }

    /**
     * Elimina una bitácora completa.
     */
    public function destroy($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);

        if ($es_admin) {
            $bitacora = TcuBitacora::findOrFail($id);
        } else {
            $bitacora = TcuBitacora::where('id', $id)
                                    ->where('usuario_id', Auth::id())
                                    ->where('estado', 'borrador')
                                    ->firstOrFail();
        }

        $bitacora->delete();

        return redirect()->route('tcu.index')->with('success', 'Bitácora eliminada con éxito.');
    }

    /**
     * Cierra y envía la bitácora.
     */
    public function finalizar($id)
    {
        $bitacora = TcuBitacora::where('id', $id)
                                ->where('usuario_id', Auth::id())
                                ->where('estado', 'borrador')
                                ->firstOrFail();

        $bitacora->update([
            'estado' => 'finalizado',
            'fecha_finalizacion' => Carbon::today()
        ]);

        return response()->json(['success' => true, 'message' => 'Bitácora finalizada y enviada con éxito.']);
    }

    /**
     * Cambia el estado a Revisado (solo administradores).
     */
    public function revisar($id)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'error' => 'Acceso denegado.']);
        }

        $bitacora = TcuBitacora::findOrFail($id);
        $bitacora->update(['estado' => 'revisado']);

        return response()->json(['success' => true, 'message' => 'Bitácora marcada como revisada.']);
    }

    /**
     * Reabre la bitácora para edición (solo administradores).
     */
    public function reabrir($id)
    {
        if (Auth::user()->id_rol != 1) {
            return response()->json(['success' => false, 'error' => 'Acceso denegado.']);
        }

        $bitacora = TcuBitacora::findOrFail($id);
        $bitacora->update(['estado' => 'borrador']);

        return response()->json(['success' => true, 'message' => 'Bitácora reabierta para edición con éxito.']);
    }

    /**
     * Agrega una actividad a la bitácora.
     */
    public function storeActividad(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_entrada' => 'required',
            'hora_salida' => 'required',
            'cantidad_horas' => 'required|numeric|min:0.1',
            'actividades' => 'required|string'
        ]);

        $bitacora = TcuBitacora::where('id', $id)
                                ->where('usuario_id', Auth::id())
                                ->firstOrFail();

        if ($bitacora->estado !== 'borrador') {
            return response()->json(['success' => false, 'error' => 'Esta bitácora está cerrada.']);
        }

        $actividad = TcuActividad::create([
            'bitacora_id' => $id,
            'fecha' => $request->fecha,
            'hora_entrada' => $request->hora_entrada,
            'hora_salida' => $request->hora_salida,
            'cantidad_horas' => $request->cantidad_horas,
            'actividades' => $request->actividades
        ]);

        return response()->json([
            'success' => true,
            'actividad' => $actividad
        ]);
    }

    /**
     * Elimina una actividad.
     */
    public function destroyActividad($id)
    {
        $actividad = TcuActividad::findOrFail($id);
        
        $bitacora = TcuBitacora::where('id', $actividad->bitacora_id)
                                ->where('usuario_id', Auth::id())
                                ->firstOrFail();

        if ($bitacora->estado !== 'borrador') {
            return response()->json(['success' => false, 'error' => 'Esta bitácora está cerrada.']);
        }

        $actividad->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Vista de impresión oficial de la bitácora (Print Layout / PDF).
     */
    public function imprimir($id)
    {
        $es_admin = (Auth::user()->id_rol == 1);
        $usuario_id = Auth::id();

        if ($es_admin) {
            $bitacora = TcuBitacora::with('estudiante')->findOrFail($id);
        } else {
            $bitacora = TcuBitacora::where('id', $id)
                                    ->where('usuario_id', $usuario_id)
                                    ->firstOrFail();
        }

        $actividades = TcuActividad::where('bitacora_id', $id)
                                    ->orderBy('fecha', 'asc')
                                    ->orderBy('hora_entrada', 'asc')
                                    ->get();

        $total_horas = $actividades->sum('cantidad_horas');
        $fecha_inicio = $actividades->min('fecha');
        $fecha_final = $actividades->max('fecha');

        return view('tcu.imprimir', compact('bitacora', 'actividades', 'total_horas', 'fecha_inicio', 'fecha_final'));
    }
}
