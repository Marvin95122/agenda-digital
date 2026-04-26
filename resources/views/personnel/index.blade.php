@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold text-primary">Gestión de Personal</h3>
            <p class="text-muted">Control de acceso para enfermería.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newNurseModal">
                <i class="bi bi-person-plus-fill me-1"></i> Alta de Personal
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Nombre</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3 text-center">Turno</th>
                        <th class="py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personnel as $nurse)
                        <tr>
                            <td class="ps-4 py-3 fw-bold">{{ $nurse->name }}</td>
                            <td class="py-3">{{ $nurse->email }}</td>
                            <td class="py-3 text-center"><span class="badge bg-info text-dark">{{ $nurse->shift }}</span></td>
                            <td class="py-3 text-center">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $nurse->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $nurse->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="delete-form-{{ $nurse->id }}" action="{{ route('personnel.destroy', $nurse->id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>

                        <div class="modal fade" id="editModal{{ $nurse->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fw-bold">Editar: {{ $nurse->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('personnel.update', $nurse->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nombre</label>
                                                <input type="text" name="name" class="form-control" value="{{ $nurse->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Correo</label>
                                                <input type="email" name="email" class="form-control" value="{{ $nurse->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Turno</label>
                                                <select name="shift" class="form-select">
                                                    <option value="Mañana" {{ $nurse->shift == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                                    <option value="Tarde" {{ $nurse->shift == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                                    <option value="Noche" {{ $nurse->shift == 'Noche' ? 'selected' : '' }}>Noche</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Contraseña (Solo si desea cambiarla)</label>
                                                <input type="password" name="password" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="4" class="text-center py-4">No hay personal registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newNurseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Nueva Enfermera/o</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('personnel.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre Completo</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej. Ana Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Correo Institucional</label>
                        <input type="email" name="email" class="form-control" required placeholder="ana@hospital.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Contraseña</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small">Turno</label>
                            <select name="shift" class="form-select" required>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                                <option value="Noche">Noche</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Personal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: '¿Eliminar personal?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        });
    }
</script>
@endsection