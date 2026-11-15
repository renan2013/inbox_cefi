<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class BaseDatosEventosController extends Controller
{
    /**
     * Crea automáticamente las tablas eventos_captacion, eventos_participantes y marketing_prospectos si no existen.
     */
    protected function ensureTables(): void
    {
        if (!Schema::hasTable('eventos_captacion')) {
            Schema::create('eventos_captacion', function ($table) {
                $table->increments('id');
                $table->string('codigo', 60)->unique();
                $table->string('nombre', 180);
                $table->string('lugar', 180)->nullable();
                $table->date('fecha_evento')->nullable();
                $table->string('premio', 255)->default('1 Beca / Matrícula 100% Gratuita');
                $table->text('descripcion')->nullable();
                $table->string('estado', 20)->default('activo');
                $table->integer('id_ganador')->nullable();
                $table->string('ganador_nombre', 150)->nullable();
                $table->string('ganador_telefono', 50)->nullable();
                $table->dateTime('fecha_sorteo')->nullable();
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('eventos_participantes')) {
            Schema::create('eventos_participantes', function ($table) {
                $table->increments('id');
                $table->integer('id_evento')->index();
                $table->string('nombre', 150);
                $table->string('telefono', 30);
                $table->string('email', 150)->nullable();
                $table->string('interes', 120)->nullable();
                $table->boolean('es_ganador')->default(false);
                $table->string('ip_registro', 50)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Panel Principal: Eventos & Sorteos en Vivo + Directorio de Contactos
     */
    public function index(Request $request)
    {
        $this->ensureTables();

        $eventos = DB::table('eventos_captacion as e')
            ->leftJoin('eventos_participantes as p', 'e.id', '=', 'p.id_evento')
            ->select('e.*', DB::raw('COUNT(p.id) as total_participantes'))
            ->groupBy('e.id')
            ->orderByDesc('e.id')
            ->get();

        $prospectos = Schema::hasTable('marketing_prospectos')
            ? DB::table('marketing_prospectos')->orderByDesc('id')->limit(300)->get()
            : collect();

        $programas = Schema::hasTable('programas')
            ? DB::table('programas')->orderBy('nombre_programa')->get()
            : collect();

        return view('base_datos.index', compact('eventos', 'prospectos', 'programas'));
    }

    /**
     * Crear o actualizar un Evento de Captación con QR.
     */
    public function storeEvento(Request $request)
    {
        $this->ensureTables();

        $request->validate([
            'nombre' => 'required|string|max:180',
            'premio' => 'required|string|max:255',
        ]);

        $codigo = strtoupper(preg_replace('/[^A-Z0-9-]/', '', $request->input('codigo') ?: ('EVT-' . date('Y') . '-' . rand(100, 999))));

        DB::table('eventos_captacion')->insert([
            'codigo'       => $codigo,
            'nombre'       => trim($request->input('nombre')),
            'lugar'        => trim($request->input('lugar', 'Evento Institucional')),
            'fecha_evento' => $request->input('fecha_evento') ?: date('Y-m-d'),
            'premio'       => trim($request->input('premio')),
            'descripcion'  => trim($request->input('descripcion', '')),
            'estado'       => 'activo',
            'created_by'   => Auth::id(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('base_datos.index')->with('success', "Evento {$codigo} creado y listo para recibir registros con QR.");
    }

    /**
     * Eliminar un evento y sus participantes.
     */
    public function destroyEvento($id)
    {
        $this->ensureTables();
        DB::table('eventos_participantes')->where('id_evento', $id)->delete();
        DB::table('eventos_captacion')->where('id', $id)->delete();

        return redirect()->route('base_datos.index')->with('success', 'Evento eliminado correctamente.');
    }

    /**
     * Pantalla Completa de Ruleta / Sorteo en Vivo para Proyector.
     */
    public function sorteoEnVivo($id)
    {
        $this->ensureTables();
        $evento = DB::table('eventos_captacion')->where('id', $id)->first();
        if (!$evento) {
            abort(404, 'Evento no encontrado');
        }

        $participantes = DB::table('eventos_participantes')
            ->where('id_evento', $id)
            ->orderBy('id', 'asc')
            ->get();

        return view('base_datos.sorteo', compact('evento', 'participantes'));
    }

    /**
     * Registra al ganador oficial del sorteo en vivo.
     */
    public function registrarGanadorSorteo(Request $request, $id)
    {
        $this->ensureTables();
        $idParticipante = (int)$request->input('id_participante');
        $part = DB::table('eventos_participantes')->where('id', $idParticipante)->where('id_evento', $id)->first();

        if (!$part) {
            return response()->json(['success' => false, 'message' => 'Participante no encontrado.'], 404);
        }

        DB::table('eventos_participantes')->where('id_evento', $id)->update(['es_ganador' => 0]);
        DB::table('eventos_participantes')->where('id', $idParticipante)->update(['es_ganador' => 1]);

        DB::table('eventos_captacion')->where('id', $id)->update([
            'id_ganador'       => $part->id,
            'ganador_nombre'   => $part->nombre,
            'ganador_telefono' => $part->telefono,
            'fecha_sorteo'     => now(),
            'updated_at'       => now(),
        ]);

        return response()->json([
            'success' => true,
            'ganador' => $part,
        ]);
    }

    /**
     * Formulario Público (Móvil / QR) para que los asistentes se registren al sorteo en vivo.
     */
    public function registroPublico($codigo)
    {
        $this->ensureTables();
        $evento = DB::table('eventos_captacion')->where('codigo', $codigo)->first();
        if (!$evento) {
            abort(404, 'Evento no disponible.');
        }

        $programas = Schema::hasTable('programas')
            ? DB::table('programas')->orderBy('nombre_programa')->get()
            : collect();

        return view('base_datos.registro_publico', compact('evento', 'programas'));
    }

    /**
     * Guarda el registro público del asistente y lo sincroniza con marketing_prospectos.
     */
    public function guardarRegistroPublico(Request $request, $codigo)
    {
        $this->ensureTables();
        $evento = DB::table('eventos_captacion')->where('codigo', $codigo)->first();
        if (!$evento) {
            abort(404);
        }

        $request->validate([
            'nombre'   => 'required|string|max:150',
            'telefono' => 'required|string|max:30',
        ]);

        $nombre   = trim($request->input('nombre'));
        $telefono = preg_replace('/[^0-9+]/', '', $request->input('telefono'));
        $email    = trim($request->input('email', ''));
        $interes  = trim($request->input('interes', 'General'));

        $existe = DB::table('eventos_participantes')
            ->where('id_evento', $evento->id)
            ->where('telefono', $telefono)
            ->first();

        if (!$existe) {
            DB::table('eventos_participantes')->insert([
                'id_evento'   => $evento->id,
                'nombre'      => $nombre,
                'telefono'    => $telefono,
                'email'       => $email,
                'interes'     => $interes,
                'ip_registro' => $request->ip(),
                'created_at'  => now(),
            ]);
        }

        // Sincronizar también en marketing_prospectos para futuras campañas de WhatsApp
        if (Schema::hasTable('marketing_prospectos')) {
            $existeMkt = DB::table('marketing_prospectos')->where('telefono', $telefono)->first();
            if (!$existeMkt) {
                DB::table('marketing_prospectos')->insert([
                    'nombre'     => $nombre,
                    'apellidos'  => '',
                    'telefono'   => $telefono,
                    'email'      => $email,
                    'origen'     => 'Evento: ' . $evento->nombre,
                    'interes'    => $interes,
                    'estado'     => 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('registro_exitoso', "¡Quedaste registrado oficialmente en el sorteo de {$evento->premio}!");
    }

    /**
     * Exportar Base Completa de Contactos / Participantes en CSV.
     */
    public function exportarCsv(Request $request)
    {
        $this->ensureTables();
        $filename = 'base_contactos_eventos_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['ID', 'Nombre Completo', 'Teléfono', 'Correo Electrónico', 'Programa / Interés', 'Origen / Evento', 'Fecha Registro']);

            if (Schema::hasTable('marketing_prospectos')) {
                $rows = DB::table('marketing_prospectos')->orderByDesc('id')->get();
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        trim(($r->nombre ?? '') . ' ' . ($r->apellidos ?? '')),
                        $r->telefono,
                        $r->email,
                        $r->interes,
                        $r->origen,
                        $r->created_at,
                    ]);
                }
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generador de Cortina Digital de Eventos & Promoción (/cortina).
     */
    public function cortinaIndex()
    {
        $this->ensureTables();
        $eventos = DB::table('eventos_captacion')->orderByDesc('id')->limit(30)->get();
        return view('cortina.index', compact('eventos'));
    }

    /**
     * Proyección Pública en Pantalla Completa de la Cortina Digital (/cortina/evento).
     */
    public function cortinaEvento(Request $request)
    {
        $titulo    = $request->query('titulo', config('cliente.nombre_legal', 'CENTRO DE ENSEÑANZA DE FORMACIÓN INTEGRAL'));
        $subtitulo = $request->query('subtitulo', 'Bienvenidos a Nuestra Sesión en Vivo');
        $expositor = $request->query('expositor', '');
        $minutos   = max(1, min(120, (int)$request->query('minutos', 5)));
        $tema      = $request->query('tema', 'institucional');

        return view('cortina.evento', compact('titulo', 'subtitulo', 'expositor', 'minutos', 'tema'));
    }
}
