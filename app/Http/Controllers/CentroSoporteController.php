<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use App\Models\Plataforma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CentroSoporteController extends Controller
{
    /**
     * Muestra la interfaz del Centro de Soporte - Credenciales.
     */
    public function index(Request $request)
    {
        $buscar = trim($request->input('buscar', $request->input('search', '')));

        $plataformas = Plataforma::orderBy('nombre', 'asc')->get();

        $query = Credencial::with('creador')
            ->select('credenciales.*')
            ->selectRaw('plataformas.nombre as nombre_plataforma')
            ->leftJoin('plataformas', function($join) {
                $join->on(\DB::raw('TRIM(LOWER(credenciales.link_acceso))'), '=', \DB::raw('TRIM(LOWER(plataformas.link_acceso))'));
            });

        if (!empty($buscar)) {
            $query->where(function($q) use ($buscar) {
                $q->where('credenciales.usuario', 'like', "%{$buscar}%")
                  ->orWhere('credenciales.tipo', 'like', "%{$buscar}%")
                  ->orWhere('credenciales.datos_link', 'like', "%{$buscar}%")
                  ->orWhere('credenciales.link_acceso', 'like', "%{$buscar}%")
                  ->orWhere('plataformas.nombre', 'like', "%{$buscar}%");
            });
        }

        $credenciales = $query->orderBy('credenciales.fecha', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('centro_soporte.index', compact('plataformas', 'credenciales', 'buscar'));
    }

    /**
     * Registra una plataforma institucional.
     */
    public function storePlataforma(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:plataformas,nombre',
            'link_acceso' => 'required|url|unique:plataformas,link_acceso'
        ]);

        Plataforma::create([
            'nombre' => $request->nombre,
            'link_acceso' => $request->link_acceso
        ]);

        return redirect()->route('centro_soporte.index')->with('success', 'Plataforma registrada con éxito.');
    }

    /**
     * Actualiza una plataforma institucional.
     */
    public function updatePlataforma(Request $request, $id)
    {
        $plataforma = Plataforma::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:plataformas,nombre,' . $plataforma->id_plataforma . ',id_plataforma',
            'link_acceso' => 'required|url'
        ]);

        $oldLink = $plataforma->link_acceso;
        $plataforma->update([
            'nombre' => $request->nombre,
            'link_acceso' => $request->link_acceso,
        ]);

        // Si cambió el link, sincronizar las credenciales vinculadas
        if ($oldLink !== $request->link_acceso) {
            Credencial::where('link_acceso', $oldLink)->update(['link_acceso' => $request->link_acceso]);
        }

        return redirect()->route('centro_soporte.index')->with('success', 'Plataforma actualizada con éxito.');
    }

    /**
     * Elimina una plataforma institucional.
     */
    public function destroyPlataforma(Request $request, $id)
    {
        $plataforma = Plataforma::findOrFail($id);
        $plataforma->delete();

        return redirect()->route('centro_soporte.index')->with('success', 'Plataforma eliminada correctamente.');
    }

    /**
     * Registra una credencial de soporte.
     */
    public function storeCredencial(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:100',
            'clave' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'link_acceso' => 'nullable|url',
            'datos_link' => 'nullable|string'
        ]);

        Credencial::create([
            'usuario' => $request->usuario,
            'clave' => $request->clave,
            'tipo' => $request->tipo ?: 'Cuenta Nueva',
            'link_acceso' => $request->link_acceso,
            'datos_link' => $request->datos_link,
            'creado_por' => Auth::id()
        ]);

        return redirect()->route('centro_soporte.index')->with('success', 'Credencial guardada con éxito.');
    }

    /**
     * Actualiza una credencial de soporte.
     */
    public function updateCredencial(Request $request, $id)
    {
        $request->validate([
            'usuario' => 'required|string|max:100',
            'clave' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'link_acceso' => 'nullable|url',
            'datos_link' => 'nullable|string'
        ]);

        $cred = Credencial::findOrFail($id);
        $cred->update([
            'usuario' => $request->usuario,
            'clave' => $request->clave,
            'tipo' => $request->tipo ?: 'Cuenta Nueva',
            'link_acceso' => $request->link_acceso,
            'datos_link' => $request->datos_link,
        ]);

        return redirect()->route('centro_soporte.index')->with('success', 'Credencial actualizada con éxito.');
    }

    /**
     * Elimina una credencial validando la clave maestra.
     */
    public function destroyCredencial(Request $request, $id)
    {
        $request->validate([
            'clave' => 'required'
        ]);

        if ($request->clave !== 'unela2026') {
            return redirect()->route('centro_soporte.index')->with('error', 'Clave incorrecta. Registro no eliminado.');
        }

        $cred = Credencial::findOrFail($id);
        $cred->delete();

        return redirect()->route('centro_soporte.index')->with('success', 'Registro eliminado correctamente.');
    }
}
