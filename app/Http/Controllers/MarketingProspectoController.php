<?php

namespace App\Http\Controllers;

use App\Models\MarketingProspecto;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

class MarketingProspectoController extends Controller
{
    /**
     * Asegura que la tabla exista en la base de datos automáticamente.
     */
    private function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('marketing_prospectos')) {
                Schema::create('marketing_prospectos', function (Blueprint $table) {
                    $table->id();
                    $table->string('nombre', 100)->nullable();
                    $table->string('apellidos', 100)->nullable();
                    $table->string('telefono', 30)->index();
                    $table->string('email', 150)->nullable()->index();
                    $table->string('origen', 100)->default('Base Externa')->index();
                    $table->string('interes', 100)->nullable();
                    $table->enum('estado', ['activo', 'contactado', 'baja'])->default('activo')->index();
                    $table->timestamp('ultimo_contacto_at')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            Log::warning("Error verificando tabla marketing_prospectos: " . $e->getMessage());
        }
    }

    /**
     * Muestra la lista de prospectos externos con buscador, KPIs y filtros.
     */
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $query = MarketingProspecto::query();

        // Filtro de búsqueda
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        // Filtro por Origen de la base
        if ($request->filled('origen') && $request->origen !== 'todos') {
            $query->where('origen', $request->origen);
        }

        // Filtro por Estado
        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        $prospectos = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        // Estadísticas KPIs
        $totalProspectos = MarketingProspecto::count();
        $totalActivosWhatsApp = MarketingProspecto::activosConWhatsApp()->count();
        $origenes = MarketingProspecto::select('origen')->distinct()->pluck('origen')->filter()->values();

        return view('marketing.prospectos.index', compact(
            'prospectos',
            'totalProspectos',
            'totalActivosWhatsApp',
            'origenes'
        ));
    }

    /**
     * Registra un prospecto individualmente.
     */
    public function store(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'telefono' => 'required|string|min:8',
            'nombre' => 'nullable|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'origen' => 'nullable|string|max:100',
            'interes' => 'nullable|string|max:100'
        ]);

        $telLimpio = WhatsAppService::normalizarTelefono($request->telefono);
        if (empty($telLimpio)) {
            return back()->with('error', 'El número de teléfono proporcionado no es válido.');
        }

        $origen = trim($request->input('origen', 'Manual'));
        if (empty($origen)) {
            $origen = 'Manual';
        }

        MarketingProspecto::updateOrCreate(
            ['telefono' => $telLimpio],
            [
                'nombre' => trim($request->nombre ?? ''),
                'apellidos' => trim($request->apellidos ?? ''),
                'email' => trim($request->email ?? ''),
                'origen' => $origen,
                'interes' => trim($request->interes ?? ''),
                'estado' => 'activo'
            ]
        );

        return back()->with('success', "Prospecto {$request->nombre} ({$telLimpio}) guardado exitosamente.");
    }

    /**
     * Carga rápida de prospectos mediante copiado y pegado directo de texto o columnas de Excel.
     */
    public function importarPegado(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'texto_pegado' => 'required|string',
            'origen_lote' => 'required|string|max:100',
            'interes_lote' => 'nullable|string|max:100'
        ]);

        $rawLines = explode("\n", $request->texto_pegado);
        $origen = trim($request->origen_lote);
        $interes = trim($request->input('interes_lote', ''));

        $insertados = 0;
        $actualizados = 0;
        $telefonosVistos = [];

        foreach ($rawLines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // 1. Extraer Email si existe
            $email = null;
            if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $line, $emailMatch)) {
                $email = $emailMatch[0];
                $line = str_replace($email, '', $line);
            }

            // 2. Extraer Teléfono (8 o más dígitos consecutivos o con guiones/espacios)
            $tel = null;
            if (preg_match('/(?:\+?506)?[\s-]?[2-8]\d{3}[\s-]?\d{4}|\+?\d{8,15}/', $line, $telMatch)) {
                $tel = WhatsAppService::normalizarTelefono($telMatch[0]);
                $line = str_replace($telMatch[0], '', $line);
            }

            if (empty($tel) || strlen($tel) < 8) {
                continue;
            }

            // Evitar duplicados dentro del mismo lote pegado
            if (isset($telefonosVistos[$tel])) {
                continue;
            }
            $telefonosVistos[$tel] = true;

            // 3. El resto de la línea es el nombre
            $nombreLimpio = trim(preg_replace('/[,\t;]+/', ' ', $line));
            $nombre = null;
            $apellidos = null;

            if (!empty($nombreLimpio)) {
                $partes = explode(' ', $nombreLimpio, 2);
                $nombre = $partes[0];
                $apellidos = $partes[1] ?? '';
            }

            $prospecto = MarketingProspecto::where('telefono', $tel)->first();
            if ($prospecto) {
                // Actualizar si no tenía nombre
                if (empty($prospecto->nombre) && !empty($nombre)) {
                    $prospecto->nombre = $nombre;
                    $prospecto->apellidos = $apellidos;
                }
                $prospecto->origen = $origen;
                if (!empty($interes)) {
                    $prospecto->interes = $interes;
                }
                $prospecto->save();
                $actualizados++;
            } else {
                MarketingProspecto::create([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'telefono' => $tel,
                    'email' => $email,
                    'origen' => $origen,
                    'interes' => $interes,
                    'estado' => 'activo'
                ]);
                $insertados++;
            }
        }

        $total = $insertados + $actualizados;
        return back()->with('success', "¡Procesamiento completado! Se registraron {$insertados} nuevos prospectos y se actualizaron {$actualizados} existentes bajo el origen '{$origen}'.");
    }

    /**
     * Importación masiva desde archivo CSV.
     */
    public function importarCsv(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:10240',
            'origen_archivo' => 'required|string|max:100'
        ]);

        $file = $request->file('archivo_csv');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'No se pudo leer el archivo CSV.');
        }

        $origen = trim($request->origen_archivo);
        $insertados = 0;
        $rowNum = 0;

        while (($data = fgetcsv($handle, 2000, ",")) !== false) {
            $rowNum++;
            if ($rowNum === 1 && !is_numeric(preg_replace('/\D/', '', $data[0] ?? ''))) {
                // Posible cabecera (Header), saltar
                continue;
            }

            // Formatos usuales: Col 0: Nombre, Col 1: Teléfono, Col 2: Email (o Col 0 Teléfono)
            $col0 = trim($data[0] ?? '');
            $col1 = trim($data[1] ?? '');
            $col2 = trim($data[2] ?? '');

            $tel = '';
            $nombre = '';
            $email = '';

            if (is_numeric(preg_replace('/\D/', '', $col0)) && strlen(preg_replace('/\D/', '', $col0)) >= 8) {
                $tel = $col0;
                $nombre = $col1;
            } else {
                $nombre = $col0;
                $tel = $col1;
                $email = $col2;
            }

            $cleanTel = WhatsAppService::normalizarTelefono($tel);
            if (empty($cleanTel) || strlen($cleanTel) < 8) {
                continue;
            }

            MarketingProspecto::updateOrCreate(
                ['telefono' => $cleanTel],
                [
                    'nombre' => $nombre ?: 'Prospecto',
                    'email' => $email ?: null,
                    'origen' => $origen,
                    'estado' => 'activo'
                ]
            );
            $insertados++;
        }

        fclose($handle);

        return back()->with('success', "¡Archivo CSV procesado! Se registraron/actualizaron {$insertados} prospectos correctamente.");
    }

    /**
     * Elimina un prospecto individual.
     */
    public function destroy($id)
    {
        $this->ensureTableExists();

        $p = MarketingProspecto::findOrFail($id);
        $p->delete();

        return back()->with('success', 'Prospecto eliminado correctamente.');
    }

    /**
     * Elimina todos los prospectos de un origen específico.
     */
    public function eliminarPorOrigen(Request $request)
    {
        $this->ensureTableExists();

        $request->validate(['origen_eliminar' => 'required|string']);

        $count = MarketingProspecto::where('origen', $request->origen_eliminar)->delete();

        return back()->with('success', "Se eliminaron {$count} prospectos pertenecientes al origen '{$request->origen_eliminar}'.");
    }

    /**
     * Endpoint AJAX para consultar prospectos disponibles para campañas.
     */
    public function prospectosAjax(Request $request)
    {
        $this->ensureTableExists();

        $origen = $request->input('origen');

        $query = MarketingProspecto::activosConWhatsApp();

        if (!empty($origen) && $origen !== 'todos') {
            $query->where('origen', $origen);
        }

        $total = $query->count();
        $muestra = $query->limit(8)->get(['id', 'nombre', 'apellidos', 'telefono', 'origen']);

        return response()->json([
            'total' => $total,
            'muestra' => $muestra
        ]);
    }
}
