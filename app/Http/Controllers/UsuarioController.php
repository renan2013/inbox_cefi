<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\ExpedienteDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros, orden alfabético por apellidos y KPIs interactivos.
     */
    public function index(Request $request)
    {
        $query = Usuario::with(['rol', 'expediente']);

        // Búsqueda por término
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filtro por Rol
        if ($request->filled('rol_id')) {
            $query->where('id_rol', $request->rol_id);
        }

        // Filtro por Origen
        if ($request->filled('origen')) {
            $query->where('origen', $request->origen);
        }

        // Filtro por Estado de Expediente Digital (Con Expediente / Sin Expediente)
        if ($request->filled('expediente_status')) {
            $expStatus = $request->expediente_status;
            if ($expStatus === 'sin_expediente') {
                $query->whereDoesntHave('expediente');
            } elseif ($expStatus === 'con_expediente') {
                $query->whereHas('expediente');
            }
        }

        // Orden alfabético estricto por Apellidos y Nombre
        $query->orderByRaw("COALESCE(NULLIF(apellidos, ''), 'ZZZ') ASC")
              ->orderBy('nombre', 'asc');

        $usuarios = $query->paginate(30)->withQueryString();
        $roles = Rol::all();

        // Conteo seguro para KPIs en tiempo real
        try {
            $totalUsuarios = Usuario::count();
            $adminsCount = Usuario::where('id_rol', 1)->count();
            $docentesCount = Usuario::where('id_rol', 2)->count();
            $estudiantesCount = Usuario::where('id_rol', 3)->count();
            $moodleCount = Usuario::where('origen', 'moodle')->count();
            $inboxCount = Usuario::where('origen', 'inbox')->count();

            $totalConExpediente = ExpedienteDigital::distinct('id_usuario')->count('id_usuario');
            $sinExpediente = max(0, $totalUsuarios - $totalConExpediente);
        } catch (\Throwable $e) {
            $totalUsuarios = 0;
            $adminsCount = 0;
            $docentesCount = 0;
            $estudiantesCount = 0;
            $moodleCount = 0;
            $inboxCount = 0;
            $totalConExpediente = 0;
            $sinExpediente = 0;
        }

        // KPIs en tiempo real para las tarjetas superiores
        $kpis = [
            'total' => $totalUsuarios,
            'admins' => $adminsCount,
            'docentes' => $docentesCount,
            'estudiantes' => $estudiantesCount,
            'moodle' => $moodleCount,
            'inbox' => $inboxCount,
            'con_expediente' => $totalConExpediente,
            'sin_expediente' => $sinExpediente,
        ];

        return view('usuarios.index', compact('usuarios', 'roles', 'kpis'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:255',
            'cedula' => 'required|string|max:50',
            'email' => 'required|email|unique:usuarios,email',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'id_rol' => 'nullable|integer|exists:roles,id'
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado.',
        ]);

        $rolId = $request->input('id_rol');
        if (!$rolId) {
            $miembroRol = Rol::where('nombre', 'Miembro')->orWhere('nombre', 'Estudiante')->first();
            $rolId = $miembroRol ? $miembroRol->id : 3;
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'cedula' => $request->cedula,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'id_rol' => $rolId,
            'origen' => 'inbox',
        ]);

        return redirect()->route('usuarios.create')->with('new_user_details', [
            'id' => $usuario->id,
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => $request->password,
        ]);
    }

    /**
     * Elimina un usuario de forma segura con verificación de clave interna y protección de Moodle.
     */
    public function destroy(Request $request, $id)
    {
        $idUsuario = (int)$id;

        // 1. Validar que no se elimine a sí mismo
        if (auth()->check() && auth()->id() === $idUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta en sesión.'
            ], 422);
        }

        // 2. Validar clave interna de administrador (clave maestra o contraseña de la cuenta activa)
        $adminPassword = trim($request->input('admin_password', ''));
        if (empty($adminPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar la clave interna para autorizar la eliminación del usuario.'
            ], 422);
        }

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        $claveValida = false;
        if ($adminPassword === $masterKey || $adminPassword === 'unela2026') {
            $claveValida = true;
        } elseif (auth()->check() && Hash::check($adminPassword, auth()->user()->password)) {
            $claveValida = true;
        }

        if (!$claveValida) {
            return response()->json([
                'success' => false,
                'message' => 'Clave interna incorrecta. No se autorizó la eliminación.'
            ], 403);
        }

        // 3. Buscar el usuario
        $usuario = Usuario::find($idUsuario);
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario seleccionado no existe.'
            ], 404);
        }

        // 4. Bloquear eliminación si el usuario proviene de Moodle
        $origenUser = strtolower((string)$usuario->origen);
        if ($origenUser === 'moodle' || !empty($usuario->id_moodle)) {
            return response()->json([
                'success' => false,
                'message' => 'Operación no permitida: Los usuarios sincronizados desde Moodle no pueden eliminarse desde Inbox.'
            ], 422);
        }

        // 5. Eliminar con captura segura de restricciones Foreign Key
        try {
            DB::beginTransaction();
            $nombreCompleto = trim($usuario->nombre . ' ' . $usuario->apellidos);
            $usuario->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Usuario '{$nombreCompleto}' eliminado exitosamente del sistema."
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            if (str_contains(strtolower($msg), 'foreign key') || str_contains(strtolower($msg), 'constraint')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el usuario porque tiene registros vinculados en el sistema (expedientes, matrículas, notas, finanzas o cursos asignados).'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $msg
            ], 500);
        }
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit($id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza la información del usuario en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:255',
            'cedula' => 'nullable|string|max:50',
            'email' => 'required|email|unique:usuarios,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'id_rol' => 'required|integer|exists:roles,id'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
        ]);

        $usuario->nombre = $request->nombre;
        $usuario->apellidos = $request->apellidos;
        $usuario->cedula = $request->cedula;
        $usuario->email = $request->email;
        $usuario->telefono = $request->telefono;
        $usuario->id_rol = $request->id_rol;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Usuario '{$usuario->nombre} {$usuario->apellidos}' actualizado exitosamente.",
                'usuario' => $usuario->load('rol')
            ]);
        }

        return redirect()->route('usuarios.index')->with('success', "Usuario '{$usuario->nombre} {$usuario->apellidos}' actualizado exitosamente.");
    }
}
