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
    public static ?string $ultimoError = null;

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
        self::$ultimoError = null;

        try {
            // 1. Administrador General: si no existe por nombre, renombrar id=1 o insertar
            $adminGen = DB::table('roles')
                ->whereRaw('LOWER(TRIM(nombre)) = ?', ['administrador general'])
                ->first();

            if (!$adminGen) {
                $rol1 = DB::table('roles')->where('id', 1)->first();
                if ($rol1) {
                    DB::table('roles')->where('id', 1)->update(['nombre' => 'Administrador General']);
                } else {
                    self::insertarRolSeguro('Administrador General');
                }
            }

            // 2. Administrador, Profesor, Estudiante: respetar su ID existente si ya existen por nombre; si no, insertar
            foreach (['Administrador', 'Profesor', 'Estudiante'] as $nombreRol) {
                $existe = DB::table('roles')
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombreRol)])
                    ->first();

                if (!$existe) {
                    self::insertarRolSeguro($nombreRol);
                } elseif ($existe->nombre !== $nombreRol) {
                    DB::table('roles')->where('id', $existe->id)->update(['nombre' => $nombreRol]);
                }
            }
        } catch (\Throwable $e) {
            self::$ultimoError = $e->getMessage();
            Log::error('Error en Rol::asegurarOficiales: ' . $e->getMessage());
        }
    }

    /**
     * Inserta un rol respetando columnas obligatorias/únicas y autoincremento o ID manual.
     */
    protected static function insertarRolSeguro(string $nombreRol): void
    {
        $slug = mb_strtolower(str_replace(' ', '_', $nombreRol));
        $payload = ['nombre' => $nombreRol];
        $necesitaIdManual = true;

        try {
            $columnas = DB::select('SHOW COLUMNS FROM roles');
            foreach ($columnas as $col) {
                $field = $col->Field ?? '';
                $type = strtolower((string) ($col->Type ?? ''));
                $null = strtoupper((string) ($col->Null ?? 'YES'));
                $default = $col->Default ?? null;
                $extra = strtolower((string) ($col->Extra ?? ''));
                $key = strtoupper((string) ($col->Key ?? ''));

                if ($field === 'id') {
                    if (str_contains($extra, 'auto_increment')) {
                        $necesitaIdManual = false;
                    }
                    continue;
                }

                if ($field === 'nombre') {
                    $payload['nombre'] = $nombreRol;
                    continue;
                }

                if (($null === 'NO' && $default === null) || $key === 'UNI') {
                    if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float')) {
                        $payload[$field] = 1;
                    } elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                        $payload[$field] = date('Y-m-d H:i:s');
                    } else {
                        $payload[$field] = $slug;
                    }
                }
            }
        } catch (\Throwable $e) {
            $necesitaIdManual = false;
        }

        try {
            if ($necesitaIdManual) {
                $nextId = ((int) DB::table('roles')->max('id')) + 1;
                $payload['id'] = max(4, $nextId);
            }
            DB::table('roles')->insert($payload);
        } catch (\Throwable $e) {
            $nextId = ((int) DB::table('roles')->max('id')) + 1;
            $payload['id'] = max(4, $nextId);
            DB::table('roles')->insert($payload);
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
