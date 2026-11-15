<?php

namespace App\Http\Controllers;

use App\Models\Soporte;
use App\Models\SoporteCategoria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SoporteController extends Controller
{
    /**
     * Correo interno fijo del Creador / Proveedor de las plataformas Inbox.
     */
    public const CREATOR_PROVIDER_EMAIL = 'renangalvan@gmail.com';
    /**
     * Vista interna de administración de Tickets / Averías.
     */
    public function index(Request $request)
    {
        $categorias = SoporteCategoria::orderBy('nombre', 'asc')->get();
        // Admins (Rol 1) o usuarios calificados
        $usuarios_soporte = Usuario::whereIn('id_rol', [1, 2])->orderBy('nombre', 'asc')->get();

        $query = Soporte::with(['creador', 'reportadoA', 'categoria']);

        // Filtros
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $soportes = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        return view('soporte.gestionar', compact('categorias', 'usuarios_soporte', 'soportes'));
    }

    /**
     * Registra un nuevo ticket de soporte.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'problema' => 'nullable|string',
            'solucion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:soporte_categorias,id',
            'reportado_a' => 'nullable|exists:usuarios,id',
            'adjunto' => 'nullable|file|max:5120' // max 5MB
        ]);

        $adjunto_nombre = null;
        if ($request->hasFile('adjunto')) {
            $file = $request->file('adjunto');
            $adjunto_nombre = 'soporte_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/soporte'), $adjunto_nombre);
        }

        $soporte = Soporte::create([
            'titulo' => $request->titulo,
            'problema' => $request->problema,
            'solucion' => $request->solucion,
            'categoria_id' => $request->categoria_id,
            'id_creador' => Auth::id(),
            'reportado_a' => $request->reportado_a,
            'estado' => 'reportado',
            'adjunto' => $adjunto_nombre
        ]);

        $user = Auth::user();
        $this->dispatchOmnicanalAveria([
            'local_id'       => $soporte->id,
            'client_id'      => 'cefi',
            'client_siglas'  => config('cliente.siglas', 'CEFI'),
            'client_nombre'  => config('cliente.nombre', 'Inbox CEFI'),
            'titulo'         => $soporte->titulo,
            'etiqueta'       => optional($soporte->categoria)->nombre ?? '🟠 Fallo en Módulo',
            'prioridad'      => 'alta',
            'problema'       => (string)$request->problema,
            'estado'         => 'reportado',
            'url_origen'     => url()->previous(),
            'navegador'      => $request->header('User-Agent', ''),
            'evidencia_url'  => $adjunto_nombre ? url('uploads/soporte/' . $adjunto_nombre) : '',
            'usuario_nombre' => $user ? trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')) : 'Usuario',
            'usuario_email'  => $user->correo ?? $user->email ?? '',
            'usuario_rol'    => ($user && $user->id_rol == 1) ? 'Administrador' : 'Usuario / Docente',
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('soporte.gestionar')->with('success', 'Reporte de avería creado y sincronizado con éxito.');
    }

    /**
     * Actualiza un ticket de soporte.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'problema' => 'nullable|string',
            'solucion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:soporte_categorias,id',
            'reportado_a' => 'nullable|exists:usuarios,id',
            'adjunto' => 'nullable|file|max:5120'
        ]);

        $soporte = Soporte::findOrFail($id);

        $adjunto_nombre = $soporte->adjunto;
        if ($request->hasFile('adjunto')) {
            // Delete old file
            if ($soporte->adjunto) {
                $oldPath = public_path('uploads/soporte/' . $soporte->adjunto);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $file = $request->file('adjunto');
            $adjunto_nombre = 'soporte_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/soporte'), $adjunto_nombre);
        }

        $soporte->update([
            'titulo' => $request->titulo,
            'problema' => $request->problema,
            'solucion' => $request->solucion,
            'categoria_id' => $request->categoria_id,
            'reportado_a' => $request->reportado_a,
            'adjunto' => $adjunto_nombre
        ]);

        return redirect()->route('soporte.gestionar')->with('success', 'Ticket de soporte actualizado con éxito.');
    }

    /**
     * Cambia el estado del ticket.
     */
    public function changeStatus($id, Request $request)
    {
        $request->validate([
            'nuevo_estado' => 'required|in:reportado,solucionado'
        ]);

        $soporte = Soporte::findOrFail($id);
        $soporte->update(['estado' => $request->nuevo_estado]);

        return redirect()->route('soporte.gestionar')->with('success', 'Estado del ticket actualizado con éxito.');
    }

    /**
     * Elimina un ticket individual (local + sincronización con Inbox Cloud Hub).
     */
    public function destroy(Request $request, $id)
    {
        $soporte = Soporte::findOrFail($id);
        if ($soporte->adjunto) {
            $filePath = public_path('uploads/soporte/' . $soporte->adjunto);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $localId = $soporte->id;
        $soporte->delete();

        $this->notifyHubTicketAction('delete_ticket', ['local_id' => $localId, 'client_id' => 'cefi']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reporte de avería eliminado correctamente.']);
        }

        return redirect()->back()->with('success', 'Reporte de avería #' . $localId . ' eliminado correctamente.');
    }

    /**
     * Elimina todos los reportes de avería (útil para limpiar pruebas).
     */
    public function destroyAll(Request $request)
    {
        foreach (Soporte::all() as $soporte) {
            if ($soporte->adjunto) {
                $filePath = public_path('uploads/soporte/' . $soporte->adjunto);
                if (File::exists($filePath)) {
                    @File::delete($filePath);
                }
            }
            $soporte->delete();
        }

        $this->notifyHubTicketAction('delete_ticket', ['local_id' => 'all', 'client_id' => 'cefi']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Todos los reportes de prueba fueron eliminados.']);
        }

        return redirect()->back()->with('success', 'Todos los reportes de avería fueron eliminados.');
    }

    /**
     * Lista general / bitácora pública de soporte y soluciones.
     */
    public function lista(Request $request)
    {
        $categorias = SoporteCategoria::orderBy('nombre', 'asc')->get();

        $query = Soporte::with(['categoria', 'reportadoA', 'creador']);

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->search . '%')
                  ->orWhere('problema', 'like', '%' . $request->search . '%')
                  ->orWhere('solucion', 'like', '%' . $request->search . '%');
            });
        }

        $soportes = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        return view('soporte.lista', compact('categorias', 'soportes'));
    }

    /**
     * Gestión de Categorías de Soporte.
     */
    public function categorias()
    {
        $categorias = SoporteCategoria::withCount('soportes')->orderBy('nombre', 'asc')->get();
        return view('soporte.categorias', compact('categorias'));
    }

    /**
     * Guarda categoría nueva.
     */
    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:soporte_categorias,nombre'
        ]);

        SoporteCategoria::create(['nombre' => $request->nombre]);

        return redirect()->route('soporte.categorias')->with('success', 'Categoría añadida con éxito.');
    }

    /**
     * Actualiza categoría.
     */
    public function updateCategoria(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:soporte_categorias,nombre,' . $id
        ]);

        $cat = SoporteCategoria::findOrFail($id);
        $cat->update(['nombre' => $request->nombre]);

        return redirect()->route('soporte.categorias')->with('success', 'Categoría actualizada con éxito.');
    }

    /**
     * Elimina categoría validando la clave maestra.
     */
    public function destroyCategoria(Request $request, $id)
    {
        $request->validate([
            'clave' => 'required'
        ]);

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        if ($request->clave !== $masterKey && $request->clave !== 'unela2026') {
            return redirect()->route('soporte.categorias')->with('error', 'Clave de confirmación incorrecta. Categoría no eliminada.');
        }

        $cat = SoporteCategoria::findOrFail($id);
        $cat->delete();

        return redirect()->route('soporte.categorias')->with('success', 'Categoría eliminada con éxito.');
    }

    /**
     * Reporte Rápido Omnicanal de Averías desde el ícono superior de cualquier pantalla.
     * Soporta Evidencia Gráfica (archivo subido o captura pegada con Ctrl+V en Base64),
     * guarda en BD local, sincroniza con https://hub.renangalvan.net/api/tickets.php,
     * envía a WhatsApp vía n8n y notifica por correo electrónico.
     */
    public function reportarRapido(Request $request)
    {
        $request->validate([
            'titulo'            => 'required|string|max:255',
            'etiqueta'          => 'nullable|string|max:120',
            'prioridad'         => 'nullable|string|max:40',
            'problema'          => 'required|string',
            'url_origen'        => 'nullable|string|max:500',
            'evidencia_grafica' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:8192',
            'evidencia_base64'  => 'nullable|string',
        ]);

        $uploadDir = public_path('uploads/soporte');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0775, true);
        }

        $adjuntoNombre = null;
        if ($request->hasFile('evidencia_grafica')) {
            $file = $request->file('evidencia_grafica');
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'png');
            $adjuntoNombre = 'averia_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
            $file->move($uploadDir, $adjuntoNombre);
        } elseif ($request->filled('evidencia_base64')) {
            $b64 = $request->input('evidencia_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $b64, $typeMatch)) {
                $ext = strtolower($typeMatch[1] === 'jpeg' ? 'jpg' : $typeMatch[1]);
                $rawImg = base64_decode(substr($b64, strpos($b64, ',') + 1));
                if ($rawImg !== false) {
                    $adjuntoNombre = 'averia_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                    File::put($uploadDir . '/' . $adjuntoNombre, $rawImg);
                }
            }
        }

        $etiqueta  = trim($request->input('etiqueta', '🟠 Fallo en Módulo'));
        $prioridad = strtolower(trim($request->input('prioridad', 'alta')));
        $urlOrigen = trim($request->input('url_origen', url()->previous()));

        // Buscar o crear la categoría equivalente en soporte_categorias
        $cleanCatName = trim(preg_replace('/^[^\w\sáéíóúÁÉÍÓÚñÑ]+\s*/u', '', $etiqueta)) ?: 'General';
        $catObj = SoporteCategoria::firstOrCreate(['nombre' => $cleanCatName]);

        // Asignar por defecto al primer Administrador General
        $adminTarget = Usuario::where('id_rol', 1)->orderBy('id', 'asc')->first();

        $problemaEnriquecido = "[Etiqueta: {$etiqueta} | Prioridad: " . strtoupper($prioridad) . "]\n"
            . ($urlOrigen ? "[Pantalla Origen: {$urlOrigen}]\n\n" : "\n")
            . trim($request->input('problema'));

        $soporte = Soporte::create([
            'titulo'       => trim($request->input('titulo')),
            'problema'     => $problemaEnriquecido,
            'solucion'     => null,
            'categoria_id' => $catObj ? $catObj->id : null,
            'id_creador'   => Auth::id(),
            'reportado_a'  => $adminTarget ? $adminTarget->id : Auth::id(),
            'estado'       => 'reportado',
            'adjunto'      => $adjuntoNombre,
        ]);

        $evidenciaUrl = $adjuntoNombre ? url('uploads/soporte/' . $adjuntoNombre) : '';
        $user = Auth::user();

        // Sincronizar con Inbox Cloud Hub (https://hub.renangalvan.net/api/tickets.php) + n8n WhatsApp + Correo
        $this->dispatchOmnicanalAveria([
            'local_id'       => $soporte->id,
            'client_id'      => env('INBOX_CLIENT_ID', 'inbox_cefi'),
            'client_siglas'  => config('cliente.siglas', 'CEFI'),
            'client_nombre'  => config('cliente.nombre', 'Inbox CEFI'),
            'titulo'         => $soporte->titulo,
            'etiqueta'       => $etiqueta,
            'prioridad'      => $prioridad,
            'problema'       => trim($request->input('problema')),
            'estado'         => 'reportado',
            'url_origen'     => $urlOrigen,
            'navegador'      => $request->header('User-Agent', ''),
            'evidencia_url'  => $evidenciaUrl,
            'usuario_nombre' => $user ? trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')) : 'Usuario',
            'usuario_email'  => $user->correo ?? $user->email ?? '',
            'usuario_rol'    => ($user && $user->id_rol == 1) ? 'Administrador' : 'Usuario / Docente',
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => '¡Avería reportada! Se notificó al Centro de Control, WhatsApp de Soporte y Correo.',
                'ticket_id'     => $soporte->id,
                'evidencia_url' => $evidenciaUrl,
            ]);
        }

        return redirect()->back()->with('success', '¡Avería #' . $soporte->id . ' reportada y enviada a Soporte Técnico!');
    }

    /**
     * Envía la avería hacia Inbox Cloud Hub (que dispara WhatsApp n8n + Email)
     * y envía simultáneamente el correo directo al Proveedor/Creador (renangalvan@gmail.com).
     */
    protected function dispatchOmnicanalAveria(array $payload): void
    {
        $payload['creator_email'] = self::CREATOR_PROVIDER_EMAIL;
        $secret = config('services.inbox_hub.secret', 'RenanHub2026_MasterHmacSecretKey_998877!');
        $hubUrl = config('services.inbox_hub.tickets_url', 'https://hub.renangalvan.net/api/tickets.php');

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature   = hash_hmac('sha256', $jsonPayload, $secret);

        try {
            $ch = curl_init($hubUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonPayload,
                CURLOPT_USERAGENT      => 'InboxBPM-HubClient/2.5',
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-Inbox-Hub-Signature: ' . $signature,
                    'X-Inbox-Client-Id: ' . ($payload['client_id'] ?? 'cefi'),
                ],
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // Continuar con envío de correo directo
        }

        // Envío directo e interno al correo fijo del Creador / Proveedor (renangalvan@gmail.com)
        try {
            $subject = "🚨 [AVERÍA " . strtoupper($payload['prioridad'] ?? 'ALTA') . "] " . ($payload['client_siglas'] ?? 'CEFI') . ": " . ($payload['titulo'] ?? 'Reporte de Sistema');
            $evHtml = '';
            if (!empty($payload['evidencia_url'])) {
                $evUrl = htmlspecialchars($payload['evidencia_url']);
                $evHtml = "<p><strong>📸 Evidencia Gráfica:</strong><br><a href='{$evUrl}' target='_blank'>Ver captura completa</a><br><img src='{$evUrl}' style='max-width:520px;border-radius:8px;margin-top:8px;'></p>";
            }
            $htmlBody = "
                <div style='font-family:Arial,sans-serif;max-width:620px;padding:20px;background:#1e293b;color:#f8fafc;border-radius:12px;'>
                    <h2 style='color:#f87171;margin-top:0;'>🚨 Nueva Avería Reportada — " . htmlspecialchars($payload['client_nombre'] ?? 'Inbox CEFI') . "</h2>
                    <p><strong>Ticket:</strong> #" . htmlspecialchars((string)($payload['local_id'] ?? '')) . "</p>
                    <p><strong>Etiqueta / Causa:</strong> " . htmlspecialchars($payload['etiqueta'] ?? '') . "</p>
                    <p><strong>Prioridad:</strong> " . strtoupper(htmlspecialchars($payload['prioridad'] ?? 'Alta')) . "</p>
                    <p><strong>Reportado por:</strong> " . htmlspecialchars(($payload['usuario_nombre'] ?? '') . ' (' . ($payload['usuario_email'] ?? '') . ')') . "</p>
                    <p><strong>URL del Fallo:</strong> <a href='" . htmlspecialchars($payload['url_origen'] ?? '#') . "' style='color:#38bdf8;'>" . htmlspecialchars($payload['url_origen'] ?? '') . "</a></p>
                    <div style='padding:14px;background:#0f172a;border-left:4px solid #f59e0b;border-radius:8px;margin:14px 0;'>
                        <strong>" . htmlspecialchars($payload['titulo'] ?? '') . "</strong><br>
                        <span style='white-space:pre-line;'>" . htmlspecialchars($payload['problema'] ?? '') . "</span>
                    </div>
                    {$evHtml}
                    <p style='margin-top:18px;'><a href='https://hub.renangalvan.net/?tab=averias' style='background:#10b981;color:#0f172a;padding:10px 20px;border-radius:999px;text-decoration:none;font-weight:bold;'>Gestionar en Inbox Cloud Hub</a></p>
                </div>
            ";
            \Illuminate\Support\Facades\Mail::html($htmlBody, function ($message) use ($subject) {
                $message->to(self::CREATOR_PROVIDER_EMAIL)
                        ->subject($subject);
            });
        } catch (\Throwable $e) {
            // Fallback silencioso si el servidor local no tiene SMTP configurado aún
        }
    }

    /**
     * Notifica al Hub cuando se elimina o actualiza una avería desde la plataforma cliente.
     */
    protected function notifyHubTicketAction(string $action, array $data): void
    {
        $secret = config('services.inbox_hub.secret', 'RenanHub2026_MasterHmacSecretKey_998877!');
        $hubUrl = config('services.inbox_hub.tickets_url', 'https://hub.renangalvan.net/api/tickets.php');

        $payload = array_merge($data, ['action' => $action]);
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature   = hash_hmac('sha256', $jsonPayload, $secret);

        try {
            $ch = curl_init($hubUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $jsonPayload,
                CURLOPT_USERAGENT      => 'InboxBPM-HubClient/2.5',
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-Inbox-Hub-Signature: ' . $signature,
                    'X-Inbox-Client-Id: cefi',
                ],
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {}
    }
}
