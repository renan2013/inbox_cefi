@extends('layouts.app')

@section('title', 'Inbox BPM - Configurar Empleados')

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
            padding: 0.7rem 1rem;
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
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        /* Dark themed transparent table list */
        .table-custom {
            margin-bottom: 0;
            background-color: transparent !important;
        }
        .table-custom td, .table-custom th {
            background-color: transparent !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid var(--border-dark);
            padding: 1.25rem 1.5rem;
        }

        .avatar-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            background-color: rgba(95, 178, 48, 0.15);
            color: var(--primary);
            object-fit: cover;
            border: 2px solid var(--border-dark);
        }

        /* Modal styling */
        .modal-content-custom {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-white-50"><i class="bi bi-house-door"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planilla.index') }}" class="text-decoration-none text-white-50">Panel de Planilla</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Configurar Empleados</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="page-header">
            <h1 class="display-6 fw-bold mb-1">Configuración de Empleados</h1>
            <p class="text-white-50 mb-0">Gestione las categorías salariales de planilla y configure los datos de credenciales corporativas.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 rounded-4 d-flex align-items-center mb-4" style="background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- TABLA DE EMPLEADOS -->
        <div class="card glass-card">
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr class="bg-dark text-white-50">
                            <th class="ps-4">Foto</th>
                            <th>Empleado</th>
                            <th>Categoría Planilla</th>
                            <th>Cédula</th>
                            <th>Vigencia Credencial</th>
                            <th>Cargo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empleados as $emp)
                            <tr>
                                <td class="ps-4">
                                    @if ($emp->foto_credencial)
                                        <img src="{{ asset($emp->foto_credencial) }}" class="avatar-circle" alt="Foto">
                                    @else
                                        <div class="avatar-circle">
                                            {{ strtoupper(substr($emp->nombre, 0, 1)) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-white d-block">{{ $emp->nombre }} {{ $emp->apellidos }}</span>
                                    <span class="small text-white-50">{{ $emp->email }}</span>
                                </td>
                                <td>
                                    @if ($emp->id_rol_planilla)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 fw-bold border border-success border-opacity-25">{{ $emp->rol_planilla_nombre }}</span>
                                        <span class="d-block text-white-50 small mt-1" style="font-size: 0.75rem;">
                                            {{ $emp->tipo_pago === 'fijo' ? 'Fijo' : 'Horas' }} ({{ $emp->moneda === 'USD' ? '$' : '₡' }}{{ number_format($emp->monto_pago, 2) }})
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-light rounded-pill px-3 py-1.5 fw-bold">Sin categoría</span>
                                    @endif
                                </td>
                                <td>{{ $emp->cedula ?? '-' }}</td>
                                <td>
                                    @if ($emp->vigencia_credencial)
                                        <span class="fw-semibold {{ Carbon\Carbon::parse($emp->vigencia_credencial)->isPast() ? 'text-danger' : 'text-white' }}">
                                            {{ Carbon\Carbon::parse($emp->vigencia_credencial)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $emp->cargo_credencial ?? '-' }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="abrirConfigCategoria({{ json_encode($emp) }})" title="Configurar Categoría">
                                            <i class="bi bi-wallet2"></i> Categoría
                                        </button>
                                        <button class="btn btn-sm btn-success rounded-pill px-3" onclick="abrirConfigCredencial({{ json_encode($emp) }})" style="background-color: var(--primary); border: none;" title="Configurar Credencial">
                                            <i class="bi bi-card-image"></i> Credencial
                                        </button>
                                        <a href="{{ route('planilla.credencial.ver', $emp->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-3" title="Ver Credencial QR">
                                            <i class="bi bi-qr-code"></i> Ver QR
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL CATEGORÍA PLANILLA -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 text-success me-2"></i> Asignar Categoría Salarial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('planilla.tarifa.empleado') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_usuario" id="cat_id_usuario">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Empleado</label>
                            <input type="text" id="cat_empleado_name" class="form-control form-control-custom w-100" readonly style="opacity: 0.7;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Categoría de Planilla</label>
                            <select name="id_rol_planilla" id="cat_id_rol_planilla" class="form-select form-select-custom w-100">
                                <option value="">Remover de Planilla / Sin Categoría</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->nombre }} ({{ $r->tipo_pago === 'fijo' ? 'Fijo' : 'Horas' }} - {{ $r->moneda === 'USD' ? '$' : '₡' }}{{ number_format($r->monto_pago, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIGURAR CREDENCIAL -->
    <div class="modal fade" id="credencialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom text-white">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold"><i class="bi bi-card-image text-success me-2"></i> Configurar Credencial QR</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('planilla.credencial.empleado') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_usuario" id="cred_id_usuario">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label-custom">Empleado</label>
                            <input type="text" id="cred_empleado_name" class="form-control form-control-custom w-100" readonly style="opacity: 0.7;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Número de Cédula</label>
                            <input type="text" name="cedula" id="cred_cedula" class="form-control form-control-custom w-100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Cargo en la Credencial</label>
                            <input type="text" name="cargo_credencial" id="cred_cargo" class="form-control form-control-custom w-100" placeholder="Ej: Gestor de Cobro" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Fecha de Vigencia</label>
                            <input type="date" name="vigencia_credencial" id="cred_vigencia" class="form-control form-control-custom w-100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-custom">Foto del Colaborador</label>
                            <input type="file" name="foto" class="form-control form-control-custom w-100" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4" style="background-color: var(--primary); border: none;">Guardar Credencial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function abrirConfigCategoria(emp) {
            $('#cat_id_usuario').val(emp.id);
            $('#cat_empleado_name').val(emp.nombre + ' ' + emp.apellidos);
            $('#cat_id_rol_planilla').val(emp.id_rol_planilla || '');
            $('#categoriaModal').modal('show');
        }

        function abrirConfigCredencial(emp) {
            $('#cred_id_usuario').val(emp.id);
            $('#cred_empleado_name').val(emp.nombre + ' ' + emp.apellidos);
            $('#cred_cedula').val(emp.cedula || '');
            $('#cred_cargo').val(emp.cargo_credencial || '');
            
            if (emp.vigencia_credencial) {
                const dateVal = emp.vigencia_credencial.split('T')[0];
                $('#cred_vigencia').val(dateVal);
            } else {
                $('#cred_vigencia').val('');
            }
            $('#credencialModal').modal('show');
        }
    </script>
@endsection
