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

// Webhook Receptor Green-API / n8n
Route::post('/api/whatsapp/webhook', [WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

Route::middleware('auth')->group(function () {
    // --- DASHBOARD & TAREAS ---
    Route::middleware('module:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/tareas/cambiar-estado', [DashboardController::class, 'cambiarEstado'])->name('tareas.cambiar_estado');
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
    Route::middleware('module:registro_academico')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios/crear', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::post('/usuarios/{id}/eliminar', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        
        // Cursos y Programas
        Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::get('/cursos/crear', [CursoController::class, 'create'])->name('cursos.create');
        Route::post('/cursos/crear', [CursoController::class, 'store'])->name('cursos.store');
        Route::get('/cursos/plan-estudio/{id}/descriptor-doc', [CursoController::class, 'generarDescriptorDoc'])->name('cursos.descriptor_doc');
        Route::get('/cursos/plan-estudio/{id}/descriptor-pdf', [CursoController::class, 'generarDescriptorPdf'])->name('cursos.descriptor_pdf');
        Route::get('/programas', [ProgramaController::class, 'index'])->name('programas.index');
        Route::get('/programas/crear', [ProgramaController::class, 'create'])->name('programas.create');
        Route::post('/programas/crear', [ProgramaController::class, 'store'])->name('programas.store');
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
    });

    // --- FACTURACIÓN Y BOLETAS DE MATRÍCULA ---
    Route::middleware('module:boletas_matricula')->group(function () {
        Route::get('/boletas', [BoletaController::class, 'index'])->name('boletas.index');
        Route::get('/boletas/generar', [BoletaController::class, 'generar'])->name('boletas.generar');
        Route::post('/boletas/generar', [BoletaController::class, 'store'])->name('boletas.store');
        Route::get('/boletas/cursos-programa/{id}/ajax', [BoletaController::class, 'getCursosPorProgramaAjax'])->name('boletas.cursos_programa_ajax');
        Route::get('/boletas/buscar-estudiante-ajax', [BoletaController::class, 'buscarEstudianteAjax'])->name('boletas.buscar_estudiante_ajax');
        Route::get('/boletas/{id}/pdf', [BoletaController::class, 'verPdf'])->name('boletas.pdf');
        Route::post('/boletas/{id}/pago', [BoletaController::class, 'registrarPago'])->name('boletas.pago.store');
        Route::post('/boletas/{id}/anular', [BoletaController::class, 'anular'])->name('boletas.anular');
        Route::post('/boletas/{id}/enviar-whatsapp', [WhatsAppController::class, 'enviarBoleta'])->name('boletas.enviar_whatsapp');
    });

    // --- FINANZAS, MOROSIDAD Y ESTADO DE CUENTA ---
    Route::middleware('module:finanzas')->group(function () {
        Route::get('/boletas/estado-cuenta', [BoletaController::class, 'estadoCuenta'])->name('boletas.estado_cuenta');
        Route::get('/boletas/estado-cuenta/{id}/ajax', [BoletaController::class, 'getEstadoCuentaAjax'])->name('boletas.estado_cuenta_ajax');
        Route::get('/boletas/morosidad', [BoletaController::class, 'morosidad'])->name('boletas.morosidad');
        Route::post('/finanzas/estudiante/{id}/enviar-whatsapp-mora', [WhatsAppController::class, 'enviarMora'])->name('finanzas.enviar_whatsapp_mora');
    });

    // --- EXPEDIENTES DIGITALES 360° ---
    Route::middleware('module:expedientes_360')->group(function () {
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/expedientes/crear', [ExpedienteController::class, 'create'])->name('expedientes.create');
        Route::post('/expedientes/crear', [ExpedienteController::class, 'store'])->name('expedientes.store');
        Route::get('/expedientes/buscar-usuario-ajax', [ExpedienteController::class, 'buscarUsuarioAjax'])->name('expedientes.buscar_usuario_ajax');
        Route::get('/expedientes/{id}', [ExpedienteController::class, 'ver'])->name('expedientes.ver');
        Route::post('/expedientes/{id}/estado', [ExpedienteController::class, 'cambiarEstado'])->name('expedientes.cambiar_estado');
        Route::post('/expedientes/subir-documento', [ExpedienteController::class, 'subirDocumento'])->name('expedientes.subir_documento');
        Route::post('/expedientes/documentos/{id}/eliminar', [ExpedienteController::class, 'eliminarDocumento'])->name('expedientes.documentos.eliminar');
        Route::get('/expedientes/{id}/observaciones', [ExpedienteController::class, 'listarObservaciones'])->name('expedientes.observaciones.listar');
        Route::post('/expedientes/observaciones/guardar', [ExpedienteController::class, 'guardarObservacion'])->name('expedientes.observaciones.guardar');
        Route::post('/expedientes/observaciones/{id}/eliminar', [ExpedienteController::class, 'eliminarObservacion'])->name('expedientes.observaciones.eliminar');
        Route::get('/expedientes/{id}/descargar-zip', [ExpedienteController::class, 'descargarZip'])->name('expedientes.descargar_zip');
        Route::get('/expedientes/{id}/record-pdf', [ExpedienteController::class, 'generarRecordPdf'])->name('expedientes.record_pdf');
    });

    // --- CONFIGURACIÓN ALERTAS / WHATSAPP ---
    Route::middleware('module:whatsapp_n8n')->group(function () {
        Route::get('/configuracion/whatsapp', [WhatsAppController::class, 'configuracion'])->name('configuracion.whatsapp');
        Route::post('/configuracion/whatsapp', [WhatsAppController::class, 'guardarConfiguracion'])->name('configuracion.whatsapp.store');
        Route::post('/configuracion/whatsapp/test', [WhatsAppController::class, 'testEnvio'])->name('configuracion.whatsapp.test');
        Route::get('/configuracion/alertas', [ConfiguracionController::class, 'alertas'])->name('configuracion.alertas');
        Route::post('/configuracion/alertas', [ConfiguracionController::class, 'guardarAlertas'])->name('configuracion.alertas.store');

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

        // Cerebro AI (submódulo inbox_ai)
        Route::middleware('module:inbox_ai')->group(function () {
            Route::get('/configuracion/conocimiento-ai', [ConfiguracionController::class, 'conocimientoAi'])->name('configuracion.conocimiento_ai');
            Route::post('/configuracion/conocimiento-ai/guardar', [ConfiguracionController::class, 'guardarConocimientoAi'])->name('configuracion.conocimiento_ai.guardar');
            Route::post('/configuracion/conocimiento-ai/{id}/eliminar', [ConfiguracionController::class, 'eliminarConocimientoAi'])->name('configuracion.conocimiento_ai.eliminar');
        });
    });

    // --- ESTUDIANTE & TCU ---
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
    Route::middleware('module:gestion_cursos')->group(function () {
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
