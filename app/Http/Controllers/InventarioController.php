<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InventarioController extends Controller
{
    /**
     * Muestra la lista de productos del inventario.
     */
    public function index(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $id_cat_filter = $request->input('categoria');

        // Obtener categorías para el filtro
        $categorias = DB::table('inventario_categorias')->orderBy('nombre', 'asc')->get();

        // Obtener productos
        $query = DB::table('inventario_productos as p')
            ->leftJoin('inventario_categorias as c', 'p.id_categoria', '=', 'c.id')
            ->select('p.*', 'c.nombre as categoria_nombre');

        if ($id_cat_filter) {
            $query->where('p.id_categoria', $id_cat_filter);
        }

        $productos = $query->orderBy('p.nombre', 'asc')->get();

        // Alertas rápidas de bajo stock
        $bajo_stock = $productos->filter(function($p) {
            return $p->stock_actual <= $p->stock_minimo && $p->estado == 'activo';
        });

        return view('inventario.index', compact('categorias', 'productos', 'id_cat_filter', 'bajo_stock'));
    }

    /**
     * Muestra el formulario para realizar operaciones de bodega.
     */
    public function operaciones()
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Obtener productos activos
        $productos = DB::table('inventario_productos')
            ->where('estado', 'activo')
            ->orderBy('nombre', 'asc')
            ->get();

        return view('inventario.operaciones', compact('productos'));
    }

    /**
     * Registra un movimiento de entrada o salida de bodega.
     */
    public function registrarMovimiento(Request $request)
    {
        if (Auth::user()->id_rol != 1) {
            return redirect()->route('dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $request->validate([
            'id_producto' => 'required|integer',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'pin' => 'required|string',
            'comentario' => 'nullable|string|max:500'
        ]);

        $id_usuario = Auth::id();

        try {
            DB::beginTransaction();

            // 1. Validar PIN del usuario
            $usuario = DB::table('usuarios')
                ->where('id', $id_usuario)
                ->where('pin_bodega', $request->pin)
                ->first();

            if (!$usuario) {
                throw new \Exception("PIN de autorización incorrecto.");
            }

            // 2. Obtener datos actuales del producto
            $producto = DB::table('inventario_productos')
                ->where('id', $request->id_producto)
                ->first();

            if (!$producto) {
                throw new \Exception("Producto no encontrado.");
            }

            // 3. Calcular nuevo stock
            $nuevo_stock = ($request->tipo === 'entrada') 
                ? $producto->stock_actual + $request->cantidad 
                : $producto->stock_actual - $request->cantidad;

            if ($nuevo_stock < 0) {
                throw new \Exception("No hay suficiente stock para realizar esta salida.");
            }

            // 4. Actualizar producto
            $updateData = ['stock_actual' => $nuevo_stock];
            if ($request->tipo === 'entrada') {
                $updateData['fecha_ultima_renovacion'] = Carbon::now();
                $updateData['ultima_alerta_en'] = null;
            }
            DB::table('inventario_productos')->where('id', $request->id_producto)->update($updateData);

            // 5. Registrar movimiento
            DB::table('inventario_movimientos')->insert([
                'id_producto' => $request->id_producto,
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'id_usuario' => $id_usuario,
                'comentario' => $request->comentario ?? '',
                'fecha' => Carbon::now()
            ]);

            DB::commit();
            return redirect()->route('inventario.index')->with('success', 'Operación registrada con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
