<?php

namespace App\Http\Controllers;

use App\Models\CursoActivo;
use App\Models\PlanEstudio;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilitarioController extends Controller
{
    /**
     * Muestra el panel de utilitarios restringido.
     */
    public function index()
    {
        $autorizado = session('utilitarios_auth', false);

        if (!$autorizado) {
            return view('mantenimiento.utilitarios_login');
        }

        // Cargar todos los grupos activos
        $grupos = CursoActivo::with(['planEstudio', 'profesor'])
                             ->orderBy('id_curso_activo', 'desc')
                             ->get();

        // Cargar diccionario de materias base
        $materias = PlanEstudio::with('programa')
                               ->orderBy('id_programa', 'asc')
                               ->orderBy('materia', 'asc')
                               ->get();

        return view('mantenimiento.utilitarios', compact('grupos', 'materias'));
    }

    /**
     * Procesa la clave maestra para dar acceso a utilitarios.
     */
    public function acceder(Request $request)
    {
        $request->validate([
            'clave_acceso' => 'required|string'
        ]);

        if ($request->clave_acceso === 'unela2026') {
            session(['utilitarios_auth' => true]);
            return redirect()->route('utilitarios.index');
        }

        return redirect()->route('utilitarios.index')->with('error', 'Clave de acceso maestra incorrecta.');
    }

    /**
     * Elimina definitivamente un grupo desde el área de utilitarios.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // 1. Eliminar matrículas asociadas
            Matricula::where('id_curso_activo', $id)->delete();

            // 2. Eliminar el curso activo
            CursoActivo::destroy($id);

            DB::commit();
            return redirect()->route('utilitarios.index')->with('success', 'Grupo eliminado definitivamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('utilitarios.index')->with('error', 'Error al eliminar el grupo: ' . $e->getMessage());
        }
    }
}
