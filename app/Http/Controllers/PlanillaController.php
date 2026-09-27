<?php

namespace App\Http\Controllers;

use App\Models\AsistenciaEmpleado;
use App\Models\EmpleadoTarifa;
use App\Models\PlanillaPago;
use App\Models\Rol;
use App\Models\RolTarifa;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanillaController extends Controller
{
    /**
     * Dashboard administrativo de Planillas.
     */
    public function index()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso restringido.');
        }

        $hoy = Carbon::today()->format('Y-m-d');

        // 1. Empleados trabajando hoy (activo)
        $activos = AsistenciaEmpleado::with('usuario')
                                     ->where('fecha', $hoy)
                                     ->where('estado', 'activo')
                                     ->get();
        $total_activos = $activos->count();

        // 2. Total marcas hoy
        $total_marcas_hoy = AsistenciaEmpleado::where('fecha', $hoy)->count();

        // 3. Empleados configurados en planilla
        $total_empleados_config = EmpleadoTarifa::whereNotNull('id_rol_planilla')->count();

        // 4. Obtener tarifas de categorías (roles Empleado%)
        $categorias_tarifas = Rol::leftJoin('roles_tarifas', 'roles.id', '=', 'roles_tarifas.id_rol')
                                 ->where('roles.nombre', 'like', 'Empleado%')
                                 ->select('roles.id as id_rol', 'roles.nombre as rol_nombre', 'roles_tarifas.tipo_pago', 'roles_tarifas.monto_pago', 'roles_tarifas.moneda')
                                 ->orderBy('roles.nombre', 'asc')
                                 ->get();

        // 5. Últimas 5 marcas
        $ultimas_marcas = AsistenciaEmpleado::with('usuario')
                                            ->orderBy('id', 'desc')
                                            ->limit(5)
                                            ->get();

        return view('planilla.index', compact('activos', 'total_activos', 'total_marcas_hoy', 'total_empleados_config', 'categorias_tarifas', 'ultimas_marcas'));
    }

    /**
     * Guarda la tarifa asignada a una categoría / rol global.
     */
    public function guardarTarifaCategoria(Request $request)
    {
        $request->validate([
            'id_rol' => 'required|exists:roles,id',
            'tipo_pago' => 'required|in:fijo,por_horas',
            'monto_pago' => 'required|numeric|min:0',
            'moneda' => 'required|string|max:10'
        ]);

        RolTarifa::updateOrCreate(
            ['id_rol' => $request->id_rol],
            [
                'tipo_pago' => $request->tipo_pago,
                'monto_pago' => $request->monto_pago,
                'moneda' => $request->moneda
            ]
        );

        return redirect()->route('planilla.index')->with('success', 'Tarifa para la categoría de empleado configurada con éxito.');
    }

    /**
     * Configuración de Empleados y sus Credenciales.
     */
    public function empleados()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso restringido.');
        }

        // Roles que coinciden con Empleado% para selectores
        $roles = Rol::leftJoin('roles_tarifas', 'roles.id', '=', 'roles_tarifas.id_rol')
                    ->where('roles.nombre', 'like', 'Empleado%')
                    ->select('roles.id', 'roles.nombre', 'roles_tarifas.tipo_pago', 'roles_tarifas.monto_pago', 'roles_tarifas.moneda')
                    ->orderBy('roles.nombre', 'asc')
                    ->get();

        // Directorio de empleados activos o registrados en tarifas
        $empleados = Usuario::leftJoin('roles', 'usuarios.id_rol', '=', 'roles.id')
                            ->leftJoin('empleados_tarifas', 'usuarios.id', '=', 'empleados_tarifas.id_usuario')
                            ->leftJoin('roles as rp', 'empleados_tarifas.id_rol_planilla', '=', 'rp.id')
                            ->leftJoin('roles_tarifas as rt', 'empleados_tarifas.id_rol_planilla', '=', 'rt.id_rol')
                            ->where('roles.nombre', 'like', 'Empleado%')
                            ->orWhereNotNull('empleados_tarifas.id_usuario')
                            ->select(
                                'usuarios.id', 'usuarios.nombre', 'usuarios.apellidos', 'usuarios.email', 'usuarios.cedula', 'usuarios.id_rol',
                                'roles.nombre as rol_nombre', 'empleados_tarifas.id_rol_planilla', 'rp.nombre as rol_planilla_nombre',
                                'rt.tipo_pago', 'rt.monto_pago', 'rt.moneda', 'empleados_tarifas.cargo_credencial', 'empleados_tarifas.foto_credencial',
                                'empleados_tarifas.vigencia_credencial'
                            )
                            ->orderBy('usuarios.nombre', 'asc')
                            ->get();

        return view('planilla.empleados', compact('roles', 'empleados'));
    }

    /**
     * Asigna la categoría de planilla al empleado.
     */
    public function guardarTarifaEmpleado(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'id_rol_planilla' => 'nullable|exists:roles,id'
        ]);

        EmpleadoTarifa::updateOrCreate(
            ['id_usuario' => $request->id_usuario],
            ['id_rol_planilla' => $request->id_rol_planilla]
        );

        return redirect()->route('planilla.empleados')->with('success', 'Categoría de planilla del empleado actualizada con éxito.');
    }

    /**
     * Guarda la credencial (cargo, vigencia, foto y cédula) del empleado.
     */
    public function guardarCredencialEmpleado(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'cedula' => 'required|string|max:50',
            'cargo_credencial' => 'required|string|max:150',
            'vigencia_credencial' => 'nullable|date',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048'
        ]);

        $vigencia = $request->filled('vigencia_credencial') ? $request->vigencia_credencial : Carbon::now()->addYear()->format('Y-m-d');

        DB::beginTransaction();
        try {
            // 1. Actualizar cédula en usuarios
            Usuario::where('id', $request->id_usuario)->update(['cedula' => $request->cedula]);

            // 2. Subir foto
            $foto_path = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = 'user_' . $request->id_usuario . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/credenciales'), $filename);
                $foto_path = 'uploads/credenciales/' . $filename;
            }

            // 3. Crear o actualizar empleado_tarifa
            $data = [
                'cargo_credencial' => $request->cargo_credencial,
                'vigencia_credencial' => $vigencia
            ];
            if ($foto_path) {
                $data['foto_credencial'] = $foto_path;
            }

            EmpleadoTarifa::updateOrCreate(
                ['id_usuario' => $request->id_usuario],
                $data
            );

            DB::commit();
            return redirect()->route('planilla.empleados')->with('success', 'Credencial del empleado configurada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('planilla.empleados')->with('error', 'Error al configurar la credencial: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la credencial QR virtual del usuario o del empleado especificado.
     */
    public function credencial($id = null)
    {
        $id_usuario = $id ?: Auth::id();

        if ($id && Auth::user()->id_rol != 1) {
            return redirect()->route('planilla.credencial')->with('error', 'No tienes permisos para ver credenciales de otros empleados.');
        }

        $user_data = Usuario::leftJoin('roles', 'usuarios.id_rol', '=', 'roles.id')
                            ->leftJoin('empleados_tarifas', 'usuarios.id', '=', 'empleados_tarifas.id_usuario')
                            ->where('usuarios.id', $id_usuario)
                            ->select('usuarios.*', 'roles.nombre as rol_nombre', 'empleados_tarifas.cargo_credencial', 'empleados_tarifas.foto_credencial', 'empleados_tarifas.vigencia_credencial')
                            ->first();

        if (!$user_data) {
            return redirect()->route('dashboard')->with('error', 'No se pudo obtener la información del usuario.');
        }

        // Validar vigencia
        $vencido = true;
        if (!empty($user_data->vigencia_credencial)) {
            $vencido = Carbon::parse($user_data->vigencia_credencial)->isPast();
        }

        // Generar Token seguro para credencial QR
        $qr_secret = config('app.key') ?: 'inbox_qr_secret_' . config('cliente.id', 'cefi');
        $qr_token = md5($id_usuario . $qr_secret);

        $qr_payload = json_encode([
            "id" => $id_usuario,
            "token" => $qr_token
        ]);

        return view('planilla.credencial', compact('user_data', 'vencido', 'qr_payload'));
    }

    /**
     * Estación de Escaneo de Credenciales QR.
     */
    public function escanear()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso restringido.');
        }
        return view('planilla.escanear');
    }

    /**
     * Procesa marcas vía AJAX desde la estación de escaneo QR.
     */
    public function procesarMarcaQR(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|integer',
            'token' => 'required|string'
        ]);

        $qr_secret = config('app.key') ?: 'inbox_qr_secret_' . config('cliente.id', 'cefi');
        $expected_token = md5($request->id_usuario . $qr_secret);
        $legacy_token = md5($request->id_usuario . 'unela_qr_secret_token_2026');

        if ($request->token !== $expected_token && $request->token !== $legacy_token) {
            return response()->json(['success' => false, 'message' => 'Firma del código QR inválida. Acceso rechazado.']);
        }

        $empleado = Usuario::leftJoin('empleados_tarifas', 'usuarios.id', '=', 'empleados_tarifas.id_usuario')
                           ->where('usuarios.id', $request->id_usuario)
                           ->select('usuarios.nombre', 'usuarios.apellidos', 'usuarios.email', 'empleados_tarifas.vigencia_credencial')
                           ->first();

        if (!$empleado) {
            return response()->json(['success' => false, 'message' => 'El empleado no existe en el sistema.']);
        }

        // Vigencia de credencial
        if (empty($empleado->vigencia_credencial)) {
            return response()->json(['success' => false, 'message' => 'Su credencial está inactiva. Contacte a administración.']);
        }
        if (Carbon::parse($empleado->vigencia_credencial)->isPast()) {
            $fecha_v = Carbon::parse($empleado->vigencia_credencial)->format('d/m/Y');
            return response()->json(['success' => false, 'message' => "Su credencial ha vencido el {$fecha_v}. Contacte a administración."]);
        }

        $hoy = Carbon::today()->format('Y-m-d');
        $hora_actual = Carbon::now()->format('H:i:s');

        // Determinar Entrada o Salida
        $jornada_activa = AsistenciaEmpleado::where('id_usuario', $request->id_usuario)
                                            ->where('estado', 'activo')
                                            ->first();

        $tipo_marca = "";

        if (!$jornada_activa) {
            // Registrar entrada
            AsistenciaEmpleado::create([
                'id_usuario' => $request->id_usuario,
                'fecha' => $hoy,
                'hora_entrada' => $hora_actual,
                'estado' => 'activo'
            ]);
            $tipo_marca = "Entrada";
        } else {
            // Registrar salida y calcular horas
            $t_entrada = Carbon::parse($jornada_activa->fecha->format('Y-m-d') . ' ' . $jornada_activa->hora_entrada);
            $t_salida = Carbon::parse($hoy . ' ' . $hora_actual);
            $horas_decimales = $t_entrada->diffInMinutes($t_salida) / 60;
            $horas_decimales = round($horas_decimales, 2);

            $jornada_activa->update([
                'hora_salida' => $hora_actual,
                'horas_trabajadas' => $horas_decimales,
                'estado' => 'completado'
            ]);
            $tipo_marca = "Salida";
        }

        // Fecha elegante en español
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        $fecha_es = $dias[Carbon::now()->dayOfWeek] . ", " . Carbon::now()->day . " de " . $meses[Carbon::now()->month - 1] . " de " . Carbon::now()->year;

        return response()->json([
            'success' => true,
            'empleado' => [
                'nombre' => htmlspecialchars($empleado->nombre . ' ' . $empleado->apellidos),
                'email' => htmlspecialchars($empleado->email)
            ],
            'marca' => [
                'tipo' => $tipo_marca,
                'hora' => Carbon::now()->format('g:i:s a'),
                'fecha' => $fecha_es
            ]
        ]);
    }

    /**
     * Motor de Cálculo de Planilla.
     */
    public function calcular(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso restringido.');
        }

        // Obtener empleados configurados
        $empleados = Usuario::join('empleados_tarifas', 'usuarios.id', '=', 'empleados_tarifas.id_usuario')
                            ->whereNotNull('empleados_tarifas.id_rol_planilla')
                            ->select('usuarios.id', 'usuarios.nombre', 'usuarios.apellidos')
                            ->orderBy('usuarios.nombre')
                            ->get();

        $calc_user = $request->id_usuario;
        $calc_start = $request->filled('fecha_inicio') ? $request->fecha_inicio : Carbon::now()->startOfMonth()->format('Y-m-d');
        $calc_end = $request->filled('fecha_fin') ? $request->fecha_fin : Carbon::now()->format('Y-m-d');

        $mostrar_calculo = false;
        $empleado_info = null;
        $tarifas_info = null;
        $horas_totales = 0.00;
        $jornadas = [];
        $monto_calculado = 0.00;

        if ($request->filled('id_usuario')) {
            $empleado_info = Usuario::find($calc_user);
            $tarifas_info = EmpleadoTarifa::leftJoin('roles_tarifas', 'empleados_tarifas.id_rol_planilla', '=', 'roles_tarifas.id_rol')
                                          ->where('empleados_tarifas.id_usuario', $calc_user)
                                          ->select('empleados_tarifas.id_usuario', 'roles_tarifas.tipo_pago', 'roles_tarifas.monto_pago', 'roles_tarifas.moneda')
                                          ->first();

            if ($empleado_info && $tarifas_info && $tarifas_info->monto_pago !== null) {
                $mostrar_calculo = true;

                // Buscar jornadas completadas
                $jornadas = AsistenciaEmpleado::where('id_usuario', $calc_user)
                                             ->whereBetween('fecha', [$calc_start, $calc_end])
                                             ->where('estado', 'completado')
                                             ->orderBy('fecha', 'asc')
                                             ->get();

                foreach ($jornadas as $jor) {
                    $horas_totales += floatval($jor->horas_trabajadas);
                }

                // Cálculo
                if ($tarifas_info->tipo_pago === 'por_horas') {
                    $monto_calculado = $horas_totales * floatval($tarifas_info->monto_pago);
                } else {
                    // Fijo
                    $monto_calculado = floatval($tarifas_info->monto_pago);
                }
            } else {
                return redirect()->route('planilla.calcular')->with('error', 'El empleado seleccionado no tiene configurada una tarifa de categoría válida en el sistema.');
            }
        }

        return view('planilla.calcular', compact('empleados', 'calc_user', 'calc_start', 'calc_end', 'mostrar_calculo', 'empleado_info', 'tarifas_info', 'horas_totales', 'jornadas', 'monto_calculado'));
    }

    /**
     * Guarda el pago de la planilla procesada.
     */
    public function guardarPagoPlanilla(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'tipo_pago' => 'required|in:fijo,por_horas',
            'horas_totales' => 'required|numeric',
            'tarifa_aplicada' => 'required|numeric',
            'monto_total' => 'required|numeric',
            'notas' => 'nullable|string'
        ]);

        PlanillaPago::create([
            'id_usuario' => $request->id_usuario,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'tipo_pago' => $request->tipo_pago,
            'horas_totales' => $request->horas_totales,
            'tarifa_aplicada' => $request->tarifa_aplicada,
            'monto_total' => $request->monto_total,
            'fecha_pago' => Carbon::now()->format('Y-m-d'),
            'estado' => 'pagado',
            'notas' => $request->notas
        ]);

        return redirect()->route('planilla.calcular')->with('success', 'Planilla procesada y pago registrado correctamente.');
    }

    /**
     * Historial de pagos desembolsados.
     */
    public function historialPagos()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso restringido.');
        }

        $pagos = PlanillaPago::with(['usuario.empleadoTarifa'])
                             ->orderBy('id', 'desc')
                             ->paginate(20);

        return view('planilla.historial_pagos', compact('pagos'));
    }
}
