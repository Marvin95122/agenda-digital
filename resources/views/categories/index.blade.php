@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold text-primary">Etiquetas y Categorías</h3>
            <p class="text-muted">Crea colores personalizados para clasificar las tareas (Ej. Medicación, Signos Vitales, Higiene).</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                <i class="bi bi-tags-fill me-1"></i> Nueva Categoría
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Color</th>
                        <th class="py-3">Nombre de la Categoría</th>
                        <th class="py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="ps-4 py-3">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background-color: {{ $category->color }}; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                            </td>
                            <td class="py-3 fw-bold">{{ $category->name }}</td>
                            
                            <td class="py-3 text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCatModal{{ $category->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmCatDelete({{ $category->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                                
                                <form id="delete-cat-form-{{ $category->id }}" action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>

                                <div class="modal fade" id="editCatModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold">Editar Categoría</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('categories.update', $category->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nombre</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Color Identificador</label>
                                                        <input type="color" name="color" class="form-control form-control-color w-100" value="{{ $category->color }}" required>
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
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4">No hay categorías registradas. Comienza creando una.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Categoría</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej. Medicación">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Color Identificador</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="#0d6efd" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmCatDelete(id) {
        Swal.fire({
            title: '¿Eliminar categoría?',
            text: "Las tareas asociadas a esta categoría se quedarán sin etiqueta de color.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-cat-form-' + id).submit();
            }
        });
    }
</script>
@endsection