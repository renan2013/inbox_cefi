@extends('layouts.app')

@section('title', 'Inbox BPM - Lista de Cursos')

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

        .filter-section {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .table-container {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }

        .table-custom thead th {
            background-color: rgba(255, 255, 255, 0.02) !important;
            color: var(--text-muted) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--border-dark);
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border-dark);
            transition: all 0.2s;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table-custom td {
            background-color: transparent !important;
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #e2e8f0 !important;
        }

        .form-control-custom {
            background-color: rgba(15, 23, 42, 0.5);
            border: 2px solid var(--border-dark);
            border-radius: 0.75rem;
            color: #f8fafc;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            background-color: rgba(15, 23, 42, 0.7);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            color: #f8fafc;
            outline: none;
        }

        .btn-search {
            background-color: var(--primary);
            border: none;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(95, 178, 48, 0.25);
        }

        .btn-clear {
            background-color: transparent;
            border: 2px solid var(--border-dark);
            color: var(--text-muted);
            padding: 0.6rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f8fafc;
            border-color: #f8fafc;
        }

        .badge-cuatri {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
            border-radius: 2rem;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            border: 1px solid rgba(95, 178, 48, 0.3);
            white-space: nowrap;
            display: inline-block;
        }

        /* Pagination design override */
        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            background-color: rgba(30, 41, 59, 0.5);
            border-color: var(--border-dark);
            color: var(--text-muted);
            padding: 0.5rem 1rem;
            transition: all 0.2s;
        }

        .page-link:hover {
            background-color: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        /* Acordeón de Programas */
        .accordion-custom .accordion-item {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.25rem !important;
            margin-bottom: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .accordion-custom .accordion-item:hover {
            border-color: rgba(95, 178, 48, 0.4);
        }

        .accordion-custom .accordion-button {
            background-color: var(--card-dark);
            color: #f8fafc;
            padding: 1.25rem 1.75rem;
            box-shadow: none !important;
            border-bottom: 1px solid transparent;
            font-weight: 700;
        }

        .accordion-custom .accordion-button:not(.collapsed) {
            background-color: rgba(95, 178, 48, 0.08);
            color: var(--primary);
            border-bottom: 1px solid var(--border-dark);
        }

        .accordion-custom .accordion-button::after {
            filter: invert(1);
            transition: transform 0.25s ease;
        }

        [data-theme="light"] .accordion-custom .accordion-item {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05) !important;
        }

        [data-theme="light"] .accordion-custom .accordion-button {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .accordion-custom .accordion-button:not(.collapsed) {
            background-color: rgba(95, 178, 48, 0.08) !important;
            color: var(--primary) !important;
            border-bottom-color: #e2e8f0 !important;
        }

        [data-theme="light"] .accordion-custom .accordion-button::after {
            filter: none !important;
        }

        [data-theme="light"] .table-custom td {
            color: #1e293b !important;
        }

        [data-theme="light"] .table-custom td strong,
        [data-theme="light"] .table-custom td .fw-bold {
            color: #0f172a !important;
        }

        [data-theme="light"] .table-custom td small,
        [data-theme="light"] .table-custom td .text-white-50 {
            color: #64748b !important;
        }

        [data-theme="light"] .filter-section {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }

        [data-theme="light"] .form-control-custom {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap g-3">
            <div>
                <h1 class="display-6 fw-bold mb-1">Lista de Cursos y Mallas</h1>
                <p class="text-white-50 mb-0">Gestiona y consulta las materias, códigos y créditos organizados por cada programa académico.</p>
            </div>
            <div>
                <a href="{{ route('cursos.create') }}" class="btn btn-search">
                    <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Curso
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-section">
            <form action="{{ route('cursos.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label text-white-50 fw-semibold mb-2">Buscar Materia o Código</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 border-2" style="border-color: var(--border-dark); border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; color: var(--text-muted);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" id="search" class="form-control form-control-custom border-start-0 ps-0" placeholder="Nombre de materia o código..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="programa_id" class="form-label text-white-50 fw-semibold mb-2">Programa Académico</label>
                    <select name="programa_id" id="programa_id" class="form-select form-control-custom">
                        <option value="">Todos los Programas</option>
                        @foreach ($programas as $prog)
                            <option value="{{ $prog->id_programa }}" {{ request('programa_id') == $prog->id_programa ? 'selected' : '' }}>{{ $prog->nombre_programa }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-search flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('cursos.index') }}" class="btn btn-clear flex-grow-1 text-center">
                        <i class="bi bi-x-circle me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        <!-- Resumen y controles de acordeón -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div class="text-white-50 small">
                <span>Total: <strong class="text-white">{{ $totalCursos }}</strong> materias registradas.</span>
                <span class="ms-2 d-none d-md-inline text-white-50 opacity-75">• Haga clic en la pestaña de cada programa para desplegar sus cursos</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btn-expandir-todo">
                    <i class="bi bi-arrows-expand me-1"></i> Expandir Todo
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btn-colapsar-todo">
                    <i class="bi bi-arrows-collapse me-1"></i> Colapsar Todo
                </button>
            </div>
        </div>

        <!-- Acordeón de Programas Académicos -->
        <div class="accordion accordion-custom" id="accordionProgramasCursos">
            @php $programasMostrados = 0; @endphp
            @foreach ($programas as $prog)
                @php
                    $cursosDelProg = $cursosPorPrograma->get($prog->id_programa, collect());
                    $numCursos = $cursosDelProg->count();
                    // Si se aplicó filtro y este programa no tiene cursos coincidentes, se omite
                    if ($isFiltered && $numCursos === 0) {
                        continue;
                    }
                    $programasMostrados++;
                    // Si está filtrado o se seleccionó específicamente este programa, abrirlo por defecto
                    $estaAbierto = $isFiltered || (request('programa_id') == $prog->id_programa);
                @endphp

                <div class="accordion-item" id="item-prog-{{ $prog->id_programa }}">
                    <h2 class="accordion-header" id="heading-{{ $prog->id_programa }}">
                        <button class="accordion-button {{ $estaAbierto ? '' : 'collapsed' }}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse-{{ $prog->id_programa }}" 
                                aria-expanded="{{ $estaAbierto ? 'true' : 'false' }}" 
                                aria-controls="collapse-{{ $prog->id_programa }}">
                            <div class="d-flex align-items-center justify-content-between w-100 pe-3 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-layers-half text-success fs-5"></i>
                                    <span class="fw-bold">{{ $prog->nombre_programa }}</span>
                                    @if ($prog->categoria)
                                        <span class="badge bg-secondary bg-opacity-25 text-white-50 rounded-pill px-2.5 py-1 small fw-normal">{{ $prog->categoria }}</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation();">
                                    <span class="badge {{ $numCursos > 0 ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-25 text-white-50' }} rounded-pill px-3 py-1.5 fw-bold">
                                        <i class="bi bi-journal-text me-1"></i> {{ $numCursos }} {{ $numCursos == 1 ? 'Materia' : 'Materias' }}
                                    </span>
                                    <a href="{{ route('cursos.create', ['programa_id' => $prog->id_programa]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold" title="Añadir nueva materia a este programa">
                                        <i class="bi bi-plus-lg me-1"></i> Añadir Materia
                                    </a>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $prog->id_programa }}" 
                         class="accordion-collapse collapse {{ $estaAbierto ? 'show' : '' }}" 
                         aria-labelledby="heading-{{ $prog->id_programa }}">
                        <div class="accordion-body p-0">
                            @if ($numCursos > 0)
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Código</th>
                                                <th>Nombre de la Materia</th>
                                                <th>Periodo / Cuatrimestre</th>
                                                <th>Modalidad / Duración</th>
                                                <th class="text-center">Créditos</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-end pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cursosDelProg as $curso)
                                                <tr>
                                                    <td class="ps-4">
                                                        <strong class="text-white">{{ $curso->codigo ?: 'S/C' }}</strong>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold" style="color: #f8fafc;">{{ $curso->materia }}</div>
                                                        <small class="text-white-50">ID Plan: {{ $curso->id_plan }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge-cuatri">{{ $curso->cuatrimestre ?: 'Regular' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="small text-white-50">{{ $curso->modalidad ?? 'Virtual' }}</span>
                                                        @if ($curso->duracion)
                                                            <small class="d-block text-white-50 opacity-75">{{ $curso->duracion }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <strong class="text-white">{{ $curso->creditos ?? 'N/D' }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="text-success fw-bold">₡{{ number_format($curso->precio, 2) }}</span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex justify-content-end gap-1">
                                                            <a href="{{ route('cursos.descriptor_pdf', $curso->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver Descriptor PDF">
                                                                <i class="bi bi-file-earmark-pdf"></i>
                                                            </a>
                                                            <a href="{{ route('cursos.descriptor_doc', $curso->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Descargar Descriptor Word (.doc)">
                                                                <i class="bi bi-file-earmark-word"></i>
                                                            </a>
                                                            <a href="{{ route('cursos.edit', $curso->id_plan) }}" class="btn btn-sm btn-outline-success" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-curso" data-id="{{ $curso->id_plan }}" data-materia="{{ $curso->materia }}" title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-white-50">
                                    <i class="bi bi-journal-x fs-3 d-block mb-2 text-muted"></i>
                                    <p class="mb-2">No hay materias registradas para este programa académico aún.</p>
                                    <a href="{{ route('cursos.create', ['programa_id' => $prog->id_programa]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="bi bi-plus-circle me-1"></i> Registrar Primera Materia
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Cursos sin programa asignado (si hubiera alguno) --}}
            @php
                $cursosHuerfanos = $cursosPorPrograma->filter(function($items, $key) use ($programas) {
                    return empty($key) || !$programas->contains('id_programa', $key);
                })->flatten();
            @endphp
            @if ($cursosHuerfanos->isNotEmpty())
                @php $programasMostrados++; @endphp
                <div class="accordion-item" id="item-prog-huerfanos">
                    <h2 class="accordion-header" id="heading-huerfanos">
                        <button class="accordion-button {{ $isFiltered ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-huerfanos">
                            <div class="d-flex align-items-center justify-content-between w-100 pe-3 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    <span class="fw-bold text-warning">Materias Sin Programa Asignado</span>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1.5 fw-bold">
                                    {{ $cursosHuerfanos->count() }} Materias
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-huerfanos" class="accordion-collapse collapse {{ $isFiltered ? 'show' : '' }}">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Código</th>
                                            <th>Nombre de la Materia</th>
                                            <th>Periodo</th>
                                            <th>Modalidad</th>
                                            <th class="text-center">Créditos</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-end pe-4">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cursosHuerfanos as $curso)
                                            <tr>
                                                <td class="ps-4"><strong class="text-white">{{ $curso->codigo ?: 'S/C' }}</strong></td>
                                                <td>
                                                    <div class="fw-bold" style="color: #f8fafc;">{{ $curso->materia }}</div>
                                                    <small class="text-white-50">ID Plan: {{ $curso->id_plan }}</small>
                                                </td>
                                                <td><span class="badge-cuatri">{{ $curso->cuatrimestre ?: 'Regular' }}</span></td>
                                                <td><span class="small text-white-50">{{ $curso->modalidad ?? 'Virtual' }}</span></td>
                                                <td class="text-center"><strong class="text-white">{{ $curso->creditos ?? 'N/D' }}</strong></td>
                                                <td class="text-center"><span class="text-success fw-bold">₡{{ number_format($curso->precio, 2) }}</span></td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <a href="{{ route('cursos.descriptor_pdf', $curso->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i></a>
                                                        <a href="{{ route('cursos.descriptor_doc', $curso->id_plan) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-word"></i></a>
                                                        <a href="{{ route('cursos.edit', $curso->id_plan) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-pencil"></i></a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-curso" data-id="{{ $curso->id_plan }}" data-materia="{{ $curso->materia }}"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($programasMostrados === 0)
                <div class="card glass-card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-journal-x display-4 d-block mb-3 text-muted"></i>
                        <h5 class="fw-bold text-white mb-2">No se encontraron cursos</h5>
                        <p class="text-white-50 mb-3">No hay materias que coincidan con los criterios de búsqueda especificados.</p>
                        <a href="{{ route('cursos.index') }}" class="btn btn-search">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Ver Todos los Cursos
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Hidden Form for deletion -->
    <form id="form-delete-curso" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: "{{ session('success') }}",
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Atención',
                    text: "{{ session('error') }}"
                });
            @endif

            // Controles de expandir/colapsar todo
            const btnExpandir = document.getElementById('btn-expandir-todo');
            const btnColapsar = document.getElementById('btn-colapsar-todo');

            if (btnExpandir) {
                btnExpandir.addEventListener('click', function() {
                    document.querySelectorAll('#accordionProgramasCursos .accordion-collapse').forEach(el => {
                        el.classList.add('show');
                    });
                    document.querySelectorAll('#accordionProgramasCursos .accordion-button').forEach(btn => {
                        btn.classList.remove('collapsed');
                        btn.setAttribute('aria-expanded', 'true');
                    });
                });
            }

            if (btnColapsar) {
                btnColapsar.addEventListener('click', function() {
                    document.querySelectorAll('#accordionProgramasCursos .accordion-collapse').forEach(el => {
                        el.classList.remove('show');
                    });
                    document.querySelectorAll('#accordionProgramasCursos .accordion-button').forEach(btn => {
                        btn.classList.add('collapsed');
                        btn.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            document.querySelectorAll('.btn-eliminar-curso').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const materia = this.getAttribute('data-materia');

                    Swal.fire({
                        title: '¿Eliminar Materia?',
                        text: `¿Seguro que deseas eliminar "${materia}" del plan de estudios?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete-curso');
                            form.action = `/cursos/${id}/eliminar`;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection
