<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\BoletaController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\SolicitudCursoLibreController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UtilitarioController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\BovedaClavesController;
use App\Http\Controllers\CentroSoporteController;
use App\Http\Controllers\PlantillaDocumentoController;
use App\Http\Controllers\TcuController;
use App\Http\Controllers\SupervisionCursosController;
use App\Http\Controllers\MisCursosController;
use App\Http\Controllers\RecursoProfesorController;
use App\Http\Controllers\VideotutorialController;
use App\Http\Controllers\BibliotecaMediosController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\InboxAiController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\CampanaWhatsAppController;
use App\Http\Controllers\MarketingProspectoController;
use App\Http\Controllers\ConvalidacionController;
use App\Http\Controllers\RetiroAsignaturaController;
use App\Http\Controllers\GraduacionController;
use App\Http\Controllers\PortalEstudianteController;

Route::post('/inbox-ai/chat', [InboxAiController::class, 'chat'])->name('inbox_ai.chat');

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas Públicas (Formulario de Inscripción Libre)
Route::get('/solicitud-curso-libre/publica', [SolicitudCursoLibreController::class, 'showPublicForm'])->name('solicitudes.public');
Route::post('/solicitud-curso-libre/publica', [SolicitudCursoLibreController::class, 'storePublic'])->name('solicitudes.public.store');
Route::get('/solicitud-curso-libre/exito', function () {
    return view('solicitudes.success');
})->name('solicitudes.public.success');

// Rutas Públicas de Encuestas
Route::get('/encuesta/{id_curso}', [EncuestaController::class, 'responder'])->name('encuesta.responder');
Route::post('/encuesta/procesar', [EncuestaController::class, 'procesarRespuesta'])->name('encuesta.procesar');

// API Externa Finanzas (n8n, Green-API, Cron WhatsApp Morosidad)
Route::match(['get', 'post'], '/api/finanzas/cuotas-vencimiento', [BoletaController::class, 'apiCuotasVencimiento'])->name('finanzas.api.cuotas_vencimiento');

// Ruta utilitaria para ejecutar migraciones en el servidor web (Hostinger / Producción)
Route::get('/sistema/migrar', function () {
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return "<pre style='background:#0f172a;color:#38bdf8;padding:25px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;'><h3>✔️ Migración de Base de Datos CEFI</h3>Exit Code: {$exitCode}\n\n{$output}\n<a href='" . url('/convalidaciones') . "' style='color:#4ade80;'>Ir a Convalidaciones &rarr;</a></pre>";
    } catch (\Throwable $e) {
        return "<pre style='background:#0f172a;color:#f87171;padding:25px;border-radius:12px;font-family:monospace;font-size:14px;line-height:1.6;'><h3>❌ Error al ejecutar migraciones</h3>" . $e->getMessage() . "</pre>";
    }
})->name('sistema.migrar');

// Ruta utilitaria para sembrar la Base de Conocimiento de 500 preguntas del Tutor AI en Producción
Route::get('/sistema/sembrar-conocimiento', function () {
    try {
        $seeder = new \Database\Seeders\BaseConocimientoInboxAiSeeder();
        $res = $seeder->run();

        $cargados = $res['total_cargados'] ?? 500;
        $insertados = $res['insertados'] ?? 0;
        $actualizados = $res['actualizados'] ?? 0;
        $totalBd = $res['total_en_bd'] ?? 0;

        return response("
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Base de Conocimiento 360° Sembrada - CEFI Tutor AI</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                body { background: #0f172a; color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; padding: 40px 15px; }
                .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
                .accent-pill { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 4px 14px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
                .stat-box { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 20px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container' style='max-width: 800px;'>
                <div class='glass-card p-4 p-md-5'>
                    <div class='d-flex align-items-center justify-content-between border-bottom pb-4 mb-4 border-secondary border-opacity-25'>
                        <div>
                            <span class='accent-pill mb-2'><i class='fa-solid fa-robot me-1'></i> Tutor AI 2.0 CEFI</span>
                            <h2 class='fw-bold mb-0 text-white'>Base de Conocimiento Sembrada</h2>
                            <p class='text-secondary mb-0'>El cerebro RAG ha sido enriquecido con las 500 preguntas y respuestas institucionales.</p>
                        </div>
                        <div class='text-end'>
                            <span class='badge bg-success p-2 px-3 fs-6'><i class='fa-solid fa-check-circle me-1'></i> 100% Operativo</span>
                        </div>
                    </div>

                    <div class='row g-3 mb-4'>
                        <div class='col-sm-3'>
                            <div class='stat-box'>
                                <h3 class='text-info fw-bold mb-0'>{$cargados}</h3>
                                <small class='text-secondary'>Preguntas Dataset</small>
                            </div>
                        </div>
                        <div class='col-sm-3'>
                            <div class='stat-box'>
                                <h3 class='text-success fw-bold mb-0'>{$insertados}</h3>
                                <small class='text-secondary'>Nuevas Insertadas</small>
                            </div>
                        </div>
                        <div class='col-sm-3'>
                            <div class='stat-box'>
                                <h3 class='text-warning fw-bold mb-0'>{$actualizados}</h3>
                                <small class='text-secondary'>Actualizadas</small>
                            </div>
                        </div>
                        <div class='col-sm-3'>
                            <div class='stat-box'>
                                <h3 class='text-white fw-bold mb-0'>{$totalBd}</h3>
                                <small class='text-secondary'>Total en BD</small>
                            </div>
                        </div>
                    </div>

                    <div class='alert alert-dark bg-dark bg-opacity-50 border-secondary border-opacity-25 text-light p-3 rounded-3 mb-4'>
                        <h6 class='fw-bold text-info mb-2'><i class='fa-solid fa-layer-group me-2'></i>20 Módulos Cubiertos:</h6>
                        <p class='small mb-0 text-secondary'>Acceso y Seguridad, Dashboard, Tareas BPM, Usuarios, Programas, Cursos y Sílabos, Grupos y Aulas, Cursos Libres, Expedientes 360°, Facturación, Firmas Digitales, Finanzas y Mora, Gestión Docente, Asistencia y Planilla, Portal Estudiante, TCU (150h), Convalidaciones, Retiros, Graduaciones, Marketing y Tutor AI.</p>
                    </div>

                    <div class='d-flex flex-wrap gap-2 pt-3 border-top border-secondary border-opacity-25'>
                        <a href='/configuracion/conocimiento-ai' class='btn btn-success px-4 fw-bold shadow-sm'><i class='fa-solid fa-brain me-2'></i>Ver Base de Conocimiento (Admin)</a>
                        <a href='/dashboard' class='btn btn-outline-light px-3'><i class='fa-solid fa-house me-2'></i>Ir al Dashboard</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ", 200, ['Content-Type' => 'text/html']);
    } catch (\Throwable $e) {
        return response("
            <div style='background: #1e1e2d; color: #ff6b6b; padding: 30px; font-family: monospace; border-radius: 8px; margin: 30px auto; max-width: 800px; border: 1px solid #ff4d4f;'>
                <h2><i class='fa fa-exclamation-triangle'></i> Error al Sembrar Base de Conocimiento</h2>
                <p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <pre style='background: #151521; color: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
            </div>
        ", 500, ['Content-Type' => 'text/html']);
    }
})->name('sistema.sembrar_conocimiento');



Route::middleware('auth')->group(function () {
    // =========================================================================
    // --- CONTROL DE SEGURIDAD & ACCESO SUPERIOR A CONFIGURACIÓN ---
    // =========================================================================
    Route::get('/configuracion/seguridad', [ConfiguracionController::class, 'login'])->name('configuracion.login');
    Route::post('/configuracion/seguridad/acceder', [ConfiguracionController::class, 'acceder'])->name('configuracion.acceder');
    Route::post('/configuracion/seguridad/salir', [ConfiguracionController::class, 'salir'])->name('configuracion.salir');

    // Rutas protegidas exclusivamente para Administrador General con Clave Superior
    Route::middleware('admin.config')->prefix('configuracion')->name('configuracion.')->group(function () {
        // Hub Central de Configuración
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
        Route::post('/guardar', [ConfiguracionController::class, 'guardar'])->name('guardar');

        // Parámetros de Pagos, Morosidad y Firmas Oficiales (Estilo CEFI)
        Route::get('/pagos', [ConfiguracionController::class, 'parametrosPagos'])->name('pagos');
        Route::post('/pagos', [ConfiguracionController::class, 'guardarParametrosPagos'])->name('pagos.guardar');
        Route::post('/pagos/reset-pruebas', [ConfiguracionController::class, 'resetearPruebasBoletas'])->name('pagos.reset_pruebas');

        // Test de conectividad
        Route::post('/test-n8n', [ConfiguracionController::class, 'testN8n'])->name('test_n8n');
        Route::post('/test-whatsapp', [ConfiguracionController::class, 'testWhatsApp'])->name('test_whatsapp');
        Route::post('/test-smtp', [ConfiguracionController::class, 'testSmtp'])->name('test_smtp');

        // Licenciamiento de módulos (Fabricante / Desarrollador)
        Route::post('/dev-desbloquear', [ConfiguracionController::class, 'desbloquearModulos'])->name('dev_desbloquear');
        Route::post('/dev-bloquear', [ConfiguracionController::class, 'bloquearModulos'])->name('dev_bloquear');

        // Compatibilidad con rutas anteriores de parámetros
        Route::get('/parametros', [ConfiguracionController::class, 'parametros'])->name('parametros');
        Route::post('/parametros', [ConfiguracionController::class, 'guardarParametros'])->name('parametros.store');
        Route::post('/parametros/acceder', [ConfiguracionController::class, 'acceder'])->name('parametros.acceder');
        Route::post('/parametros/salir', [ConfiguracionController::class, 'salir'])->name('parametros.salir');

        // Configuración de WhatsApp y Alertas dentro del módulo seguro
        Route::get('/whatsapp', [WhatsAppController::class, 'configuracion'])->name('whatsapp');
        Route::post('/whatsapp', [WhatsAppController::class, 'guardarConfiguracion'])->name('whatsapp.store');
        Route::post('/whatsapp/test', [WhatsAppController::class, 'testEnvio'])->name('whatsapp.test');
        Route::get('/alertas', [ConfiguracionController::class, 'alertas'])->name('alertas');
        Route::post('/alertas', [ConfiguracionController::class, 'guardarAlertas'])->name('alertas.store');
    });

    // --- DASHBOARD & TAREAS ---
    Route::middleware('module:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/tareas/cambiar-estado', [DashboardController::class, 'cambiarEstado'])->name('tareas.cambiar_estado');
        Route::post('/tareas/cambiar-prioridad', [DashboardController::class, 'cambiarPrioridad'])->name('tareas.cambiar_prioridad');
        Route::post('/tareas/crear-rapida', [DashboardController::class, 'crearTareaRapida'])->name('tareas.crear_rapida');
        Route::get('/tareas/{id}/detalle', [DashboardController::class, 'obtenerTarea'])->name('tareas.obtener');
        Route::post('/tareas/{id}/actualizar', [DashboardController::class, 'actualizarTarea'])->name('tareas.actualizar');
        Route::delete('/tareas/{id}/eliminar', [DashboardController::class, 'eliminarTarea'])->name('tareas.eliminar');
    });

    Route::middleware('module:tareas_gantt')->group(function () {
        Route::get('/tareas/crear', [TareaController::class, 'create'])->name('tareas.create');
        Route::post('/tareas/crear', [TareaController::class, 'store'])->name('tareas.store');
        Route::get('/tareas/seleccionar-plantilla', [TareaController::class, 'seleccionarPlantilla'])->name('tareas.seleccionar_plantilla');
        Route::get('/tareas/plantillas/gestionar', [TareaController::class, 'gestionarPlantillas'])->name('tareas.plantillas.gestionar');
        Route::post('/tareas/plantillas/crear', [TareaController::class, 'storePlantilla'])->name('tareas.plantillas.store');
        Route::get('/tareas/plantillas/{id}/editar', [TareaController::class, 'editarPlantilla'])->name('tareas.plantillas.edit');
        Route::post('/tareas/plantillas/{id}/editar', [TareaController::class, 'updatePlantilla'])->name('tareas.plantillas.update');
        Route::post('/tareas/plantillas/{id}/eliminar', [TareaController::class, 'destroyPlantilla'])->name('tareas.plantillas.destroy');
    });

    // --- REGISTRO ACADÉMICO ---
    Route::middleware(['module:registro_academico', 'no.alumno'])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios/crear', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::post('/usuarios/{id}/actualizar', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{id}/eliminar', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        
        // Cursos y Programas
        Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::get('/cursos/crear', [CursoController::class, 'create'])->name('cursos.create');
        Route::post('/cursos/crear', [CursoController::class, 'store'])->name('cursos.store');
        Route::get('/cursos/{id}/editar', [CursoController::class, 'edit'])->name('cursos.edit');
        Route::post('/cursos/{id}/actualizar', [CursoController::class, 'update'])->name('cursos.update');
        Route::post('/cursos/{id}/eliminar', [CursoController::class, 'destroy'])->name('cursos.destroy');
        Route::get('/cursos/plan-estudio/{id}/descriptor-doc', [CursoController::class, 'generarDescriptorDoc'])->name('cursos.descriptor_doc');
        Route::get('/cursos/plan-estudio/{id}/descriptor-pdf', [CursoController::class, 'generarDescriptorPdf'])->name('cursos.descriptor_pdf');
        Route::get('/programas', [ProgramaController::class, 'index'])->name('programas.index');
        Route::get('/programas/crear', [ProgramaController::class, 'create'])->name('programas.create');
        Route::post('/programas/crear', [ProgramaController::class, 'store'])->name('programas.store');
        Route::get('/programas/{id}/editar', [ProgramaController::class, 'edit'])->name('programas.edit');
        Route::post('/programas/{id}/actualizar', [ProgramaController::class, 'update'])->name('programas.update');
        Route::post('/programas/{id}/eliminar', [ProgramaController::class, 'destroy'])->name('programas.destroy');
        Route::get('/programas-completos', [ProgramaController::class, 'programasCompletos'])->name('programas.completos');

        // Grupos
        Route::get('/grupos', [GrupoController::class, 'index'])->name('grupos.index');
        Route::post('/grupos/crear', [GrupoController::class, 'store'])->name('grupos.store');
        Route::post('/grupos/eliminar', [GrupoController::class, 'destroy'])->name('grupos.destroy');

        // Solicitudes Cursos Libres
        Route::get('/solicitudes-cursos-libres', [SolicitudCursoLibreController::class, 'index'])->name('solicitudes.index');
        Route::get('/solicitudes-cursos-libres/crear', [SolicitudCursoLibreController::class, 'create'])->name('solicitudes.create');
        Route::post('/solicitudes-cursos-libres/crear', [SolicitudCursoLibreController::class, 'store'])->name('solicitudes.store');
        Route::get('/solicitudes-cursos-libres/{id}/editar', [SolicitudCursoLibreController::class, 'edit'])->name('solicitudes.edit');
        Route::post('/solicitudes-cursos-libres/{id}/editar', [SolicitudCursoLibreController::class, 'update'])->name('solicitudes.update');
        Route::post('/solicitudes-cursos-libres/{id}/eliminar', [SolicitudCursoLibreController::class, 'destroy'])->name('solicitudes.destroy');

        // Reportes
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/generar', [ReporteController::class, 'generar'])->name('reportes.generar');
        Route::get('/reportes/ingresos-drive', [ReporteController::class, 'ingresosDrive'])->name('reportes.ingresos_drive');

        // Utilitarios
        Route::get('/utilitarios', [UtilitarioController::class, 'index'])->name('utilitarios.index');
        Route::post('/utilitarios/acceder', [UtilitarioController::class, 'acceder'])->name('utilitarios.acceder');
        Route::post('/utilitarios/{id}/eliminar', [UtilitarioController::class, 'destroy'])->name('utilitarios.destroy');

        // Convalidaciones & Homologaciones (Fase 4 CEFI)
        Route::get('/convalidaciones', [ConvalidacionController::class, 'index'])->name('convalidaciones.index');
        Route::get('/convalidaciones/crear', [ConvalidacionController::class, 'create'])->name('convalidaciones.create');
        Route::post('/convalidaciones/crear', [ConvalidacionController::class, 'store'])->name('convalidaciones.store');
        Route::post('/convalidaciones/{id}/estado', [ConvalidacionController::class, 'cambiarEstado'])->name('convalidaciones.cambiar_estado');
        Route::post('/convalidaciones/{id}/eliminar', [ConvalidacionController::class, 'destroy'])->name('convalidaciones.destroy');
        Route::get('/convalidaciones/programa/{id}/cursos-ajax', [ConvalidacionController::class, 'ajaxCursosPrograma'])->name('convalidaciones.cursos_programa_ajax');

        // Retiros y Cambios de Asignaturas (Fase 4 CEFI)
        Route::get('/retiros-asignaturas', [RetiroAsignaturaController::class, 'index'])->name('retiros.index');
        Route::get('/retiros-asignaturas/crear', [RetiroAsignaturaController::class, 'create'])->name('retiros.create');
        Route::post('/retiros-asignaturas/crear', [RetiroAsignaturaController::class, 'store'])->name('retiros.store');
        Route::post('/retiros-asignaturas/{id}/resolver', [RetiroAsignaturaController::class, 'resolver'])->name('retiros.resolver');
        Route::post('/retiros-asignaturas/{id}/eliminar', [RetiroAsignaturaController::class, 'destroy'])->name('retiros.destroy');
        Route::get('/retiros-asignaturas/estudiante/{id}/matriculas-ajax', [RetiroAsignaturaController::class, 'ajaxMatriculasEstudiante'])->name('retiros.matriculas_estudiante_ajax');

        // Graduaciones & Auditoría de Requisitos (Fase 4 CEFI)
        Route::get('/graduaciones', [GraduacionController::class, 'index'])->name('graduaciones.index');
        Route::get('/graduaciones/crear', [GraduacionController::class, 'create'])->name('graduaciones.create');
        Route::post('/graduaciones/crear', [GraduacionController::class, 'store'])->name('graduaciones.store');
        Route::get('/graduaciones/{id}/auditar', [GraduacionController::class, 'auditar'])->name('graduaciones.auditar');
        Route::post('/graduaciones/{id}/aprobar', [GraduacionController::class, 'aprobar'])->name('graduaciones.aprobar');
        Route::get('/graduaciones/{id}/dictamen-pdf', [GraduacionController::class, 'generarDictamenPdf'])->name('graduaciones.dictamen_pdf');
        Route::post('/graduaciones/{id}/eliminar', [GraduacionController::class, 'destroy'])->name('graduaciones.destroy');
    });

    // --- FACTURACIÓN Y BOLETAS DE MATRÍCULA ---
    Route::middleware(['module:boletas_matricula', 'no.alumno'])->group(function () {
        Route::get('/boletas', [BoletaController::class, 'index'])->name('boletas.index');
        Route::get('/boletas/generar', [BoletaController::class, 'generar'])->name('boletas.generar');
        Route::post('/boletas/generar', [BoletaController::class, 'store'])->name('boletas.store');
        Route::get('/boletas/cursos-programa/{id}/ajax', [BoletaController::class, 'getCursosPorProgramaAjax'])->name('boletas.cursos_programa_ajax');
        Route::get('/boletas/matriculas-pendientes-ajax', [BoletaController::class, 'getMatriculasPendientesAjax'])->name('boletas.matriculas_pendientes_ajax');
        Route::post('/boletas/datos-ajax', [BoletaController::class, 'getBoletaDataAjax'])->name('boletas.datos_ajax');
        Route::get('/boletas/buscar-estudiante-ajax', [BoletaController::class, 'buscarEstudianteAjax'])->name('boletas.buscar_estudiante_ajax');
        Route::post('/boletas/enviar-enlace-firma', [BoletaController::class, 'enviarEnlaceFirma'])->name('boletas.enviar_enlace_firma');
        Route::post('/boletas/guardar-oficializar', [BoletaController::class, 'guardarOficializarBoleta'])->name('boletas.guardar_oficializar');
        Route::get('/boletas/{id}/procesar', [BoletaController::class, 'procesarBoleta'])->name('boletas.procesar');
        Route::post('/boletas/{id}/oficializar-firmada', [BoletaController::class, 'oficializarBoletaFirmada'])->name('boletas.oficializar_firmada');
        Route::get('/boletas/{id}/pdf', [BoletaController::class, 'verPdf'])->name('boletas.pdf');
        Route::post('/boletas/{id}/pago', [BoletaController::class, 'registrarPago'])->name('boletas.pago.store');
        Route::post('/boletas/vaciar-pruebas', [BoletaController::class, 'vaciarPruebas'])->name('boletas.vaciar_pruebas');
        Route::post('/boletas/{id}/anular', [BoletaController::class, 'anular'])->name('boletas.anular');
        Route::post('/boletas/{id}/eliminar', [BoletaController::class, 'eliminar'])->name('boletas.eliminar');
        Route::post('/boletas/{id}/enviar-whatsapp', [WhatsAppController::class, 'enviarBoleta'])->name('boletas.enviar_whatsapp');
        Route::post('/boletas/{id}/enviar-email', [BoletaController::class, 'enviarEmail'])->name('boletas.enviar_email');
    });

    // --- FINANZAS, MOROSIDAD Y ESTADO DE CUENTA ---
    Route::middleware(['module:finanzas', 'no.alumno'])->group(function () {
        Route::get('/boletas/estado-cuenta', [BoletaController::class, 'estadoCuenta'])->name('boletas.estado_cuenta');
        Route::get('/boletas/estado-cuenta/{id}/ajax', [BoletaController::class, 'getEstadoCuentaAjax'])->name('boletas.estado_cuenta_ajax');
        Route::get('/boletas/morosidad', [BoletaController::class, 'morosidad'])->name('boletas.morosidad');
        Route::post('/finanzas/estudiante/{id}/enviar-whatsapp-mora', [WhatsAppController::class, 'enviarMora'])->name('finanzas.enviar_whatsapp_mora');
    });

    // --- EXPEDIENTES DIGITALES 360° ---
    Route::middleware(['module:expedientes_360', 'no.alumno'])->group(function () {
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/expedientes/crear', [ExpedienteController::class, 'create'])->name('expedientes.create');
        Route::post('/expedientes/crear', [ExpedienteController::class, 'store'])->name('expedientes.store');
        Route::get('/expedientes/buscar-usuario-ajax', [ExpedienteController::class, 'buscarUsuarioAjax'])->name('expedientes.buscar_usuario_ajax');
        Route::get('/expedientes/{id}', [ExpedienteController::class, 'ver'])->name('expedientes.ver');
        Route::get('/expedientes/{id}/editar', [ExpedienteController::class, 'edit'])->name('expedientes.edit');
        Route::delete('/expedientes/{id}', [ExpedienteController::class, 'destroy'])->name('expedientes.destroy');
        Route::post('/expedientes/{id}/estado', [ExpedienteController::class, 'cambiarEstado'])->name('expedientes.cambiar_estado');
        Route::post('/expedientes/subir-documento', [ExpedienteController::class, 'subirDocumento'])->name('expedientes.subir_documento');
        Route::post('/expedientes/documentos/{id}/eliminar', [ExpedienteController::class, 'eliminarDocumento'])->name('expedientes.documentos.eliminar');
        Route::get('/expedientes/{id}/observaciones', [ExpedienteController::class, 'listarObservaciones'])->name('expedientes.observaciones.listar');
        Route::post('/expedientes/observaciones/guardar', [ExpedienteController::class, 'guardarObservacion'])->name('expedientes.observaciones.guardar');
        Route::post('/expedientes/observaciones/{id}/eliminar', [ExpedienteController::class, 'eliminarObservacion'])->name('expedientes.observaciones.eliminar');
        Route::get('/expedientes/{id}/descargar-zip', [ExpedienteController::class, 'descargarZip'])->name('expedientes.descargar_zip');
        Route::get('/expedientes/{id}/record-pdf', [ExpedienteController::class, 'generarRecordPdf'])->name('expedientes.record_pdf');
        Route::get('/expedientes/{id}/expediente-pdf', [ExpedienteController::class, 'generarExpedientePdf'])->name('expedientes.expediente_pdf');
    });

    // --- DIFUSIÓN WHATSAPP & MARKETING PROSPECTOS ---
    Route::middleware('module:whatsapp_n8n')->group(function () {
        // Campañas y Difusión Masiva (Oferta Académica)
        Route::get('/whatsapp/campanas', [CampanaWhatsAppController::class, 'index'])->name('whatsapp.campanas.index');
        Route::get('/whatsapp/campanas/destinatarios-ajax', [CampanaWhatsAppController::class, 'destinatariosAjax'])->name('whatsapp.campanas.destinatarios_ajax');
        Route::post('/whatsapp/campanas/test', [CampanaWhatsAppController::class, 'testEnvio'])->name('whatsapp.campanas.test');
        Route::post('/whatsapp/campanas/lanzar', [CampanaWhatsAppController::class, 'lanzar'])->name('whatsapp.campanas.lanzar');
        Route::post('/whatsapp/campanas/guardar-webhook', [CampanaWhatsAppController::class, 'guardarConfigWebhook'])->name('whatsapp.campanas.guardar_webhook');

        // Base de Datos de Prospectos y Leads Externos (Marketing)
        Route::get('/marketing/prospectos', [MarketingProspectoController::class, 'index'])->name('marketing.prospectos.index');
        Route::post('/marketing/prospectos', [MarketingProspectoController::class, 'store'])->name('marketing.prospectos.store');
        Route::post('/marketing/prospectos/importar-pegado', [MarketingProspectoController::class, 'importarPegado'])->name('marketing.prospectos.importar_pegado');
        Route::post('/marketing/prospectos/importar-csv', [MarketingProspectoController::class, 'importarCsv'])->name('marketing.prospectos.importar_csv');
        Route::delete('/marketing/prospectos/{id}', [MarketingProspectoController::class, 'destroy'])->name('marketing.prospectos.destroy');
        Route::post('/marketing/prospectos/eliminar-origen', [MarketingProspectoController::class, 'eliminarPorOrigen'])->name('marketing.prospectos.eliminar_origen');
        Route::get('/marketing/prospectos/ajax', [MarketingProspectoController::class, 'prospectosAjax'])->name('marketing.prospectos.ajax');
    });

    // --- ASISTENCIA Y PLANILLA ---
    Route::middleware('module:planilla')->group(function () {
        Route::get('/asistencia/marcar', [AsistenciaController::class, 'marcar'])->name('asistencia.marcar');
        Route::post('/asistencia/registrar', [AsistenciaController::class, 'registrarMarca'])->name('asistencia.registrar');
        Route::get('/asistencia/historial', [AsistenciaController::class, 'historial'])->name('asistencia.historial');
        Route::post('/asistencia/manual', [AsistenciaController::class, 'guardarMarcaManual'])->name('asistencia.manual.store');
        Route::post('/asistencia/manual/{id}/editar', [AsistenciaController::class, 'actualizarMarca'])->name('asistencia.manual.update');
        Route::post('/asistencia/manual/{id}/eliminar', [AsistenciaController::class, 'eliminarMarca'])->name('asistencia.manual.delete');

        Route::get('/planilla', [PlanillaController::class, 'index'])->name('planilla.index');
        Route::post('/planilla/tarifa/categoria', [PlanillaController::class, 'guardarTarifaCategoria'])->name('planilla.tarifa.categoria');
        Route::get('/planilla/empleados', [PlanillaController::class, 'empleados'])->name('planilla.empleados');
        Route::post('/planilla/tarifa/empleado', [PlanillaController::class, 'guardarTarifaEmpleado'])->name('planilla.tarifa.empleado');
        Route::post('/planilla/credencial/empleado', [PlanillaController::class, 'guardarCredencialEmpleado'])->name('planilla.credencial.empleado');
        Route::get('/planilla/credencial/{id?}', [PlanillaController::class, 'credencial'])->name('planilla.credencial');
        Route::get('/planilla/credencial/ver/{id}', [PlanillaController::class, 'credencial'])->name('planilla.credencial.ver');
        Route::get('/planilla/escanear', [PlanillaController::class, 'escanear'])->name('planilla.escanear');
        Route::post('/planilla/procesar-marca-qr', [PlanillaController::class, 'procesarMarcaQR'])->name('planilla.escanear.marca');
        Route::get('/planilla/calcular', [PlanillaController::class, 'calcular'])->name('planilla.calcular');
        Route::post('/planilla/calcular/pago', [PlanillaController::class, 'guardarPagoPlanilla'])->name('planilla.calcular.pago');
        Route::get('/planilla/historial-pagos', [PlanillaController::class, 'historialPagos'])->name('planilla.historial_pagos');
    });

    // --- SOPORTE, CLAVES Y AUTOMATIZACIONES ---
    Route::middleware('module:soporte')->group(function () {
        Route::get('/soporte', [SoporteController::class, 'index'])->name('soporte.gestionar');
        Route::post('/soporte/crear', [SoporteController::class, 'store'])->name('soporte.store');
        Route::post('/soporte/{id}/editar', [SoporteController::class, 'update'])->name('soporte.update');
        Route::post('/soporte/{id}/estado', [SoporteController::class, 'changeStatus'])->name('soporte.estado');
        Route::post('/soporte/{id}/eliminar', [SoporteController::class, 'destroy'])->name('soporte.delete');
        Route::get('/soporte/lista', [SoporteController::class, 'lista'])->name('soporte.lista');
        Route::get('/soporte/categorias', [SoporteController::class, 'categorias'])->name('soporte.categorias');
        Route::post('/soporte/categorias/crear', [SoporteController::class, 'storeCategoria'])->name('soporte.categorias.store');
        Route::post('/soporte/categorias/{id}/editar', [SoporteController::class, 'updateCategoria'])->name('soporte.categorias.update');
        Route::post('/soporte/categorias/{id}/eliminar', [SoporteController::class, 'destroyCategoria'])->name('soporte.categorias.delete');

        // Bóveda Claves
        Route::get('/claves', [BovedaClavesController::class, 'index'])->name('claves.index');
        Route::post('/claves/acceder', [BovedaClavesController::class, 'acceder'])->name('claves.acceder');
        Route::post('/claves/salir', [BovedaClavesController::class, 'salir'])->name('claves.salir');
        Route::post('/claves/crear', [BovedaClavesController::class, 'store'])->name('claves.store');
        Route::post('/claves/{id}/editar', [BovedaClavesController::class, 'update'])->name('claves.update');
        Route::post('/claves/{id}/eliminar', [BovedaClavesController::class, 'destroy'])->name('claves.delete');

        // Centro Soporte
        Route::get('/centro-soporte', [CentroSoporteController::class, 'index'])->name('centro_soporte.index');
        Route::post('/centro-soporte/plataforma', [CentroSoporteController::class, 'storePlataforma'])->name('centro_soporte.plataforma.store');
        Route::match(['put', 'post'], '/centro-soporte/plataforma/{id}/editar', [CentroSoporteController::class, 'updatePlataforma'])->name('centro_soporte.plataforma.update');
        Route::match(['delete', 'post'], '/centro-soporte/plataforma/{id}/eliminar', [CentroSoporteController::class, 'destroyPlataforma'])->name('centro_soporte.plataforma.delete');
        Route::post('/centro-soporte/credencial', [CentroSoporteController::class, 'storeCredencial'])->name('centro_soporte.credencial.store');
        Route::match(['put', 'post'], '/centro-soporte/credencial/{id}/editar', [CentroSoporteController::class, 'updateCredencial'])->name('centro_soporte.credencial.update');
        Route::post('/centro-soporte/credencial/{id}/eliminar', [CentroSoporteController::class, 'destroyCredencial'])->name('centro_soporte.credencial.delete');

        // Automatizaciones (Plantillas)
        Route::get('/automatizaciones', [PlantillaDocumentoController::class, 'index'])->name('plantillas.index');
        Route::get('/automatizaciones/crear', [PlantillaDocumentoController::class, 'create'])->name('plantillas.create');
        Route::post('/automatizaciones/crear', [PlantillaDocumentoController::class, 'store'])->name('plantillas.store');
        Route::get('/automatizaciones/{id}/editar', [PlantillaDocumentoController::class, 'edit'])->name('plantillas.edit');
        Route::post('/automatizaciones/{id}/editar', [PlantillaDocumentoController::class, 'update'])->name('plantillas.update');
        Route::post('/automatizaciones/{id}/eliminar', [PlantillaDocumentoController::class, 'destroy'])->name('plantillas.delete');
        Route::get('/automatizaciones/{id}/preparar', [PlantillaDocumentoController::class, 'preparar'])->name('plantillas.preparar');
        Route::post('/automatizaciones/generar', [PlantillaDocumentoController::class, 'generar'])->name('plantillas.generar');
        Route::post('/automatizaciones/generar-png', [PlantillaDocumentoController::class, 'generarPng'])->name('plantillas.generar_png');
        Route::post('/automatizaciones/generar-lote-zip', [PlantillaDocumentoController::class, 'generarLoteZip'])->name('plantillas.generar_lote_zip');
    });

    // --- CEREBRO & TUTOR AI 2.0 (ASISTENTE VIRTUAL RAG CEFI) ---
    Route::get('/configuracion/conocimiento-ai', [ConfiguracionController::class, 'conocimientoAi'])->name('configuracion.conocimiento_ai');
    Route::post('/configuracion/conocimiento-ai/guardar', [ConfiguracionController::class, 'guardarConocimientoAi'])->name('configuracion.conocimiento_ai.guardar');
    Route::post('/configuracion/conocimiento-ai/{id}/eliminar', [ConfiguracionController::class, 'eliminarConocimientoAi'])->name('configuracion.conocimiento_ai.eliminar');

    // --- PORTAL DE AUTOGESTIÓN ESTUDIANTIL (FASE 6 CEFI - SIEMPRE DISPONIBLE) ---
    Route::get('/estudiante/portal', [PortalEstudianteController::class, 'index'])->name('estudiante.portal');
    Route::get('/estudiante/mi-record-pdf', [PortalEstudianteController::class, 'descargarMiRecordPdf'])->name('estudiante.mi_record_pdf');
    Route::get('/estudiante/mi-expediente-pdf', [PortalEstudianteController::class, 'descargarMiExpedientePdf'])->name('estudiante.mi_expediente_pdf');

    // --- SEGUIMIENTO DE TCU & TRÁMITES ESTUDIANTILES ---
    Route::middleware('module:estudiante_tcu')->group(function () {
        Route::get('/estudiante/tcu', [TcuController::class, 'index'])->name('tcu.index');
        Route::get('/estudiante/tcu/crear', [TcuController::class, 'create'])->name('tcu.create');
        Route::post('/estudiante/tcu/crear', [TcuController::class, 'store'])->name('tcu.store');
        Route::get('/estudiante/tcu/{id}/editar', [TcuController::class, 'edit'])->name('tcu.edit');
        Route::post('/estudiante/tcu/{id}/editar', [TcuController::class, 'update'])->name('tcu.update');
        Route::post('/estudiante/tcu/{id}/eliminar', [TcuController::class, 'destroy'])->name('tcu.delete');
        Route::post('/estudiante/tcu/{id}/finalizar', [TcuController::class, 'finalizar'])->name('tcu.finalizar');
        Route::post('/estudiante/tcu/{id}/revisar', [TcuController::class, 'revisar'])->name('tcu.revisar');
        Route::post('/estudiante/tcu/{id}/reabrir', [TcuController::class, 'reabrir'])->name('tcu.reabrir');
        Route::post('/estudiante/tcu/{id}/actividad', [TcuController::class, 'storeActividad'])->name('tcu.actividad.store');
        Route::post('/estudiante/tcu/actividad/{id}/eliminar', [TcuController::class, 'destroyActividad'])->name('tcu.actividad.delete');
        Route::get('/estudiante/tcu/{id}/imprimir', [TcuController::class, 'imprimir'])->name('tcu.imprimir');
    });

    // --- GESTIÓN DE CURSOS, SÍLABOS Y RECURSOS DOCENTES ---
    Route::middleware(['module:gestion_cursos', 'no.alumno'])->group(function () {
        Route::get('/cursos/supervision', [SupervisionCursosController::class, 'index'])->name('supervision.index');
        Route::post('/cursos/supervision/toggle', [SupervisionCursosController::class, 'toggleSupervision'])->name('supervision.toggle');
        Route::post('/cursos/supervision/toggle-supervision', [SupervisionCursosController::class, 'toggleSupervision'])->name('supervision.toggle_supervision');
        Route::post('/cursos/supervision/toggle-check', [SupervisionCursosController::class, 'toggleCheck'])->name('supervision.toggle_check');
        Route::post('/cursos/supervision/notificar', [SupervisionCursosController::class, 'notificar'])->name('supervision.notificar');
        Route::get('/cursos/supervision/{id}/historial', [SupervisionCursosController::class, 'getHistorialNotificaciones'])->name('supervision.historial');

        Route::get('/docente/cursos', [MisCursosController::class, 'index'])->name('mis_cursos.index');
        Route::get('/docente/cursos/{id}', [MisCursosController::class, 'ver'])->name('mis_cursos.ver');
        Route::post('/docente/cursos/{id}/notas', [MisCursosController::class, 'guardarNotas'])->name('mis_cursos.guardar_notas');
        Route::post('/docente/cursos/{id}/factura', [MisCursosController::class, 'subirFactura'])->name('mis_cursos.subir_factura');
        Route::post('/docente/cursos/{id}/factura/eliminar', [MisCursosController::class, 'eliminarFactura'])->name('mis_cursos.eliminar_factura');
        Route::post('/docente/cursos/{id}/enviar-nota-individual', [MisCursosController::class, 'enviarNotaIndividual'])->name('mis_cursos.enviar_nota_individual');
        Route::get('/docente/cursos/{id}/asistencia', [MisCursosController::class, 'verAsistencia'])->name('mis_cursos.asistencia');
        Route::post('/docente/cursos/{id}/asistencia/guardar', [MisCursosController::class, 'registrarAsistencia'])->name('mis_cursos.asistencia.store');
        Route::post('/docente/cursos/{id}/sincronizar-moodle-estudiantes', [MisCursosController::class, 'sincronizarEstudiantes'])->name('mis_cursos.sincronizar_estudiantes');
        Route::post('/docente/cursos/{id}/sincronizar-moodle-notas', [MisCursosController::class, 'sincronizarNotasMoodle'])->name('mis_cursos.sincronizar_moodle_notas');
        Route::get('/docente/cursos/{id}/revisiones', [MisCursosController::class, 'listarRevisiones'])->name('mis_cursos.revisiones.list');
        Route::post('/docente/cursos/{id}/revisiones', [MisCursosController::class, 'guardarRevision'])->name('mis_cursos.revisiones.store');
        Route::post('/docente/cursos/revisiones/resolver', [MisCursosController::class, 'resolverRevision'])->name('mis_cursos.revisiones.resolver');
        Route::post('/docente/cursos/revisiones/eliminar', [MisCursosController::class, 'eliminarRevision'])->name('mis_cursos.revisiones.delete');
        Route::get('/docente/cursos/{id}/cortina', [MisCursosController::class, 'verCortina'])->name('mis_cursos.cortina');
        Route::get('/docente/cursos/{id}/crear-portada', [MisCursosController::class, 'verCrearPortada'])->name('mis_cursos.crear_portada');
        Route::post('/docente/cursos/{id}/crear-portada/guardar', [MisCursosController::class, 'guardarPortada'])->name('mis_cursos.crear_portada.guardar');
        Route::get('/docente/cursos/{id}/notificaciones-clase', [MisCursosController::class, 'verNotificacionesClase'])->name('mis_cursos.notificaciones_clase');
        Route::post('/docente/cursos/{id}/notificaciones-clase/enviar', [MisCursosController::class, 'enviarNotificacionClase'])->name('mis_cursos.notificaciones_clase.enviar');
        Route::get('/docente/cursos/{id}/gestionar-silabo', [MisCursosController::class, 'verGestionarSilabo'])->name('mis_cursos.gestionar_silabo');
        Route::post('/docente/cursos/{id}/gestionar-silabo/guardar', [MisCursosController::class, 'guardarSilabo'])->name('mis_cursos.gestionar_silabo.guardar');
        Route::get('/docente/cursos/{id}/silabo-pdf', [MisCursosController::class, 'verSilaboPdf'])->name('mis_cursos.silabo_pdf');
        Route::get('/docente/cursos/{id}/acta-pdf', [MisCursosController::class, 'generarActaPdf'])->name('mis_cursos.acta_pdf');
        
        Route::get('/docente/cursos/{id}/recursos-drive', [MisCursosController::class, 'verRecursosDrive'])->name('mis_cursos.recursos_drive');
        Route::post('/docente/cursos/{id}/recursos-drive/guardar', [MisCursosController::class, 'guardarRecursoDrive'])->name('mis_cursos.recursos_drive.guardar');
        Route::get('/docente/cursos/{id}/recursos-drive/{recurso_id}/eliminar', [MisCursosController::class, 'eliminarRecursoDrive'])->name('mis_cursos.recursos_drive.eliminar');

        Route::get('/docente/recursos', [RecursoProfesorController::class, 'index'])->name('recursos.index');
        Route::post('/docente/recursos/categoria', [RecursoProfesorController::class, 'storeCategoria'])->name('recursos.categoria.store');
        Route::post('/docente/recursos/categoria/{id}/editar', [RecursoProfesorController::class, 'updateCategoria'])->name('recursos.categoria.update');
        Route::post('/docente/recursos/categoria/{id}/eliminar', [RecursoProfesorController::class, 'destroyCategoria'])->name('recursos.categoria.delete');
        Route::post('/docente/recursos', [RecursoProfesorController::class, 'storeRecurso'])->name('recursos.store');
        Route::post('/docente/recursos/{id}/editar', [RecursoProfesorController::class, 'updateRecurso'])->name('recursos.update');
        Route::post('/docente/recursos/{id}/eliminar', [RecursoProfesorController::class, 'destroyRecurso'])->name('recursos.delete');
        Route::get('/docente/recursos-compartidos', [RecursoProfesorController::class, 'compartidos'])->name('recursos.compartidos');

        Route::get('/tutoriales', [VideotutorialController::class, 'index'])->name('videotutoriales.index');
        Route::post('/tutoriales', [VideotutorialController::class, 'store'])->name('videotutoriales.store');
        Route::post('/tutoriales/{id}/editar', [VideotutorialController::class, 'update'])->name('videotutoriales.update');
        Route::post('/tutoriales/{id}/eliminar', [VideotutorialController::class, 'destroy'])->name('videotutoriales.delete');

        Route::get('/biblioteca', [BibliotecaMediosController::class, 'index'])->name('biblioteca.index');
        Route::post('/biblioteca', [BibliotecaMediosController::class, 'store'])->name('biblioteca.store');
        Route::post('/biblioteca/{id}/eliminar', [BibliotecaMediosController::class, 'destroy'])->name('biblioteca.delete');

        Route::get('/encuestas/preguntas', [EncuestaController::class, 'gestionarPreguntas'])->name('encuestas.preguntas');
        Route::post('/encuestas/preguntas/ajax', [EncuestaController::class, 'ajaxPreguntas'])->name('encuestas.preguntas.ajax');
        Route::get('/encuestas/resultados', [EncuestaController::class, 'resultados'])->name('encuestas.resultados');
        Route::post('/encuestas/{id_curso}/publicar-moodle', [EncuestaController::class, 'publicarMoodle'])->name('encuestas.publicar_moodle');
        Route::post('/encuestas/{id_curso}/reset', [EncuestaController::class, 'resetEncuesta'])->name('encuestas.reset');
    });

    // --- LOGÍSTICA & INVENTARIO ---
    Route::middleware('module:inventario')->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::get('/inventario/operaciones', [InventarioController::class, 'operaciones'])->name('inventario.operaciones');
        Route::post('/inventario/operaciones/guardar', [InventarioController::class, 'registrarMovimiento'])->name('inventario.movimiento.store');
    });
});

// --- FIRMA DIGITAL PÚBLICA DE BOLETA PARA ESTUDIANTE (SIN LOGIN) ---
Route::get('/boletas/firmar/{token}', [BoletaController::class, 'firmarPublico'])->name('boletas.firmar_publico');
Route::post('/boletas/firmar/{token}/guardar', [BoletaController::class, 'guardarFirmaPublica'])->name('boletas.guardar_firma_publica');

// --- DEMO AUTOMATIZADO / SEMBRAR ALUMNO COMPLETO 360° ---
Route::get('/sistema/sembrar-demo', function () {
    try {
        $seeder = new \Database\Seeders\AlumnoDemoCompletoSeeder();
        $res = $seeder->run();

        $alumno = $res['alumno'] ?? null;
        $cursos = $res['cursos'] ?? [];
        $convalidacion = $res['convalidacion'] ?? null;
        $boleta = $res['boleta'] ?? null;
        $tcu = $res['tcu'] ?? null;

        $email = $alumno ? $alumno->email : 'carlos.alvarado@ceficr.com';
        $nombreCompleto = $alumno ? ($alumno->nombre . ' ' . $alumno->apellidos) : 'Carlos Eduardo Alvarado Mora';
        $cedula = $alumno ? $alumno->cedula : '1-1850-0492';
        $boletaNumero = $boleta ? $boleta->numero_boleta : 'BOL-2026-0088';
        $boletaTotal = $boleta ? number_format($boleta->total ?? 180000, 2) : '180,000.00';
        $boletaPagado = $boleta ? number_format($boleta->monto_pagado ?? 120000, 2) : '120,000.00';
        $boletaSaldo = $boleta ? number_format($boleta->saldo_pendiente ?? 60000, 2) : '60,000.00';

        return response("
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Alumno Demo Creado con Éxito - CEFI</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
            <style>
                body { background: #0f172a; color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; padding: 40px 15px; }
                .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
                .accent-pill { background: linear-gradient(135deg, #0284c7, #2563eb); color: white; padding: 4px 14px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; display: inline-block; }
                .success-pill { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 4px 12px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; }
                .info-badge { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
                .module-card { background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 18px; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class='container' style='max-width: 900px;'>
                <div class='glass-card p-4 p-md-5'>
                    <div class='d-flex align-items-center justify-content-between border-bottom pb-4 mb-4 border-secondary border-opacity-25'>
                        <div>
                            <span class='accent-pill mb-2'><i class='fa-solid fa-graduation-cap me-1'></i> Entorno de Pruebas Institucional</span>
                            <h2 class='fw-bold mb-0 text-white'>Alumno Demo 360° Sembrado</h2>
                            <p class='text-secondary mb-0'>Se ha configurado todo el ciclo del estudiante: expediente, matrícula, notas, boletas y TCU.</p>
                        </div>
                        <div class='text-end'>
                            <span class='success-pill'><i class='fa-solid fa-check-circle me-1'></i> 100% Operativo</span>
                        </div>
                    </div>

                    <!-- Credenciales -->
                    <div class='alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 text-white p-3 rounded-3 mb-4'>
                        <h5 class='fw-bold text-info mb-2'><i class='fa-solid fa-key me-2'></i>Credenciales para Iniciar Sesión como Alumno:</h5>
                        <div class='row g-3'>
                            <div class='col-sm-6'><strong>Correo:</strong> <code class='text-warning fs-6'>{$email}</code></div>
                            <div class='col-sm-6'><strong>Contraseña:</strong> <code class='text-warning fs-6'>cefi2026</code></div>
                            <div class='col-sm-6'><strong>Nombre Completo:</strong> {$nombreCompleto}</div>
                            <div class='col-sm-6'><strong>Cédula:</strong> {$cedula} (Rol Alumno)</div>
                        </div>
                    </div>

                    <!-- Resumen del Ciclo -->
                    <div class='row g-3 mb-4'>
                        <!-- Expediente Digital -->
                        <div class='col-md-6'>
                            <div class='module-card h-100'>
                                <h6 class='text-info fw-bold mb-2'><i class='fa-solid fa-folder-open me-2'></i>1. Expediente Digital 360°</h6>
                                <p class='small text-secondary mb-2'>Datos personales, bachillerato de secundaria, antecedentes laborales y checklist de requisitos.</p>
                                <span class='badge bg-success'><i class='fa-solid fa-check me-1'></i> Estado: Aprobado (100% Completo)</span>
                            </div>
                        </div>

                        <!-- Cursos y Notas -->
                        <div class='col-md-6'>
                            <div class='module-card h-100'>
                                <h6 class='text-info fw-bold mb-2'><i class='fa-solid fa-book-bookmark me-2'></i>2. Cursos Matriculados (3)</h6>
                                <ul class='small text-secondary ps-3 mb-0'>
                                    <li><strong class='text-white'>CON-101 Contabilidad I:</strong> <span class='text-success fw-bold'>Nota 92.5 (Aprobado)</span></li>
                                    <li><strong class='text-white'>LEG-102 Legislación Laboral:</strong> <span class='text-success fw-bold'>Nota 87.0 (Aprobado)</span></li>
                                    <li><strong class='text-white'>FIN-201 Finanzas Empresariales:</strong> <span class='text-warning fw-bold'>En Curso (Con Zoom y Silabo)</span></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Convalidaciones -->
                        <div class='col-md-6'>
                            <div class='module-card h-100'>
                                <h6 class='text-info fw-bold mb-2'><i class='fa-solid fa-award me-2'></i>3. Homologación / Convalidación</h6>
                                <p class='small text-secondary mb-1'><strong>U. de Costa Rica:</strong> MAT-100 Matemática General</p>
                                <p class='small text-secondary mb-2'><strong>Resolución:</strong> RES-CEFI-2026-042 (Nota: 90.0)</p>
                                <span class='badge bg-primary'><i class='fa-solid fa-stamp me-1'></i> Reconocimiento Aprobado</span>
                            </div>
                        </div>

                        <!-- Finanzas / Boleta -->
                        <div class='col-md-6'>
                            <div class='module-card h-100'>
                                <h6 class='text-info fw-bold mb-2'><i class='fa-solid fa-file-invoice-dollar me-2'></i>4. Boleta y Estado Financiero</h6>
                                <p class='small text-secondary mb-1'><strong>Boleta:</strong> {$boletaNumero} - ₡{$boletaTotal}</p>
                                <p class='small text-secondary mb-2'><strong>Pagado:</strong> ₡{$boletaPagado} | <strong>Saldo:</strong> <span class='text-warning fw-bold'>₡{$boletaSaldo}</span></p>
                                <span class='badge bg-warning text-dark'><i class='fa-solid fa-clock me-1'></i> Estado: Pago Parcial</span>
                            </div>
                        </div>

                        <!-- TCU -->
                        <div class='col-12'>
                            <div class='module-card'>
                                <h6 class='text-info fw-bold mb-2'><i class='fa-solid fa-hand-holding-heart me-2'></i>5. Trabajo Comunal Universitario (TCU)</h6>
                                <p class='small text-secondary mb-2'>Proyecto: <em>Apoyo Comunal y Alfabetización Financiera San José</em></p>
                                <div class='progress mb-2' style='height: 10px; background: rgba(255,255,255,0.1);'>
                                    <div class='progress-bar bg-success' style='width: 30%;'></div>
                                </div>
                                <span class='small text-light'><strong>45 horas completadas</strong> de 150 requeridas (2 bitácoras registradas)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Enlaces de Navegación -->
                    <div class='d-flex flex-wrap gap-2 pt-3 border-top border-secondary border-opacity-25'>
                        <a href='/estudiante/portal' class='btn btn-primary px-4 fw-bold shadow-sm'><i class='fa-solid fa-graduation-cap me-2'></i>Ir al Portal del Estudiante</a>
                        <a href='/expedientes' class='btn btn-outline-light px-3'><i class='fa-solid fa-folder me-2'></i>Ver Expedientes (Admin)</a>
                        <a href='/boletas' class='btn btn-outline-light px-3'><i class='fa-solid fa-file-invoice me-2'></i>Ver Boletas (Admin)</a>
                        <a href='/convalidaciones' class='btn btn-outline-light px-3'><i class='fa-solid fa-stamp me-2'></i>Ver Convalidaciones</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ", 200, ['Content-Type' => 'text/html']);
    } catch (\Throwable $e) {
        return response("
            <div style='background: #1e1e2d; color: #ff6b6b; padding: 30px; font-family: monospace; border-radius: 8px; margin: 30px auto; max-width: 800px; border: 1px solid #ff4d4f;'>
                <h2><i class='fa fa-exclamation-triangle'></i> Error al Sembrar Datos Demo</h2>
                <p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . " en línea " . $e->getLine() . "</p>
                <pre style='background: #151521; color: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>
            </div>
        ", 500, ['Content-Type' => 'text/html']);
    }
})->name('sistema.sembrar_demo');
