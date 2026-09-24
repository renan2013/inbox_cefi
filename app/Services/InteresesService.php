<?php

namespace App\Services;

use App\Models\SeguimientoPago;
use App\Models\Boleta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InteresesService
{
    /**
     * Obtiene la configuración de morosidad desde la base de datos con valores por defecto.
     */
    public static function getConfiguracionPagos(): array
    {
        $config = [
            'tasa_interes_mora' => 2.0,
            'tipo_interes_mora' => 'diario_compuesto',
            'dias_gracia_mora' => 0,
        ];

        try {
            if (Schema::hasTable('configuracion_sistema_pagos')) {
                $rows = DB::table('configuracion_sistema_pagos')
                    ->whereIn('clave', ['tasa_interes_mora', 'tipo_interes_mora', 'dias_gracia_mora'])
                    ->pluck('valor', 'clave');

                if (isset($rows['tasa_interes_mora']) && is_numeric($rows['tasa_interes_mora'])) {
                    $config['tasa_interes_mora'] = floatval($rows['tasa_interes_mora']);
                }
                if (isset($rows['tipo_interes_mora']) && !empty($rows['tipo_interes_mora'])) {
                    $config['tipo_interes_mora'] = trim($rows['tipo_interes_mora']);
                }
                if (isset($rows['dias_gracia_mora']) && is_numeric($rows['dias_gracia_mora'])) {
                    $config['dias_gracia_mora'] = intval($rows['dias_gracia_mora']);
                }
            }
        } catch (\Throwable $e) {
            // Fallback en caso de error de conexión o tabla
        }

        return $config;
    }

    /**
     * Recalcula los intereses por mora de un estudiante específico.
     */
    public static function recalcularInteresesEstudiante($id_estudiante)
    {
        $config = self::getConfiguracionPagos();
        $tasa_num = floatval($config['tasa_interes_mora'] ?? 2.0);
        $rate_decimal = $tasa_num / 100.0;
        $tipo_calculo = $config['tipo_interes_mora'] ?? 'diario_compuesto';
        $dias_gracia = intval($config['dias_gracia_mora'] ?? 0);

        // 1. Obtener todas las cuotas/letras vencidas y pendientes del estudiante
        $cuotas = SeguimientoPago::where('id_estudiante', $id_estudiante)
                                 ->where('estado', 'pendiente')
                                 ->get();

        $boletas_a_actualizar = [];

        foreach ($cuotas as $cuota) {
            $id_boleta = $cuota->id_boleta;
            $boletas_a_actualizar[$id_boleta] = true;

            $hoy = Carbon::today();
            $vencimiento = Carbon::parse($cuota->fecha_vencimiento)->startOfDay();

            // Si la cuota ya está vencida
            if ($vencimiento->lessThan($hoy)) {
                $dias_atraso = $vencimiento->diffInDays($hoy);
                $dias_efectivos = max(0, $dias_atraso - $dias_gracia);

                if ($dias_efectivos > 0) {
                    $monto_original = floatval($cuota->monto_cuota);
                    $interes = 0.0;

                    if ($tipo_calculo === 'diario_compuesto') {
                        $interes = $monto_original * (pow(1 + $rate_decimal, $dias_efectivos) - 1);
                    } elseif ($tipo_calculo === 'diario_simple') {
                        $interes = $monto_original * $rate_decimal * $dias_efectivos;
                    } elseif ($tipo_calculo === 'mensual_simple') {
                        $meses = ceil($dias_efectivos / 30);
                        $interes = $monto_original * $rate_decimal * $meses;
                    } else {
                        $interes = $monto_original * (pow(1 + $rate_decimal, $dias_efectivos) - 1);
                    }

                    $cuota->update([
                        'interes_acumulado' => round($interes, 2),
                        'ultimo_recalculo_interes' => Carbon::now()
                    ]);
                } else {
                    self::resetearInteresCuota($cuota);
                }
            } else {
                self::resetearInteresCuota($cuota);
            }
        }

        // 2. Actualizar las boletas asociadas
        foreach (array_keys($boletas_a_actualizar) as $id_boleta) {
            self::actualizarTotalesBoleta($id_boleta);
        }
    }

    /**
     * Resetea el interés a 0 para una cuota que no está en mora.
     */
    private static function resetearInteresCuota($cuota)
    {
        $cuota->update([
            'interes_acumulado' => 0.00,
            'ultimo_recalculo_interes' => null
        ]);
    }

    /**
     * Actualiza el saldo pendiente, mora y estado de la boleta.
     */
    public static function actualizarTotalesBoleta($id_boleta)
    {
        $boleta = Boleta::find($id_boleta);
        if (!$boleta) return;

        // Sumar todos los intereses de las cuotas pendientes
        $nuevo_interes_acumulado = floatval(
            SeguimientoPago::where('id_boleta', $id_boleta)
                ->where('estado', 'pendiente')
                ->sum('interes_acumulado')
        );

        // Sumar el capital pendiente de las cuotas
        $nuevo_capital_pendiente = floatval(
            SeguimientoPago::where('id_boleta', $id_boleta)
                ->where('estado', 'pendiente')
                ->sum('monto_cuota')
        );

        // Saldo pendiente de la boleta = capital pendiente de cuotas + interés acumulado de cuotas
        $nuevo_saldo_pendiente = $nuevo_capital_pendiente + $nuevo_interes_acumulado;
        $monto_pagado = floatval($boleta->monto_pagado ?? 0);
        $estado_actual = $boleta->estado;

        // Si ya no queda saldo pendiente => pagada
        // Si hay saldo pero ya se hizo un abono parcial previo => pago_parcial
        // Si aún no se ha pagado nada => conservar el estado original (pendiente, oficializada, etc.)
        if ($nuevo_saldo_pendiente <= 0 && $boleta->total > 0) {
            $nuevo_estado = 'pagada';
        } elseif ($monto_pagado > 0) {
            $nuevo_estado = 'pago_parcial';
        } else {
            $nuevo_estado = ($estado_actual === 'pagada') ? 'pendiente' : $estado_actual;
        }

        $boleta->update([
            'saldo_pendiente' => round($nuevo_saldo_pendiente, 2),
            'interes_acumulado' => round($nuevo_interes_acumulado, 2),
            'estado' => $nuevo_estado
        ]);
    }

    /**
     * Recalcula los intereses de todos los estudiantes con cuotas pendientes.
     */
    public static function recalcularTodosLosIntereses()
    {
        $estudiantesIds = SeguimientoPago::where('estado', 'pendiente')
            ->distinct()
            ->pluck('id_estudiante');

        foreach ($estudiantesIds as $id_estudiante) {
            self::recalcularInteresesEstudiante($id_estudiante);
        }
    }
}
