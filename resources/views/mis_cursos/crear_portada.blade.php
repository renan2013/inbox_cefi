@extends('layouts.app')

@section('title', 'Creador de Portada - ' . $curso->planEstudio->materia)

@section('styles')
<style>
    .selection-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(13, 110, 253, 0.4);
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .lib-item:hover .selection-overlay {
        opacity: 1;
    }
    .lib-item:hover {
        border-color: #0d6efd !important;
        transform: translateY(-2px);
        transition: all 0.2s ease;
    }
    .x-small { font-size: 0.75rem; }
    
    #preview_container div, #moodle_preview_container div {
        user-select: none;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis_cursos.index') }}">Mis Cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis_cursos.ver', $curso->id_curso_activo) }}">{{ $curso->planEstudio->materia }}</a></li>
            <li class="breadcrumb-item active">Creador de Portada</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- PANEL DE CONFIGURACIÓN (Izquierda) -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-gear-fill text-primary me-2"></i>Configuración de Diseño</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Switch Fijar Diseño -->
                    <div class="mb-4">
                        <div class="form-check form-switch p-3 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25">
                            <input class="form-check-input ms-0 me-3" type="checkbox" id="switchFixLayout" style="cursor: pointer;">
                            <label class="form-check-label fw-bold text-primary" for="switchFixLayout" style="cursor: pointer; font-size: 0.85rem;">
                                <i class="bi bi-pin-angle-fill me-1"></i> Fijar Diseño y Posiciones
                            </label>
                            <div class="x-small text-muted mt-1" style="font-size: 0.65rem;">Mantiene medidas y fondos para todos los cursos de forma global.</div>
                        </div>
                    </div>

                    <!-- Pestañas de Navegación de Diseño -->
                    <ul class="nav nav-pills nav-fill mb-4 bg-light p-1 rounded-3" id="designTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold small py-2 rounded-3" id="portada-tab" data-bs-toggle="tab" data-bs-target="#portada-pane" type="button" role="tab"><i class="bi bi-image me-1"></i>Portada Principal</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold small py-2 rounded-3" id="moodle-tab" data-bs-toggle="tab" data-bs-target="#moodle-pane" type="button" role="tab"><i class="bi bi-grid-3x2 me-1"></i>Miniatura Moodle (Tarjeta Curso)</button>
                        </li>
                    </ul>

                    <form action="{{ route('mis_cursos.crear_portada.guardar', $curso->id_curso_activo) }}" method="POST" enctype="multipart/form-data" id="formPortada">
                    @csrf
                    <input type="hidden" name="portada_data" id="portada_data">
                    <input type="hidden" name="miniatura_data" id="miniatura_data">

                    <div class="tab-content" id="designTabsContent">
                        
                        <!-- PANE PORTADA PRINCIPAL -->
                        <div class="tab-pane fade show active" id="portada-pane" role="tabpanel" aria-labelledby="portada-tab">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">1. Fondo de Portada Principal (PNG/JPG)</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="background" id="file_background" class="form-control" accept="image/*" onchange="previewImage(this, 'portada')">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBiblioteca" onclick="abrirBiblioteca('portada')">
                                        <i class="bi bi-images"></i> Biblioteca
                                    </button>
                                </div>
                                <input type="hidden" name="background_path" id="background_path" value="{{ $imagen_inicial_ruta }}">
                            </div>

                            <div class="row g-3">
                                <!-- Bloque Profesor -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-person text-primary me-1"></i>Nombre Profesor</h6>
                                            <input type="text" name="texto_profesor" id="input_texto_prof" class="form-control form-control-sm mb-3" value="{{ trim(($curso->profesor->titulo_academico ?? '') . ' ' . ($curso->profesor->nombre ?? '') . ' ' . ($curso->profesor->apellidos ?? '')) }}">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_profesor" id="input_align_prof" class="form-select form-select-sm">
                                                        <option value="left">Izquierda</option>
                                                        <option value="center">Centro</option>
                                                        <option value="right" selected>Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_profesor" id="input_color_prof" class="form-control form-control-color form-control-sm w-100" value="#ffffff">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_profesor" id="input_x_prof" class="form-control form-control-sm" value="32">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_profesor" id="input_y_prof" class="form-control form-control-sm" value="13">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_profesor" id="input_size_prof" class="form-control form-control-sm" value="30">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bloque Curso -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-book text-primary me-1"></i>Nombre del Curso</h6>
                                            <input type="text" name="texto_curso" id="input_texto_curso" class="form-control form-control-sm mb-3" value="{{ $curso->planEstudio->materia ?? '' }}">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_curso" id="input_align_curso" class="form-select form-select-sm">
                                                        <option value="left">Izquierda</option>
                                                        <option value="center">Centro</option>
                                                        <option value="right" selected>Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_curso" id="input_color_curso" class="form-control form-control-color form-control-sm w-100" value="#ffffff">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_curso" id="input_x_curso" class="form-control form-control-sm" value="32">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_curso" id="input_y_curso" class="form-control form-control-sm" value="75">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_curso" id="input_size_curso" class="form-control form-control-sm" value="42">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bloque Programa -->
                                <div class="col-md-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-mortarboard text-primary me-1"></i>Programa Académico</h6>
                                            <input type="text" name="texto_programa" id="input_texto_prog" class="form-control form-control-sm mb-3" value="{{ mb_strtoupper($curso->planEstudio->programa->nombre_programa ?? '', 'UTF-8') }}">
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_programa" id="input_align_prog" class="form-select form-select-sm">
                                                        <option value="left">Izquierda</option>
                                                        <option value="center" selected>Centro</option>
                                                        <option value="right">Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-2">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_programa" id="input_color_prog" class="form-control form-control-color form-control-sm w-100" value="#000000">
                                                </div>
                                                <div class="col-2">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_programa" id="input_x_prog" class="form-control form-control-sm" value="32">
                                                </div>
                                                <div class="col-2">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_programa" id="input_y_prog" class="form-control form-control-sm" value="203">
                                                </div>
                                                <div class="col-2">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_programa" id="input_size_prog" class="form-control form-control-sm" value="22">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PANE MINIATURA MOODLE -->
                        <div class="tab-pane fade" id="moodle-pane" role="tabpanel" aria-labelledby="moodle-tab">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">1. Fondo de Miniatura Moodle (PNG/JPG)</label>
                                <div class="input-group input-group-sm">
                                    <input type="file" name="background_moodle" id="file_background_moodle" class="form-control" accept="image/*" onchange="previewImage(this, 'miniatura')">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBiblioteca" onclick="abrirBiblioteca('miniatura')">
                                        <i class="bi bi-images"></i> Biblioteca
                                    </button>
                                </div>
                                <input type="hidden" name="background_path_moodle" id="background_path_moodle" value="{{ $imagen_inicial_ruta_moodle }}">
                            </div>

                            <div class="row g-3">
                                <!-- 1. Bloque Curso Moodle (Inferior Izquierda) -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-book text-success me-1"></i>1. Título del Curso (Miniatura)</h6>
                                            <textarea name="texto_curso_moodle" id="input_texto_curso_moodle" class="form-control form-control-sm mb-3 fw-bold" rows="1">{{ mb_strtoupper($curso->planEstudio->materia ?? '', 'UTF-8') }}</textarea>
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_curso_moodle" id="input_align_curso_moodle" class="form-select form-select-sm">
                                                        <option value="left" selected>Izquierda</option>
                                                        <option value="center">Centro</option>
                                                        <option value="right">Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_curso_moodle" id="input_color_curso_moodle" class="form-control form-control-color form-control-sm w-100" value="#ffffff">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_curso_moodle" id="input_x_curso_moodle" class="form-control form-control-sm" value="24">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_curso_moodle" id="input_y_curso_moodle" class="form-control form-control-sm" value="110">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_curso_moodle" id="input_size_curso_moodle" class="form-control form-control-sm" value="32">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Bloque Profesor Moodle (Debajo del curso) -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-person text-success me-1"></i>2. Nombre Profesor (Debajo Curso)</h6>
                                            <input type="text" name="texto_profesor_moodle" id="input_texto_prof_moodle" class="form-control form-control-sm mb-3" value="{{ trim(($curso->profesor->titulo_academico ?? '') . ' ' . ($curso->profesor->nombre ?? '') . ' ' . ($curso->profesor->apellidos ?? '')) }}">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_profesor_moodle" id="input_align_prof_moodle" class="form-select form-select-sm">
                                                        <option value="left" selected>Izquierda</option>
                                                        <option value="center">Centro</option>
                                                        <option value="right">Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_profesor_moodle" id="input_color_prof_moodle" class="form-control form-control-color form-control-sm w-100" value="#ffffff">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_profesor_moodle" id="input_x_prof_moodle" class="form-control form-control-sm" value="24">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_profesor_moodle" id="input_y_prof_moodle" class="form-control form-control-sm" value="155">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_profesor_moodle" id="input_size_prof_moodle" class="form-control form-control-sm" value="20">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Bloque Cuatrimestre y Año (Lado derecho inferior) -->
                                <div class="col-md-6">
                                    <div class="card bg-light border-0 h-100">
                                        <div class="card-body p-3">
                                            <h6 class="small fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-calendar3 text-success me-1"></i>3. Cuatrimestre y Año (Derecha Inferior)</h6>
                                            <input type="text" name="texto_cuatrimestre_moodle" id="input_texto_cuatrimestre_moodle" class="form-control form-control-sm mb-3 fw-bold" value="{{ $periodo_texto_default }}">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <label class="x-small fw-bold mb-1 text-dark">Alineación</label>
                                                    <select name="align_cuatrimestre_moodle" id="input_align_cuatrimestre_moodle" class="form-select form-select-sm">
                                                        <option value="left">Izquierda</option>
                                                        <option value="center">Centro</option>
                                                        <option value="right" selected>Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label class="x-small fw-bold mb-1 text-dark">Color</label>
                                                    <input type="color" name="color_cuatrimestre_moodle" id="input_color_cuatrimestre_moodle" class="form-control form-control-color form-control-sm w-100" value="#000000">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">MargX</label>
                                                    <input type="number" name="x_cuatrimestre_moodle" id="input_x_cuatrimestre_moodle" class="form-control form-control-sm" value="24">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">PosY</label>
                                                    <input type="number" name="y_cuatrimestre_moodle" id="input_y_cuatrimestre_moodle" class="form-control form-control-sm" value="140">
                                                </div>
                                                <div class="col-4">
                                                    <label class="x-small fw-bold mb-1 text-dark">Size px</label>
                                                    <input type="number" name="font_size_cuatrimestre_moodle" id="input_size_cuatrimestre_moodle" class="form-control form-control-sm" value="36">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mensaje de Ayuda -->
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100 p-3 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 text-center text-success small fw-bold">
                                        <i class="bi bi-info-circle-fill me-1"></i> Diseñado exactamente para la tarjeta miniatura de Moodle (600x220)
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Botón de Envío del Formulario Completo -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                        <button type="submit" name="generar_y_guardar" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">
                            <i class="bi bi-save-fill me-2"></i> GUARDAR DISEÑOS
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PANEL DE PREVISUALIZACIÓN (Derecha) -->
        <div class="col-xl-6 col-lg-5">
            <div style="position: sticky; top: 20px;">
                <!-- Visor -->
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-eye-fill text-info me-2"></i>Previsualización en Tiempo Real</h6>
                        <span class="badge bg-light text-dark border fw-normal">Ajuste automático de escala</span>
                    </div>
                    <div class="card-body p-2 text-center">
                        <div id="preview_container" style="position: relative; display: inline-block; border: 1px solid #ddd; width: 100%; min-height: 250px; overflow: hidden; background: #f0f0f0; border-radius: 8px;">
                            <img id="img_preview" src="{{ !empty($imagen_inicial_ruta) ? asset($imagen_inicial_ruta) : '' }}" style="max-width: 100%; height: auto; display: {{ !empty($imagen_inicial_ruta) ? 'block' : 'none' }};">
                            <div id="text_preview_prof" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 100; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></div>
                            <div id="text_preview_curso" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 101; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></div>
                            <div id="text_preview_prog" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 102; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></div>
                            <div id="placeholder_text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #aaa; display: {{ !empty($imagen_inicial_ruta) ? 'none' : 'block' }};">
                                <i class="bi bi-image fs-1 d-block mb-2"></i>
                                Sube una imagen o elige de biblioteca para diseñar
                            </div>
                        </div>

                        <!-- Miniatura Moodle -->
                        <div class="mt-3 border-top pt-3 text-start">
                            <h6 class="small fw-bold text-muted mb-2"><i class="bi bi-card-image me-1"></i>Miniatura para Moodle (Tarjeta Curso)</h6>
                            <div id="moodle_preview_container" style="position: relative; display: inline-block; width: 100%; line-height: 0; overflow: hidden; border: 1px solid #ddd; border-radius: 8px; background: #f8f9fa;">
                                <img id="img_preview_moodle" src="{{ !empty($imagen_inicial_ruta_moodle) ? asset($imagen_inicial_ruta_moodle) : '' }}" style="width: 100%; height: auto; display: {{ !empty($imagen_inicial_ruta_moodle) ? 'block' : 'none' }};">
                                <div id="moodle_preview_curso" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 101; font-weight: 800; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></div>
                                <div id="moodle_preview_prof" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 100; font-weight: 500; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></div>
                                <div id="moodle_preview_cuatrimestre" style="position: absolute; pointer-events: none; line-height: 1.1; white-space: pre-wrap; width: 100%; left: 0; z-index: 102; font-weight: 800;"></div>
                                <div id="placeholder_text_moodle" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #aaa; line-height: 1.4; display: {{ !empty($imagen_inicial_ruta_moodle) ? 'none' : 'block' }};">
                                    <i class="bi bi-grid-3x2 d-block mb-1 fs-4 text-center"></i>
                                    Sin miniatura cargada (Sube una o elige de biblioteca)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Miniatura de Descarga -->
                @if ($existe_portada)
                <div class="card shadow-sm border-0 rounded-4 border-start border-4 border-success overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-7 p-3">
                                <h6 class="mb-3 fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Archivos Guardados</h6>
                                <div class="row g-2">
                                    <div class="col-6 text-center">
                                        <small class="text-muted d-block mb-1 fw-bold" style="font-size: 0.7rem;">Portada Principal</small>
                                        <img src="{{ asset($path_saved_portada) }}?t={{ time() }}" class="img-fluid rounded-3 border shadow-sm w-100" style="max-height: 100px; object-fit: contain; background: #f8f9fa;">
                                    </div>
                                    @if ($existe_moodle)
                                    <div class="col-6 text-center">
                                        <small class="text-muted d-block mb-1 fw-bold" style="font-size: 0.7rem;">Miniatura Moodle</small>
                                        <img src="{{ asset($path_saved_moodle) }}?t={{ time() }}" class="img-fluid rounded-3 border shadow-sm w-100" style="max-height: 100px; object-fit: cover; background: #f8f9fa;">
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5 bg-light border-start p-3 text-center d-flex flex-column justify-content-center gap-2" style="min-height: 180px;">
                                <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">Descargar archivos:</small>
                                <a href="{{ asset($path_saved_portada) }}" download="portada_{{ $curso->id_curso_activo }}.png" class="btn btn-success btn-sm rounded-pill fw-bold py-2 w-100">
                                    <i class="bi bi-download me-1"></i> Portada Principal
                                </a>
                                @if ($existe_moodle)
                                <a href="{{ asset($path_saved_moodle) }}" download="miniatura_moodle_{{ $curso->id_curso_activo }}.png" class="btn btn-outline-success btn-sm rounded-pill fw-bold py-2 w-100">
                                    <i class="bi bi-download me-1"></i> Miniatura Moodle
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Biblioteca -->
<div class="modal fade" id="modalBiblioteca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalBibliotecaTitulo"><i class="bi bi-images me-2"></i>Biblioteca de Medios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row row-cols-2 row-cols-md-3 g-3">
                    @foreach ($biblioteca_imgs as $img)
                        @php
                        $tipo_recurso_item = !empty($img->categoria_academica) ? $img->categoria_academica : 'portada'; 
                        @endphp
                        <div class="col lib-item-container" data-tipo="{{ $tipo_recurso_item }}">
                            <div class="card h-100 border shadow-none rounded-3 position-relative overflow-hidden lib-item" style="cursor: pointer;" onclick="seleccionarDeBiblioteca('{{ $img->ruta_archivo }}')">
                                <div style="height: 120px; overflow: hidden; background-color: #f8f9fa;">
                                    <img src="{{ asset($img->ruta_archivo) }}" class="card-img-top" style="height: 100%; width: 100%; object-fit: contain;">
                                </div>
                                <div class="card-body p-2">
                                    <small class="d-block text-truncate fw-bold mb-1 text-dark">{{ $img->nombre }}</small>
                                    @if ($img->id_programa == ($curso->planEstudio->id_programa ?? 0))
                                        <span class="badge bg-success x-small" style="font-size: 0.65rem;">Programa</span>
                                    @elseif ($img->programa_categoria === ($curso->planEstudio->programa->categoria ?? '') && !empty($curso->planEstudio->programa->categoria))
                                        <span class="badge bg-primary x-small" style="font-size: 0.65rem;">{{ $curso->planEstudio->programa->categoria }}</span>
                                    @endif
                                </div>
                                <div class="selection-overlay d-flex align-items-center justify-content-center">
                                    <span class="badge bg-light text-primary fw-bold shadow-sm px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Seleccionar</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let tipoSeleccionBiblioteca = 'portada';

function abrirBiblioteca(tipo) {
    tipoSeleccionBiblioteca = tipo;
    const modalTitle = document.getElementById('modalBibliotecaTitulo');
    if (modalTitle) {
        modalTitle.innerHTML = tipo === 'miniatura' ? '<i class="bi bi-grid-3x2 me-2"></i>Biblioteca - Miniaturas Moodle' : '<i class="bi bi-images me-2"></i>Biblioteca - Portadas Principales';
    }
    
    // Filtrar elementos visibles en el modal
    document.querySelectorAll('.lib-item-container').forEach(col => {
        const itemTipo = col.getAttribute('data-tipo');
        if (tipo === 'miniatura') {
            if (itemTipo === 'miniatura' || col.innerText.toLowerCase().includes('miniatura')) {
                col.style.display = 'block';
            } else {
                col.style.display = 'none';
            }
        } else {
            if (itemTipo === 'miniatura') {
                col.style.display = 'none';
            } else {
                col.style.display = 'block';
            }
        }
    });
}

function seleccionarDeBiblioteca(ruta) {
    const origin = window.location.origin;
    const fullUrl = ruta.startsWith('http') ? ruta : (origin + '/' + ruta);

    if (tipoSeleccionBiblioteca === 'miniatura') {
        document.getElementById('background_path_moodle').value = ruta;
        const imgMoodle = document.getElementById('img_preview_moodle');
        const placeholderMoodle = document.getElementById('placeholder_text_moodle');
        
        imgMoodle.src = fullUrl;
        imgMoodle.style.display = 'block';
        placeholderMoodle.style.display = 'none';
        
        imgMoodle.onload = function() {
            updateTextPreview();
            if (document.getElementById('switchFixLayout').checked) saveFixedSettings();
        };
    } else {
        document.getElementById('background_path').value = ruta;
        const img = document.getElementById('img_preview');
        const placeholder = document.getElementById('placeholder_text');
        
        img.src = fullUrl;
        img.style.display = 'block';
        placeholder.style.display = 'none';
        
        img.onload = function() {
            updateTextPreview();
            if (document.getElementById('switchFixLayout').checked) saveFixedSettings();
        };
    }
    
    bootstrap.Modal.getInstance(document.getElementById('modalBiblioteca')).hide();
}

function previewImage(input, tipo) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (tipo === 'portada') {
                const img = document.getElementById('img_preview');
                const placeholder = document.getElementById('placeholder_text');
                img.src = e.target.result;
                img.style.display = 'block';
                placeholder.style.display = 'none';
                document.getElementById('background_path').value = '';
                img.onload = function() {
                    updateTextPreview();
                    if (document.getElementById('switchFixLayout').checked) saveFixedSettings();
                };
            } else {
                const imgMoodle = document.getElementById('img_preview_moodle');
                const placeholderMoodle = document.getElementById('placeholder_text_moodle');
                imgMoodle.src = e.target.result;
                imgMoodle.style.display = 'block';
                placeholderMoodle.style.display = 'none';
                document.getElementById('background_path_moodle').value = '';
                imgMoodle.onload = function() {
                    updateTextPreview();
                    if (document.getElementById('switchFixLayout').checked) saveFixedSettings();
                };
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// --- LÓGICA DE FIJADO DE DISEÑO (LocalStorage - Plantilla Global) ---
const switchFix = document.getElementById('switchFixLayout');
const ids_to_save = [
    'input_x_prof', 'input_y_prof', 'input_size_prof', 'input_color_prof', 'input_align_prof',
    'input_x_curso', 'input_y_curso', 'input_size_curso', 'input_color_curso', 'input_align_curso',
    'input_x_prog', 'input_y_prog', 'input_size_prog', 'input_color_prog', 'input_align_prog',
    'background_path',
    
    // Configs de Miniatura Moodle
    'input_texto_curso_moodle', 'input_x_curso_moodle', 'input_y_curso_moodle', 'input_size_curso_moodle', 'input_color_curso_moodle', 'input_align_curso_moodle',
    'input_texto_prof_moodle', 'input_x_prof_moodle', 'input_y_prof_moodle', 'input_size_prof_moodle', 'input_color_prof_moodle', 'input_align_prof_moodle',
    'input_texto_cuatrimestre_moodle', 'input_x_cuatrimestre_moodle', 'input_y_cuatrimestre_moodle', 'input_size_cuatrimestre_moodle', 'input_color_cuatrimestre_moodle', 'input_align_cuatrimestre_moodle',
    'background_path_moodle'
];

function saveFixedSettings() {
    if (switchFix.checked) {
        const values = {};
        ids_to_save.forEach(id => {
            const el = document.getElementById(id);
            if (el) values[id] = el.value;
        });
        localStorage.setItem('portada_global_template', JSON.stringify(values));
        localStorage.setItem('portada_fix_mode', 'on');
    } else {
        localStorage.setItem('portada_fix_mode', 'off');
    }
}

function loadFixedSettings() {
    const fixMode = localStorage.getItem('portada_fix_mode');
    const savedTemplate = localStorage.getItem('portada_global_template');
    
    if (fixMode === 'on' && savedTemplate) {
        switchFix.checked = true;
        const values = JSON.parse(savedTemplate);
        
        for (const [id, value] of Object.entries(values)) {
            const el = document.getElementById(id);
            if (el) {
                el.value = value;
                
                if (id === 'background_path' && value) {
                    const img = document.getElementById('img_preview');
                    const placeholder = document.getElementById('placeholder_text');
                    img.src = value.startsWith('http') ? value : ('/' + value);
                    img.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                
                if (id === 'background_path_moodle' && value) {
                    const imgMoodle = document.getElementById('img_preview_moodle');
                    const placeholderMoodle = document.getElementById('placeholder_text_moodle');
                    imgMoodle.src = value.startsWith('http') ? value : ('/' + value);
                    imgMoodle.style.display = 'block';
                    placeholderMoodle.style.display = 'none';
                }
            }
        }
        setTimeout(updateTextPreview, 300);
    } else if (fixMode === 'off') {
        switchFix.checked = false;
    }
}

switchFix.addEventListener('change', function() {
    saveFixedSettings();
    if (this.checked) {
        Swal.fire({
            icon: 'info',
            title: 'Plantilla Global Activada',
            text: 'Las posiciones y el fondo actual se aplicarán a todos los cursos que abras.',
            timer: 2000,
            showConfirmButton: false
        });
    }
});

ids_to_save.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', () => {
        if (switchFix.checked) saveFixedSettings();
    });
});

document.addEventListener('DOMContentLoaded', function() {
    loadFixedSettings();
    
    const img = document.getElementById('img_preview');
    if (img && img.style.display === 'block') {
        img.addEventListener('load', updateTextPreview);
        if (img.complete) updateTextPreview();
    }
    
    const imgMoodle = document.getElementById('img_preview_moodle');
    if (imgMoodle) {
        const bgMoodlePath = document.getElementById('background_path_moodle').value;
        if (bgMoodlePath) {
            imgMoodle.src = bgMoodlePath.startsWith('http') ? bgMoodlePath : ('/' + bgMoodlePath);
            imgMoodle.style.display = 'block';
            document.getElementById('placeholder_text_moodle').style.display = 'none';
        }
        
        if (imgMoodle.style.display === 'block') {
            imgMoodle.addEventListener('load', updateTextPreview);
            if (imgMoodle.complete) updateTextPreview();
        }
    }
});

function updateTextPreview() {
    const img = document.getElementById('img_preview');
    const imgMoodle = document.getElementById('img_preview_moodle');
    
    // 1. Portada Principal
    if (img && img.style.display !== 'none') {
        const naturalW = img.naturalWidth || 600;
        const currentW = img.clientWidth || 600;
        const scale = currentW / naturalW;

        const configs = [
            {
                input_text: 'input_texto_prof',
                input_x: 'input_x_prof',
                input_y: 'input_y_prof',
                input_size: 'input_size_prof',
                input_color: 'input_color_prof',
                input_align: 'input_align_prof',
                preview_id: 'text_preview_prof'
            },
            {
                input_text: 'input_texto_curso',
                input_x: 'input_x_curso',
                input_y: 'input_y_curso',
                input_size: 'input_size_curso',
                input_color: 'input_color_curso',
                input_align: 'input_align_curso',
                preview_id: 'text_preview_curso'
            },
            {
                input_text: 'input_texto_prog',
                input_x: 'input_x_prog',
                input_y: 'input_y_prog',
                input_size: 'input_size_prog',
                input_color: 'input_color_prog',
                input_align: 'input_align_prog',
                preview_id: 'text_preview_prog'
            }
        ];

        configs.forEach(c => {
            const inputElem = document.getElementById(c.input_text);
            if (!inputElem) return;
            const text = inputElem.value;
            const x = parseFloat(document.getElementById(c.input_x).value) || 0;
            const y = parseFloat(document.getElementById(c.input_y).value) || 0;
            const size = parseFloat(document.getElementById(c.input_size).value) || 20;
            const color = document.getElementById(c.input_color).value;
            const align = document.getElementById(c.input_align).value;
            
            const preview = document.getElementById(c.preview_id);
            if (preview) {
                preview.innerText = text;
                preview.style.top = (y * scale) + 'px';
                preview.style.fontSize = (size * scale) + 'px';
                preview.style.color = color;
                preview.style.textAlign = align;

                if (align === 'center') {
                    preview.style.left = '0';
                    preview.style.paddingLeft = '0';
                    preview.style.paddingRight = '0';
                } else if (align === 'right') {
                    preview.style.left = '0';
                    preview.style.paddingRight = (x * scale) + 'px';
                    preview.style.paddingLeft = '0';
                } else {
                    preview.style.left = '0';
                    preview.style.paddingLeft = (x * scale) + 'px';
                    preview.style.paddingRight = '0';
                }
            }
        });
    }

    // 2. Miniatura Moodle
    if (imgMoodle && imgMoodle.style.display !== 'none') {
        const currentW_moodle = imgMoodle.clientWidth || 300;
        const currentH_moodle = imgMoodle.clientHeight || 110;
        const scale_x_moodle = currentW_moodle / 600;
        const scale_y_moodle = currentH_moodle / 220;

        const configsMoodle = [
            {
                input_text: 'input_texto_curso_moodle',
                input_x: 'input_x_curso_moodle',
                input_y: 'input_y_curso_moodle',
                input_size: 'input_size_curso_moodle',
                input_color: 'input_color_curso_moodle',
                input_align: 'input_align_curso_moodle',
                preview_id: 'moodle_preview_curso'
            },
            {
                input_text: 'input_texto_prof_moodle',
                input_x: 'input_x_prof_moodle',
                input_y: 'input_y_prof_moodle',
                input_size: 'input_size_prof_moodle',
                input_color: 'input_color_prof_moodle',
                input_align: 'input_align_prof_moodle',
                preview_id: 'moodle_preview_prof'
            },
            {
                input_text: 'input_texto_cuatrimestre_moodle',
                input_x: 'input_x_cuatrimestre_moodle',
                input_y: 'input_y_cuatrimestre_moodle',
                input_size: 'input_size_cuatrimestre_moodle',
                input_color: 'input_color_cuatrimestre_moodle',
                input_align: 'input_align_cuatrimestre_moodle',
                preview_id: 'moodle_preview_cuatrimestre'
            }
        ];

        configsMoodle.forEach(c => {
            const inputElem = document.getElementById(c.input_text);
            if (!inputElem) return;
            const text = inputElem.value;
            const x = parseFloat(document.getElementById(c.input_x).value) || 0;
            const y = parseFloat(document.getElementById(c.input_y).value) || 0;
            const size = parseFloat(document.getElementById(c.input_size).value) || 20;
            const color = document.getElementById(c.input_color).value;
            const align = document.getElementById(c.input_align).value;
            
            const previewMoodle = document.getElementById(c.preview_id);
            if (previewMoodle) {
                previewMoodle.innerText = text;
                previewMoodle.style.top = (y * scale_y_moodle) + 'px';
                previewMoodle.style.fontSize = (size * scale_x_moodle) + 'px';
                previewMoodle.style.color = color;
                previewMoodle.style.textAlign = align;

                if (align === 'center') {
                    previewMoodle.style.left = '0';
                    previewMoodle.style.paddingLeft = '0';
                    previewMoodle.style.paddingRight = '0';
                } else if (align === 'right') {
                    previewMoodle.style.left = '0';
                    previewMoodle.style.paddingRight = (x * scale_x_moodle) + 'px';
                    previewMoodle.style.paddingLeft = '0';
                } else {
                    previewMoodle.style.left = '0';
                    previewMoodle.style.paddingLeft = (x * scale_x_moodle) + 'px';
                    previewMoodle.style.paddingRight = '0';
                }
            }
        });
    }
}

const ids_to_listen = [
    'input_texto_prof', 'input_x_prof', 'input_y_prof', 'input_size_prof', 'input_color_prof', 'input_align_prof',
    'input_texto_curso', 'input_x_curso', 'input_y_curso', 'input_size_curso', 'input_color_curso', 'input_align_curso',
    'input_texto_prog', 'input_x_prog', 'input_y_prog', 'input_size_prog', 'input_color_prog', 'input_align_prog',
    'input_texto_curso_moodle', 'input_x_curso_moodle', 'input_y_curso_moodle', 'input_size_curso_moodle', 'input_color_curso_moodle', 'input_align_curso_moodle',
    'input_texto_prof_moodle', 'input_x_prof_moodle', 'input_y_prof_moodle', 'input_size_prof_moodle', 'input_color_prof_moodle', 'input_align_prof_moodle',
    'input_texto_cuatrimestre_moodle', 'input_x_cuatrimestre_moodle', 'input_y_cuatrimestre_moodle', 'input_size_cuatrimestre_moodle', 'input_color_cuatrimestre_moodle', 'input_align_cuatrimestre_moodle'
];

ids_to_listen.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updateTextPreview);
});

window.addEventListener('resize', updateTextPreview);
</script>
@endsection
