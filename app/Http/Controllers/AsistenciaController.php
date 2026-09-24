<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaEmpleado;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Muestra la interfaz de marcado para el empleado logueado.
     */
    public function marcar()
    {
        $id_usuario = Auth::id();
        $hoy = Carbon::today()->format('Y-m-d');

        // Buscar si hay jornada activa (entrada sin salida)
        $jornada_activa = AsistenciaEmpleado::where('id_usuario', $id_usuario)
                                            ->where('estado', 'activo')
                                            ->first();

        // Historial del usuario para el día de hoy
        $historial_hoy = AsistenciaEmpleado::where('id_usuario', $id_usuario)
                                           ->whereDate('fecha', $hoy)
                                           ->orderBy('id', 'desc')
                                           ->get();

        return view('asistencia.marcar', compact('jornada_activa', 'historial_hoy'));
    }

    /**
     * Registra la entrada o la salida del usuario logueado.
     */
    public function registrarMarca(Request $request)
    {
        $request->validate([
            'action' => 'required|in:entrada,salida'
        ]);

        $id_usuario = Auth::id();
        $hoy = Carbon::today()->format('Y-m-d');
        $hora_actual = Carbon::now()->format('H:i:s');

        // Buscar jornada activa
        $jornada_activa = AsistenciaEmpleado::where('id_usuario', $id_usuario)
                                            ->where('estado', 'activo')
                                            ->first();

        if ($request->action === 'entrada') {
            if ($jornada_activa) {
                return redirect()->route('asistencia.marcar')->with('error', 'Ya tienes una jornada activa iniciada.');
            }

            AsistenciaEmpleado::create([
                'id_usuario' => $id_usuario,
                'fecha' => $hoy,
                'hora_entrada' => $hora_actual,
                'estado' => 'activo'
            ]);

            return redirect()->route('asistencia.marcar')->with('success', 'Entrada registrada correctamente a las ' . Carbon::now()->format('g:i a') . '. ¡Que tengas un excelente día de trabajo!');
        } else {
            // salida
            if (!$jornada_activa) {
                return redirect()->route('asistencia.marcar')->with('error', 'No hay ninguna jornada activa registrada para cerrar.');
            }

            // Calcular horas trabajadas en decimal
            $fechaEntrada = Carbon::parse($jornada_activa->fecha->format('Y-m-d') . ' ' . $jornada_activa->hora_entrada);
            $fechaSalida = Carbon::parse($hoy . ' ' . $hora_actual);
            
            $horas_decimales = $fechaEntrada->diffInMinutes($fechaSalida) / 60;
            $horas_decimales = round($horas_decimales, 2);

            $jornada_activa->update([
                'hora_salida' => $hora_actual,
                'horas_trabajadas' => $horas_decimales,
                'estado' => 'completado'
            ]);

            return redirect()->route('asistencia.marcar')->with('success', "Salida registrada correctamente a las " . Carbon::now()->format('g:i a') . ". Has trabajado {$horas_decimales} horas.");
        }
    }

    /**
     * Historial general de asistencia (Administración).
     */
    public function historial(Request $request)
    {
        // Solo administradores (Rol 1)
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        $empleados = Usuario::orderBy('nombre', 'asc')->get();

        $query = AsistenciaEmpleado::with('usuario');

        // Filtros
        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $registros = $query->orderBy('fecha', 'desc')->orderBy('hora_entrada', 'desc')->paginate(20);

        return view('asistencia.historial', compact('empleados', 'registros'));
    }

    /**
     * Registra una marca de asistencia de forma manual (Administración).
     */
    public function guardarMarcaManual(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No autorizado.');
        }

        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required',
            'hora_salida' => 'nullable'
        ]);

        $horas_trabajadas = 0.00;
        $estado = 'activo';

        if ($request->filled('hora_salida')) {
            $t_entrada = Carbon::parse($request->fecha . ' ' . $request->hora_entrada);
            $t_salida = Carbon::parse($request->fecha . ' ' . $request->hora_salida);
            $horas_trabajadas = $t_entrada->diffInMinutes($t_salida) / 60;
            $horas_trabajadas = round($horas_trabajadas, 2);
            $estado = 'completado';
        }

        AsistenciaEmpleado::create([
            'id_usuario' => $request->id_usuario,
            'fecha' => $request->fecha,
            'hora_entrada' => $request->hora_entrada,
            'hora_salida' => $request->hora_salida,
            'horas_trabajadas' => $horas_trabajadas,
            'estado' => $estado
        ]);

        return redirect()->route('asistencia.historial')->with('success', 'Registro de asistencia manual creado con éxito.');
    }

    /**
     * Actualiza un registro de asistencia (Administración).
     */
    public function actualizarMarca(Request $request, $id)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No autorizado.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'hora_entrada' => 'required',
            'hora_salida' => 'nullable'
        ]);

        $registro = AsistenciaEmpleado::findOrFail($id);
        
        $horas_trabajadas = 0.00;
        $estado = 'activo';

        if ($request->filled('hora_salida')) {
            $t_entrada = Carbon::parse($request->fecha . ' ' . $request->hora_entrada);
            $t_salida = Carbon::parse($request->fecha . ' ' . $request->hora_salida);
            $horas_trabajadas = $t_entrada->diffInMinutes($t_salida) / 60;
            $horas_trabajadas = round($horas_trabajadas, 2);
            $estado = 'completado';
        }

        $registro->update([
            'fecha' => $request->fecha,
            'hora_entrada' => $request->hora_entrada,
            'hora_salida' => $request->hora_salida,
            'horas_trabajadas' => $horas_trabajadas,
            'estado' => $estado
        ]);

        return redirect()->route('asistencia.historial')->with('success', 'Registro de asistencia actualizado con éxito.');
    }

    /**
     * Elimina un registro de asistencia (Administración).
     */
    public function eliminarMarca($id)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No autorizado.');
        }

        $registro = AsistenciaEmpleado::findOrFail($id);
        $registro->delete();

        return redirect()->route('asistencia.historial')->with('success', 'Registro de asistencia eliminado con éxito.');
    }
}
