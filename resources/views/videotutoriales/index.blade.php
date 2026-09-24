@extends('layouts.app')

@section('title', 'Inbox BPM - Videotutoriales')

@section('styles')
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .form-control-custom, .form-select-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 0.9rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .form-label-custom {
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Filter buttons */
        .filter-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
            border: 1px solid var(--border-dark);
            background-color: transparent;
            color: #94a3b8;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(95, 178, 48, 0.3);
        }

        /* Video Card Grid Item */
        .video-card {
            background: rgba(30, 41, 59, 0.3);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            transition: all 0.3s;
            position: relative;
        }
        .video-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .video-thumbnail-wrapper {
            position: relative;
            aspect-ratio: 16 / 9;
            background-color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            border-top-left-radius: 1.25rem;
            border-top-right-radius: 1.25rem;
            overflow: hidden;
            cursor: pointer;
        }
        .video-play-btn {
            font-size: 3rem;
            color: var(--primary);
            transition: transform 0.2s;
        }
        .video-thumbnail-wrapper:hover .video-play-btn {
            transform: scale(1.15);
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Videotutoriales</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Videotutoriales de Capacitación</h1>
                <p class="text-white-50 mb-0">Suite autoinstruccional para el dominio integral de los hitos y procesos académicos en Inbox.</p>
            </div>
            
            @if ($es_admin)
                <div>
                    <button type="button" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;" data-bs-toggle="modal" data-bs-target="#modalVideo">
                        <i class="bi bi-plus-circle me-2"></i> Registrar Video
                    </button>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- FILTER CATEGORY BAR -->
        <div class="d-flex flex-wrap gap-2 mb-5">
            <button class="filter-btn active" onclick="filterCategory('todos')">Todos los Temas</button>
            <button class="filter-btn" onclick="filterCategory('inicio')">Primeros Pasos</button>
            <button class="filter-btn" onclick="filterCategory('silabo')">Sílabo & Rúbricas</button>
            <button class="filter-btn" onclick="filterCategory('asistencia')">Asistencias</button>
            <button class="filter-btn" onclick="filterCategory('calificaciones')">Notas & Actas</button>
            <button class="filter-btn" onclick="filterCategory('facturacion')">Facturas Docentes</button>
        </div>

        <!-- VIDEOS GRID -->
        <div class="row g-4" id="videosContainer">
            @if (count($videos) > 0)
                @foreach ($videos as $v)
                    <div class="col-lg-4 col-md-6 video-item-card" data-category="{{ $v->categoria }}">
                        <div class="card video-card text-white h-100 d-flex flex-column justify-content-between">
                            
                            @if ($es_admin)
                                <div class="position-absolute top-0 end-0 p-2 d-flex gap-1" style="z-index: 10;">
                                    <button class="btn btn-xs btn-outline-light border-0" onclick='editarVideo({{ json_encode($v) }})'><i class="bi bi-pencil-fill"></i></button>
                                    <form action="{{ route('videotutoriales.delete', $v->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este videotutorial?')">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-danger border-0"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                </div>
                            @endif

                            <div class="video-thumbnail-wrapper" onclick="playVideo('{{ $v->video_url }}', '{{ addslashes($v->titulo) }}')">
                                <i class="bi bi-play-circle-fill video-play-btn"></i>
                                <span class="badge bg-dark position-absolute bottom-0 end-0 m-2 rounded-1 small">{{ $v->duracion ?: '0:00' }}</span>
                            </div>

                            <div class="card-body p-4">
                                <span class="badge bg-secondary bg-opacity-25 text-white-50 x-small mb-2 text-uppercase">{{ $v->categoria }}</span>
                                <h5 class="fw-bold mb-2 text-white">{{ $v->titulo }}</h5>
                                <p class="text-white-50 small mb-0">{{ $v->descripcion }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="text-center text-white-50 py-5">No hay videotutoriales de capacitación registrados.</div>
                </div>
            @endif
        </div>

    </div>

    <!-- PLAYER MODAL -->
    <div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="playerModalLabel">Reproductor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopVideo()"></button>
                </div>
                <div class="modal-body p-0 bg-black">
                    <div class="ratio ratio-16x9">
                        <video id="modalVideoPlayer" controls src=""></video>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIDEO MANAGEMENT MODAL (ADMIN) -->
    @if ($es_admin)
        <div class="modal fade" id="modalVideo" tabindex="-1" aria-labelledby="modalVideoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark border border-secondary text-white rounded-4">
                    <form action="{{ route('videotutoriales.store') }}" method="POST" id="formVideo">
                        @csrf
                        <input type="hidden" name="_method" id="videoMethod" value="POST">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title" id="modalVideoLabel">Registrar Videotutorial</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label-custom">Título del Video *</label>
                                <input type="text" name="titulo" id="videoTitulo" class="form-control form-control-custom w-100" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Categoría *</label>
                                <select name="categoria" id="videoCategoria" class="form-select form-select-custom w-100" required>
                                    <option value="inicio">Primeros Pasos</option>
                                    <option value="silabo">Sílabo & Rúbricas</option>
                                    <option value="asistencia">Asistencias</option>
                                    <option value="calificaciones">Notas & Actas</option>
                                    <option value="facturacion">Facturas Docentes</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">URL del Video (MP4) *</label>
                                <input type="url" name="video_url" id="videoUrl" class="form-control form-control-custom w-100" placeholder="https://ejemplo.com/video.mp4" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Duración *</label>
                                <input type="text" name="duracion" id="videoDuracion" class="form-control form-control-custom w-100" placeholder="Ej: 2:30" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-custom">Descripción Detallada</label>
                                <textarea name="descripcion" id="videoDesc" class="form-control form-control-custom w-100" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function filterCategory(category) {
            $('.filter-btn').removeClass('active');
            event.target.classList.add('active');

            if (category === 'todos') {
                $('.video-item-card').removeClass('d-none');
            } else {
                $('.video-item-card').addClass('d-none');
                $(`.video-item-card[data-category="${category}"]`).removeClass('d-none');
            }
        }

        function playVideo(url, title) {
            $('#playerModalLabel').text(title);
            const video = document.getElementById('modalVideoPlayer');
            video.src = url;
            video.load();
            
            var modal = new bootstrap.Modal(document.getElementById('playerModal'));
            modal.show();
            video.play();
        }

        function stopVideo() {
            const video = document.getElementById('modalVideoPlayer');
            video.pause();
            video.src = "";
        }

        @if ($es_admin)
            function editarVideo(v) {
                $('#formVideo').attr('action', "{{ route('videotutoriales.update', ':id') }}".replace(':id', v.id));
                $('#videoMethod').val('POST');
                $('#videoTitulo').val(v.titulo);
                $('#videoCategoria').val(v.categoria);
                $('#videoUrl').val(v.video_url);
                $('#videoDuracion').val(v.duracion);
                $('#videoDesc').val(v.descripcion);

                var modal = new bootstrap.Modal(document.getElementById('modalVideo'));
                modal.show();
            }
        @endif
    </script>
@endsection
