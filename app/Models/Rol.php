<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            $adminOp = DB::table('roles')
                ->whereRaw('LOWER(TRIM(nombre)) = ?', ['administrador'])
                ->first();

            if ($adminOp) {
                if ($adminOp->nombre !== 'Administrador') {
                    DB::table('roles')->where('id', $adminOp->id)->update(['nombre' => 'Administrador']);
                }
            } else {
                try {
                    DB::table('roles')->insert(['nombre' => 'Administrador']);
                } catch (\Throwable $e) {
                    $nextId = ((int) DB::table('roles')->max('id')) + 1;
                    if ($nextId < 4) {
                        $nextId = 4;
                    }
                    DB::table('roles')->insert([
                        'id' => $nextId,
                        'nombre' => 'Administrador',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error en Rol::asegurarOficiales: ' . $e->getMessage());
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

        $roles = self::all()->keyBy(fn ($r) => mb_strtolower(trim((string) $r->nombre)));

        return collect($nombres)
            ->map(fn (string $nombre) => $roles->get(mb_strtolower($nombre)))
            ->filter()
            ->values();
    }
}
