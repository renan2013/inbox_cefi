<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
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
        $query = Usuario::with('rol');

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

        // Orden alfabético estricto por Apellidos y Nombre
        $query->orderByRaw("COALESCE(NULLIF(apellidos, ''), 'ZZZ') ASC")
              ->orderBy('nombre', 'asc');

        $usuarios = $query->paginate(30)->withQueryString();
        $roles = Rol::all();

        // KPIs en tiempo real para las tarjetas superiores
        $kpis = [
            'total' => Usuario::count(),
            'admins' => Usuario::where('id_rol', 1)->count(),
            'docentes' => Usuario::where('id_rol', 2)->count(),
            'estudiantes' => Usuario::where('id_rol', 3)->count(),
            'moodle' => Usuario::where('origen', 'moodle')->count(),
            'inbox' => Usuario::where('origen', 'inbox')->count(),
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

        // 2. Validar clave interna de administrador ('unela2026' o contraseña de la cuenta activa)
        $adminPassword = trim($request->input('admin_password', ''));
        if (empty($adminPassword)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe ingresar la clave interna para autorizar la eliminación del usuario.'
            ], 422);
        }

        $claveValida = false;
        if ($adminPassword === 'unela2026') {
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
}
