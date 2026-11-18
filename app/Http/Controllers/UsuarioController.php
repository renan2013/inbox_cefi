<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\ExpedienteDigital;
use App\Services\ModuleService;
use App\Services\ClienteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios con filtros, orden alfabético por apellidos y KPIs interactivos.
     */
    public function index(Request $request)
    {
        $moodleSyncEnabled = ModuleService::isEnabled('moodle_sync');

        // Saneamiento automático: Si Moodle Sync no está activo, todos los usuarios operan 100% con la base de datos interna de Inbox
        if (!$moodleSyncEnabled) {
            Usuario::where('origen', 'moodle')->orWhereNotNull('id_moodle')->update([
                'origen' => 'inbox',
                'id_moodle' => null,
            ]);
        }

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
        $roles = Rol::oficiales();

        // Conteo seguro para KPIs en tiempo real
        try {
            $totalUsuarios = Usuario::count();
            $adminRolIds = Rol::whereIn('nombre', ['Administrador General', 'Administrador'])->pluck('id')->all();
            $adminsCount = Usuario::whereIn('id_rol', !empty($adminRolIds) ? $adminRolIds : [1])->count();
            $docenteRolIds = Rol::whereIn('nombre', ['Profesor', 'Profesor Activo', 'Docente'])->pluck('id')->all();
            $docentesCount = !empty($docenteRolIds) ? Usuario::whereIn('id_rol', $docenteRolIds)->count() : 0;
            $estudianteRolIds = Rol::whereIn('nombre', ['Estudiante', 'Miembro', 'Alumno'])->pluck('id')->all();
            $estudiantesCount = !empty($estudianteRolIds) ? Usuario::whereIn('id_rol', $estudianteRolIds)->count() : 0;
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
        $roles = Rol::oficiales(auth()->user()->esAdminGeneral());
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
            $estRol = Rol::where('nombre', 'Estudiante')->first();
            $rolId = $estRol ? $estRol->id : 3;
        }

        if (!auth()->user()->esAdminGeneral() && (int)$rolId === 1) {
            abort(403, 'Solo el Administrador General puede asignar el rol de Administrador General.');
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
            'telefono' => $request->telefono,
            'password' => $request->password,
        ]);
    }

    /**
     * Envía las credenciales de acceso editables al WhatsApp del usuario vía n8n / Evolution API.
     */
    public function enviarCredencialesWhatsapp(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string|max:30',
            'mensaje'  => 'required|string|max:3000',
        ]);

        $res = \App\Services\WhatsAppService::sendTo(
            $request->input('telefono'),
            $request->input('mensaje'),
            null,
            ['tipo' => 'credenciales_acceso']
        );

        return response()->json([
            'success' => !empty($res['success']),
            'message' => $res['message'] ?? (!empty($res['success'])
                ? 'Credenciales enviadas automáticamente por WhatsApp.'
                : 'No se pudo enviar automáticamente. Verifica la configuración de n8n / WhatsApp.'),
        ], !empty($res['success']) ? 200 : 422);
    }

    /**
     * Envía la tarjeta editable de credenciales al correo electrónico del usuario vía SMTP.
     */
    public function enviarCredencialesEmail(Request $request)
    {
        $request->validate([
            'email'   => 'required|email|max:255',
            'nombre'  => 'nullable|string|max:255',
            'mensaje' => 'required|string|max:3000',
        ]);

        $email = trim((string) $request->input('email'));
        $nombre = trim((string) $request->input('nombre', ''));
        $mensajeTexto = trim((string) $request->input('mensaje'));
        $institucion = ClienteService::nombre();
        $loginUrl = url('/');

        $asunto = "Credenciales de Acceso | {$institucion}";
        $mensajeHtml = nl2br(e($mensajeTexto));

        $html = "
        <div style='font-family:Arial,Helvetica,sans-serif;background:#f1f5f9;padding:24px;'>
            <div style='max-width:600px;margin:0 auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(15,23,42,0.06);'>
                <div style='background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);padding:22px 28px;color:#ffffff;'>
                    <div style='font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#86efac;font-weight:bold;'>" . e($institucion) . " · Acceso a Plataforma</div>
                    <h2 style='margin:6px 0 0;font-size:20px;color:#ffffff;'>Bienvenido(a) a la Plataforma</h2>
                </div>
                <div style='padding:26px 28px;color:#1e293b;font-size:14px;line-height:1.65;'>
                    <div style='background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #5fb230;border-radius:10px;padding:18px;margin-bottom:22px;'>
                        {$mensajeHtml}
                    </div>
                    <div style='text-align:center;'>
                        <a href='" . e($loginUrl) . "' style='display:inline-block;background:#5fb230;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 28px;border-radius:999px;font-size:14px;'>Ingresar a la Plataforma</a>
                    </div>
                </div>
                <div style='background:#f8fafc;padding:14px 28px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;text-align:center;'>
                    Correo enviado automáticamente por " . e($institucion) . ".
                </div>
            </div>
        </div>";

        try {
            Mail::html($html, function ($message) use ($email, $nombre, $asunto) {
                $message->to($email, $nombre ?: null)->subject($asunto);
            });

            $destLabel = $nombre !== '' ? "{$nombre} ({$email})" : $email;
            return response()->json([
                'success' => true,
                'message' => "Las credenciales han sido enviadas al correo de {$destLabel} con éxito.",
            ]);
        } catch (\Throwable $e) {
            Log::error("Error enviando credenciales por correo a {$email}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el correo SMTP: ' . $e->getMessage(),
            ], 422);
        }
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

        // 4. Bloquear eliminación del Administrador Principal (#ID 1)
        if ($usuario->id === 1) {
            return response()->json([
                'success' => false,
                'message' => 'No es posible eliminar la cuenta del Administrador Principal del sistema.'
            ], 422);
        }

        // 5. Bloquear eliminación si el usuario proviene de Moodle (únicamente cuando Moodle Sync esté contratado/activo)
        $moodleSyncEnabled = ModuleService::isEnabled('moodle_sync');
        $origenUser = strtolower((string)$usuario->origen);
        if ($moodleSyncEnabled && ($origenUser === 'moodle' || !empty($usuario->id_moodle))) {
            return response()->json([
                'success' => false,
                'message' => 'Operación no permitida: Los usuarios sincronizados desde Moodle no pueden eliminarse desde Inbox.'
            ], 422);
        }

        // 6. Eliminar con captura segura de restricciones Foreign Key
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
        $esAdminGen = auth()->user()->esAdminGeneral();
        if (!$esAdminGen && $usuario->esAdminGeneral()) {
            abort(403, 'Solo el Administrador General puede editar esta cuenta.');
        }
        $roles = Rol::oficiales($esAdminGen);
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza la información del usuario en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        $esAdminGen = auth()->user()->esAdminGeneral();

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

        if (!$esAdminGen && ($usuario->esAdminGeneral() || (int)$request->id_rol === 1)) {
            $msg = 'Solo el Administrador General puede modificar cuentas o asignar el rol de Administrador General.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            abort(403, $msg);
        }

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
