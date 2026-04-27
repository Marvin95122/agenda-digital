<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Turno</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0d6efd; font-size: 20px; }
        .header p { margin: 5px 0 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f6f9; color: #333; font-weight: bold; }
        .priority-alta { color: #dc3545; font-weight: bold; }
        .firma-box { width: 300px; margin: 60px auto 0 auto; text-align: center; }
        .firma-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p><strong>Generado el:</strong> {{ date('d/m/Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @if($user->role === 'supervisor') <th>Enfermera/o</th> @endif
                <th>Categoría</th>
                <th>Actividad</th>
                <th>Ubicación</th>
                <th>Hora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    @if($user->role === 'supervisor') <td>{{ $task->user->name }}</td> @endif
                    <td>{{ $task->category->name ?? 'Gral' }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->location ?? 'N/A' }}</td>
                    <td>{{ $task->due_time ?? '----' }}</td>
                    <td>{{ ucfirst($task->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $user->role === 'supervisor' ? '6' : '5' }}" style="text-align:center;">No hay tareas registradas para el día de hoy.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="firma-box">
        <div class="firma-line">
            Firma del Responsable<br>
            <strong>{{ $user->name }}</strong><br>
            <small>Agenda Digital Hospitalaria</small>
        </div>
    </div>
</body>
</html>