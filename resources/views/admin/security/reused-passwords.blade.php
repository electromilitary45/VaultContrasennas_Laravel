<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-arrow-repeat text-warning"></i>
                <span>Contraseñas Reutilizadas</span>
            </h2>
        </div>
    </x-slot>

    @if($total > 0)
        <div class="alert alert-warning border-0 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Se encontraron {{ number_format($total) }} contraseñas reutilizadas.</strong>
                    Se recomienda que los usuarios usen contraseñas únicas para cada servicio.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Hash (Prefijo)</th>
                                <th>Veces Reutilizada</th>
                                <th>Items Afectados</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reusedPasswords as $reused)
                                <tr>
                                    <td>
                                        <code class="small">{{ $reused['password_hash'] }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            {{ $reused['reuse_count'] }} veces
                                        </span>
                                    </td>
                                    <td>
                                        <div class="accordion accordion-flush" id="accordion{{ $reused['password_hash'] }}">
                                            <div class="accordion-item border-0">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ md5($reused['password_hash']) }}">
                                                        Ver {{ $reused['reuse_count'] }} items
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ md5($reused['password_hash']) }}" class="accordion-collapse collapse" data-bs-parent="#accordion{{ $reused['password_hash'] }}">
                                                    <div class="accordion-body">
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($reused['items'] as $item)
                                                                <li class="mb-2 pb-2 border-bottom">
                                                                    <div class="fw-semibold">{{ $item['item_title'] }}</div>
                                                                    <small class="text-body-secondary">
                                                                        Propietario: {{ $item['owner_name'] }} ({{ $item['owner_email'] }})<br>
                                                                        Creado: {{ $item['created_at']->format('d/m/Y H:i') }}
                                                                    </small>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                    No se encontraron contraseñas reutilizadas en el sistema.
                </p>
            </div>
        </div>
    @endif
</x-admin-layout>
