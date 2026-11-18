<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    public $timestamps = false;

    public const NOMBRES_OFICIALES = [
        'Administrador General',
        'Administrador',
        'Profesor',
        'Estudiante',
    ];

    protected static bool $oficialesAsegurados = false;

    protected $fillable = [
        'nombre'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id');
    }

    /**
     * Garantiza en tiempo de ejecución que los 4 roles oficiales existan en la tabla roles.
     */
    public static function asegurarOficiales(): void
    {
        if (self::$oficialesAsegurados) {
            return;
        }
        self::$oficialesAsegurados = true;

        try {
            DB::table('roles')->updateOrInsert(['id' => 1], ['nombre' => 'Administrador General']);
            DB::table('roles')->updateOrInsert(['id' => 2], ['nombre' => 'Profesor']);
            DB::table('roles')->updateOrInsert(['id' => 3], ['nombre' => 'Estudiante']);

            if (!DB::table('roles')->where('nombre', 'Administrador')->exists()) {
                DB::table('roles')->insert(['nombre' => 'Administrador']);
            }
        } catch (\Throwable $e) {
            // Ignorar si la tabla aún no está disponible
        }
    }

    /**
     * Retorna únicamente los 4 roles oficiales ordenados jerárquicamente.
     */
    public static function oficiales(bool $incluirAdminGeneral = true): Collection
    {
        self::asegurarOficiales();

        $nombres = $incluirAdminGeneral
            ? self::NOMBRES_OFICIALES
            : ['Administrador', 'Profesor', 'Estudiante'];

        $roles = self::whereIn('nombre', $nombres)->get()->keyBy('nombre');

        return collect($nombres)
            ->map(fn (string $nombre) => $roles->get($nombre))
            ->filter()
            ->values();
    }
}
