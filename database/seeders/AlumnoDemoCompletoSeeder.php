<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Usuario;
use App\Models\Programa;
use App\Models\PlanEstudio;
use App\Models\CursoActivo;
use App\Models\Matricula;
use App\Models\ExpedienteDigital;
use App\Models\Boleta;
use App\Models\Convalidacion;
use App\Models\TcuBitacora;
use App\Models\TcuActividad;

class AlumnoDemoCompletoSeeder extends Seeder
{
    /**
     * Siembra un alumno completo con todo el ciclo institucional (Fases 1, 2, 4 y 6).
     */
    public function run(): array
    {
        // 1. Crear o recuperar el Alumno Demo
        $email = 'carlos.alvarado@ceficr.com';
        $alumno = Usuario::where('email', $email)->first();

        if (!$alumno) {
            $alumno = Usuario::create([
                'nombre'    => 'Carlos Eduardo',
                'apellidos' => 'Alvarado Mora',
                'cedula'    => '1-1850-0492',
                'email'     => $email,
                'telefono'  => '50688991122',
                'password'  => Hash::make('cefi2026'),
                'id_rol'    => 3, // Rol Estudiante / Alumno
                'origen'    => 'inbox',
            ]);
        } else {
            $alumno->update([
                'nombre'    => 'Carlos Eduardo',
                'apellidos' => 'Alvarado Mora',
                'cedula'    => '1-1850-0492',
                'telefono'  => '50688991122',
                'password'  => Hash::make('cefi2026'),
                'id_rol'    => 3,
            ]);
        }

        // 2. Crear o recuperar Docente para los cursos
        $profesor = Usuario::where('id_rol', 2)->orWhere('id_rol', 4)->first();
        if (!$profesor) {
            $profesor = Usuario::where('id_rol', 1)->first();
        }
        if (!$profesor) {
            $profesor = Usuario::create([
                'nombre'    => 'Lic. Fernando',
                'apellidos' => 'Solano Valverde',
                'cedula'    => '1-0987-0654',
                'email'     => 'profesor.solano@ceficr.com',
                'telefono'  => '50683334455',
                'password'  => Hash::make('cefi2026'),
                'id_rol'    => 2,
                'origen'    => 'inbox',
            ]);
        }

        // 3. Crear o recuperar Programa Académico
        $programa = null;
        if (Schema::hasTable('programas')) {
            $programa = Programa::first();
            if (!$programa) {
                $programa = Programa::create([
                    'nombre_programa'         => 'Técnico Superior en Gestión Financiera y Bancaria',
                    'categoria'               => 'Finanzas y Banca',
                    'informacion'             => 'Formación integral en análisis contable, tributario y gestión bancaria.',
                    'costo_materia'           => 60000.00,
                    'costo_matricula'         => 25000.00,
                    'costo_biblioteca'        => 10000.00,
                    'costo_inscripcion_unica' => 15000.00,
                ]);
            }
        }

        $idPrograma = $programa ? $programa->id_programa : 1;

        // 4. Crear o recuperar Planes de Estudio (Materias)
        $materia1 = null;
        $materia2 = null;
        $materia3 = null;
        $materiaConv = null;

        if (Schema::hasTable('plan_estudios')) {
            $materia1 = PlanEstudio::where('codigo', 'CON-101')->first() ?: PlanEstudio::create([
                'id_programa' => $idPrograma,
                'codigo'      => 'CON-101',
                'materia'     => 'Contabilidad Financiera I',
                'creditos'    => 3,
                'cuatrimestre'=> 'I Cuatrimestre',
                'precio'      => 60000.00,
                'horas_totales' => 45
            ]);

            $materia2 = PlanEstudio::where('codigo', 'LEG-102')->first() ?: PlanEstudio::create([
                'id_programa' => $idPrograma,
                'codigo'      => 'LEG-102',
                'materia'     => 'Legislación Tributaria y Mercantil',
                'creditos'    => 3,
                'cuatrimestre'=> 'I Cuatrimestre',
                'precio'      => 60000.00,
                'horas_totales' => 45
            ]);

            $materia3 = PlanEstudio::where('codigo', 'FIN-201')->first() ?: PlanEstudio::create([
                'id_programa' => $idPrograma,
                'codigo'      => 'FIN-201',
                'materia'     => 'Análisis Financiero y Presupuestos',
                'creditos'    => 4,
                'cuatrimestre'=> 'II Cuatrimestre',
                'precio'      => 60000.00,
                'horas_totales' => 60
            ]);

            $materiaConv = PlanEstudio::where('codigo', 'MAT-100')->first() ?: PlanEstudio::create([
                'id_programa' => $idPrograma,
                'codigo'      => 'MAT-100',
                'materia'     => 'Matemática Financiera Aplicada',
                'creditos'    => 3,
                'cuatrimestre'=> 'I Cuatrimestre',
                'precio'      => 60000.00,
                'horas_totales' => 45
            ]);
        }

        // 5. Crear o recuperar Cursos Activos (Aulas Activas)
        $cursoActivo1 = null;
        $cursoActivo2 = null;
        $cursoActivo3 = null;
        $periodoActual = 'I Cuatrimestre ' . date('Y');

        if (Schema::hasTable('cursos_activos') && $materia1 && $materia2 && $materia3) {
            $cursoActivo1 = CursoActivo::where('id_plan', $materia1->id_plan)->where('periodo', $periodoActual)->first() ?: CursoActivo::create([
                'id_plan'        => $materia1->id_plan,
                'id_profesor'    => $profesor->id,
                'periodo'        => $periodoActual,
                'horario'        => 'Lunes y Miércoles 6:00 PM - 8:30 PM',
                'enlace_zoom'    => 'https://zoom.us/j/cefi-contabilidad1',
                'modalidad'      => 'Virtual Sincrónico',
                'check_terminado'=> false,
            ]);

            $cursoActivo2 = CursoActivo::where('id_plan', $materia2->id_plan)->where('periodo', $periodoActual)->first() ?: CursoActivo::create([
                'id_plan'        => $materia2->id_plan,
                'id_profesor'    => $profesor->id,
                'periodo'        => $periodoActual,
                'horario'        => 'Martes y Jueves 6:00 PM - 8:30 PM',
                'enlace_zoom'    => 'https://zoom.us/j/cefi-legislacion',
                'modalidad'      => 'Virtual Sincrónico',
                'check_terminado'=> false,
            ]);

            $cursoActivo3 = CursoActivo::where('id_plan', $materia3->id_plan)->where('periodo', $periodoActual)->first() ?: CursoActivo::create([
                'id_plan'        => $materia3->id_plan,
                'id_profesor'    => $profesor->id,
                'periodo'        => $periodoActual,
                'horario'        => 'Sábados 8:00 AM - 1:00 PM',
                'enlace_zoom'    => 'https://zoom.us/j/cefi-finanzas',
                'modalidad'      => 'Virtual Sincrónico',
                'check_terminado'=> false,
            ]);
        }

        // 6. Matricular al Estudiante en los 3 Cursos con Calificaciones
        if (Schema::hasTable('matriculas')) {
            // Materia 1: Aprobado con 92.5
            if ($cursoActivo1) {
                Matricula::updateOrCreate(
                    ['id_estudiante' => $alumno->id, 'id_curso_activo' => $cursoActivo1->id_curso_activo],
                    [
                        'calificacion' => 92.5,
                        'comentarios'  => 'Excelente dominio contable y participación destacada.',
                        'estado'       => 'Activo'
                    ]
                );
            }

            // Materia 2: Aprobado con 87.0
            if ($cursoActivo2) {
                Matricula::updateOrCreate(
                    ['id_estudiante' => $alumno->id, 'id_curso_activo' => $cursoActivo2->id_curso_activo],
                    [
                        'calificacion' => 87.0,
                        'comentarios'  => 'Aprobado satisfactoriamente en evaluaciones tributarias.',
                        'estado'       => 'Activo'
                    ]
                );
            }

            // Materia 3: En curso actual (sin nota final registrada aún)
            if ($cursoActivo3) {
                Matricula::updateOrCreate(
                    ['id_estudiante' => $alumno->id, 'id_curso_activo' => $cursoActivo3->id_curso_activo],
                    [
                        'calificacion' => null,
                        'comentarios'  => 'Cursando actualmente el ciclo lectivo.',
                        'estado'       => 'Activo'
                    ]
                );
            }
        }

        // 7. Crear Expediente Digital 360° Completo y Aprobado
        $expediente = null;
        if (Schema::hasTable('expedientes_digitales')) {
            $expediente = ExpedienteDigital::updateOrCreate(
                ['id_usuario' => $alumno->id],
                [
                    'grado_a_matricular'                    => 'Técnico Superior',
                    'especialidad_deseada'                  => 'Gestión Financiera y Bancaria',
                    'genero'                                => 'Masculino',
                    'fecha_nacimiento'                      => '1998-05-14',
                    'lugar_nacimiento'                      => 'San José, Costa Rica',
                    'nacionalidad'                          => 'Costarricense',
                    'cedula_residencia'                     => '1-1850-0492',
                    'estado_civil'                          => 'Soltero',
                    'domicilio_direccion'                   => 'Barrio Escalante, 100m Este del Parque Francia',
                    'domicilio_provincia'                   => 'San José',
                    'domicilio_canton'                      => 'Central',
                    'domicilio_distrito'                    => 'Carmen',
                    'contacto_tel_celular'                  => '50688991122',
                    'contacto_tel_habitacion'               => '50622554433',
                    'contacto_otro_emergencias'             => 'María Mora (Madre) - 50688112233',
                    'procedencia_secundaria_institucion'    => 'Liceo de Costa Rica',
                    'procedencia_secundaria_ano_graduacion' => 2016,
                    'procedencia_secundaria_grado_obtenido' => 'Bachiller en Educación Media',
                    'laboral_institucion'                   => 'BAC Credomatic Costa Rica',
                    'laboral_fecha_ingreso'                 => '2021-03-01',
                    'laboral_puesto'                        => 'Asistente Contable',
                    'laboral_telefono'                      => '50622959888',
                    'registro_doc_titulo_sec'               => true,
                    'registro_doc_cedula'                   => true,
                    'registro_doc_fotografia'               => true,
                    'registro_doc_certificaciones'          => true,
                    'registro_doc_titulo_univ'              => false,
                    'fecha_registro'                        => date('Y-m-d', strtotime('-3 months')),
                    'registro_fecha_matricula'              => date('Y-m-d', strtotime('-3 months')),
                    'estado'                                => 'Aprobado',
                    'registro_observaciones'                => 'Expediente digital verificado y aprobado 100% conforme a los requisitos institucionales de CEFI.',
                ]
            );
        }

        // 8. Crear Convalidación Aprobada (Fase 4: Control Académico)
        $convalidacion = null;
        if (Schema::hasTable('convalidaciones') && $materiaConv) {
            $convalidacion = Convalidacion::updateOrCreate(
                ['id_estudiante' => $alumno->id, 'materia_origen' => 'Cálculo y Matemática Financiera I'],
                [
                    'id_expediente'          => $expediente ? $expediente->id_expediente : null,
                    'id_programa'            => $idPrograma,
                    'id_plan'                => $materiaConv->id_plan,
                    'institucion_origen'     => 'Universidad de Costa Rica (UCR)',
                    'carrera_origen'         => 'Administración de Empresas',
                    'codigo_origen'          => 'MAT-200',
                    'creditos_origen'        => 3.0,
                    'calificacion_origen'    => 90.0,
                    'creditos_convalidados'  => 3.0,
                    'calificacion_homologada'=> 90.0,
                    'tipo_convalidacion'     => 'Convalidación Externa',
                    'numero_resolucion'      => 'RES-CEFI-2026-042',
                    'fecha_resolucion'       => date('Y-m-d', strtotime('-2 months')),
                    'estado'                 => 'Aprobada',
                    'observaciones'          => 'Homologación de créditos universitarios avalada por Dirección Académica.',
                    'registrado_por'         => $profesor ? $profesor->id : 1,
                    'aprobado_por'           => $profesor ? $profesor->id : 1,
                ]
            );
        }

        // 9. Crear Boleta de Matrícula y Finanzas
        $boleta = null;
        if (Schema::hasTable('boletas')) {
            $boleta = Boleta::updateOrCreate(
                ['id_estudiante' => $alumno->id, 'periodo' => $periodoActual],
                [
                    'numero_boleta'           => 'BOL-2026-0088',
                    'id_creador'              => $profesor ? $profesor->id : 1,
                    'total'                   => 180000.00,
                    'descuento'               => 0.00,
                    'interes_acumulado'       => 0.00,
                    'monto_pagado'            => 120000.00,
                    'saldo_pendiente'         => 60000.00,
                    'pago_inicial'            => 60000.00,
                    'pago_inicial_metodo'     => 'SINPE Móvil',
                    'pago_inicial_referencia' => 'TR-88491024',
                    'cuotas'                  => 3,
                    'estado'                  => 'pago_parcial',
                    'token_firma'             => 'TOKEN-FIRMA-DEMO-2026',
                    'fecha_firma'             => date('Y-m-d H:i:s', strtotime('-1 month')),
                    'fecha_creacion'          => date('Y-m-d H:i:s', strtotime('-1 month')),
                    'enviado_email'           => true
                ]
            );
        }

        // 10. Crear Bitácora de TCU con Actividades Registradas
        $tcu = null;
        if (Schema::hasTable('tcu_bitacoras')) {
            $tcu = TcuBitacora::updateOrCreate(
                ['usuario_id' => $alumno->id],
                [
                    'nombre_proyecto'        => 'Digitalización y Gestión Financiera Comunitaria para Pymes',
                    'lugar_realizacion'      => 'Comunidades de San José y Heredia',
                    'institucion_beneficiada'=> 'Cámara de Pequeños Productores y Emprendedores',
                    'nombre_supervisor'      => 'Licda. Tatiana Castro Vargas',
                    'cedula_supervisor'      => '1-1234-0567',
                    'email_institucion'      => 'contacto@productoreslocales.cr',
                    'telefono_institucion'   => '50622223344',
                    'carrera'                => 'Gestión Financiera y Bancaria',
                    'estado'                 => 'borrador',
                    'fecha_creacion'         => date('Y-m-d H:i:s', strtotime('-40 days')),
                ]
            );

            if (Schema::hasTable('tcu_actividades') && $tcu) {
                TcuActividad::where('bitacora_id', $tcu->id)->delete();

                TcuActividad::create([
                    'bitacora_id'    => $tcu->id,
                    'fecha'          => date('Y-m-d', strtotime('-25 days')),
                    'hora_entrada'   => '08:00:00',
                    'hora_salida'    => '13:00:00',
                    'cantidad_horas' => 25.0,
                    'actividades'    => 'Taller de presupuestos básicos y flujo de caja para 6 emprendimientos.',
                    'fecha_registro' => date('Y-m-d H:i:s', strtotime('-25 days')),
                ]);

                TcuActividad::create([
                    'bitacora_id'    => $tcu->id,
                    'fecha'          => date('Y-m-d', strtotime('-10 days')),
                    'hora_entrada'   => '09:00:00',
                    'hora_salida'    => '13:00:00',
                    'cantidad_horas' => 20.0,
                    'actividades'    => 'Implementación de facturación electrónica y conciliación de cuentas bancarias.',
                    'fecha_registro' => date('Y-m-d H:i:s', strtotime('-10 days')),
                ]);
            }
        }

        return [
            'success'       => true,
            'alumno'        => $alumno,
            'expediente'    => $expediente,
            'cursos'        => [$materia1 ?? null, $materia2 ?? null, $materia3 ?? null],
            'convalidacion' => $convalidacion,
            'boleta'        => $boleta,
            'tcu'           => $tcu,
            'id_estudiante' => $alumno->id,
            'email'         => $alumno->email,
            'password'      => 'cefi2026',
            'nombre'        => $alumno->nombre . ' ' . $alumno->apellidos,
            'cedula'        => $alumno->cedula,
            'matriculas'    => 3,
            'convalidado'   => 1,
            'tcu_horas'     => 45,
            'saldo_deuda'   => '₡60,000.00',
        ];
    }
}
