<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-shield-x text-danger"></i>
                <span>Contraseñas Débiles</span>
            </h2>
        </div>
    </x-slot>

    @if($total > 0)
        <div class="alert alert-warning border-0 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Se encontraron {{ number_format($total) }} items con contraseñas débiles.</strong>
                    Se recomienda notificar a los usuarios para que actualicen sus contraseñas.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Item</th>
                                <th>Propietario</th>
                                <th>Longitud</th>
                                <th>Problemas Detectados</th>
                                <th>Creado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weakPasswords as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item['item_title'] }}</div>
                                        <small class="text-body-secondary">ID: {{ $item['item_id'] }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $item['owner_name'] }}</div>
                                        <small class="text-body-secondary">{{ $item['owner_email'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item['password_length'] < 8 ? 'danger' : ($item['password_length'] < 12 ? 'warning' : 'secondary') }}">
                                            {{ $item['password_length'] }} caracteres
                                        </span>
                                    </td>
                                    <td>
                                        @foreach($item['issues'] as $issue)
                                            <span class="badge bg-danger-subtle text-danger mb-1 d-inline-block">{{ $issue }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $item['created_at']->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-check text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3 mb-2">¡Excelente!</h4>
                <p class="text-body-secondary mb-0">
                    No se encontraron contraseñas débiles en el sistema.
                </p>
            </div>
        </div>
    @endif
</x-admin-layout>
