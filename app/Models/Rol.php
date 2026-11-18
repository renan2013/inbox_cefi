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
                $payload = ['nombre' => 'Administrador'];
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
                            $payload['nombre'] = 'Administrador';
                            continue;
                        }

                        if (($null === 'NO' && $default === null) || $key === 'UNI') {
                            if (str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float')) {
                                $payload[$field] = 1;
                            } elseif (str_contains($type, 'date') || str_contains($type, 'time')) {
                                $payload[$field] = date('Y-m-d H:i:s');
                            } else {
                                $payload[$field] = 'administrador';
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Continuar con payload mínimo si no es MySQL (ej. SQLite)
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
        } catch (\Throwable $e) {
            self::$ultimoError = $e->getMessage();
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
