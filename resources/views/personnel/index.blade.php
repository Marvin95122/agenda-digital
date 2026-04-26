@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="fw-bold">Gestión de Personal de Enfermería</h3>
            <p class="text-muted">Administra los accesos de tu equipo para la Agenda Digital.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newNurseModal">
                + Alta de Nuevo Personal
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 text-center">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4 py-3">Nombre Completo</th>
                        <th class="py-3">Correo</th>
                        <th class="py-3">Turno</th>
                        <th class="py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($personnel as $nurse)
                        <tr>
                            <td class="px-4 py-3 fw-bold text-primary">{{ $nurse->name }}</td>
                            <td class="py-3">{{ $nurse->email }}</td>
                            <td class="py-3"><span class="badge bg-secondary">{{ $nurse->shift }}</span></td>
                            <td class="py-3">
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#editModal{{ $nurse->id }}">Editar</button>
                                
                                <form action="{{ route('personnel.destroy', $nurse->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar a este usuario?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>

                        <div class="modal fade" id="editModal{{ $nurse->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog text-start">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title fw-bold">Editar Datos: {{ $nurse->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('personnel.update', $nurse->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body bg-light">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nombre Completo</label>
                                                <input type="text" name="name" class="form-control" value="{{ $nurse->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Correo</label>
                                                <input type="email" name="email" class="form-control" value="{{ $nurse->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nuevo Turno</label>
                                                <select name="shift" class="form-select" required>
                                                    <option value="Mañana" {{ $nurse->shift == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                                    <option value="Tarde" {{ $nurse->shift == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                                    <option value="Noche" {{ $nurse->shift == 'Noche' ? 'selected' : '' }}>Noche</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 text-muted" style="font-size: 0.85rem;">
                                                <label class="form-label fw-bold">Nueva Contraseña (Opcional)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-info text-white">Actualizar Datos</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">No hay personal registrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection