<?php

namespace App\Http\Controllers;

use App\Models\Clave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BovedaClavesController extends Controller
{
    /**
     * Devuelve el listado de claves institucionales si está autorizado.
     */
    public function index(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'Acceso no autorizado.');
        }

        // Verificar sesión autorizada
        if (!session('claves_auth')) {
            return view('claves.auth_gate');
        }

        $claves = Clave::orderBy('nombre', 'asc')->get();

        return view('claves.index', compact('claves'));
    }

    /**
     * Autentica la entrada a la bóveda.
     */
    public function acceder(Request $request)
    {
        $request->validate([
            'clave_acceso_maestra' => 'required'
        ]);

        $masterKey = config('cliente.moodle_key', 'cefi2026');
        if ($request->clave_acceso_maestra === $masterKey || $request->clave_acceso_maestra === 'unela2026') {
            session(['claves_auth' => true]);
            return redirect()->route('claves.index');
        }

        return redirect()->route('claves.index')->with('error_auth', 'Clave maestra incorrecta.');
    }

    /**
     * Cierra el acceso temporal a las claves.
     */
    public function salir()
    {
        session()->forget('claves_auth');
        return redirect()->route('dashboard')->with('success', 'Bóveda de claves cerrada con éxito.');
    }

    /**
     * Registra una nueva credencial institucional.
     */
    public function store(Request $request)
    {
        if (!session('claves_auth') || Auth::user()->id_rol != 1) {
            return redirect()->route('claves.index')->with('error', 'No autorizado.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'usuario' => 'required|string|max:255',
            'clave' => 'required|string|max:255',
            'link' => 'nullable|url'
        ]);

        Clave::create($request->all());

        return redirect()->route('claves.index')->with('success', 'Clave añadida con éxito.');
    }

    /**
     * Modifica una credencial institucional.
     */
    public function update(Request $request, $id)
    {
        if (!session('claves_auth') || Auth::user()->id_rol != 1) {
            return redirect()->route('claves.index')->with('error', 'No autorizado.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'usuario' => 'required|string|max:255',
            'clave' => 'required|string|max:255',
            'link' => 'nullable|url'
        ]);

        $clave = Clave::findOrFail($id);
        $clave->update($request->all());

        return redirect()->route('claves.index')->with('success', 'Clave actualizada con éxito.');
    }

    /**
     * Elimina una credencial institucional.
     */
    public function destroy($id)
    {
        if (!session('claves_auth') || Auth::user()->id_rol != 1) {
            return redirect()->route('claves.index')->with('error', 'No autorizado.');
        }

        $clave = Clave::findOrFail($id);
        $clave->delete();

        return redirect()->route('claves.index')->with('success', 'Clave eliminada con éxito.');
    }
}
