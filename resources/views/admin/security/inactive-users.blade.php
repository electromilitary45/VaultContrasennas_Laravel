<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-person-x text-info"></i>
                <span>Usuarios Inactivos</span>
            </h2>
        </div>
    </x-slot>

    <!-- Filtro de días -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.security.inactive-users') }}" class="d-flex align-items-center gap-3">
                <label class="form-label mb-0">Mostrar usuarios inactivos por más de:</label>
                <select name="days" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="30" {{ $daysInactive == 30 ? 'selected' : '' }}>30 días</option>
                    <option value="90" {{ $daysInactive == 90 ? 'selected' : '' }}>90 días</option>
                    <option value="180" {{ $daysInactive == 180 ? 'selected' : '' }}>180 días</option>
                    <option value="365" {{ $daysInactive == 365 ? 'selected' : '' }}>1 año</option>
                </select>
            </form>
        </div>
    </div>

    @if($total > 0)
        <div class="alert alert-info border-0 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Se encontraron {{ number_format($total) }} usuarios inactivos por más de {{ $daysInactive }} días.</strong>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Último Login</th>
                                <th>Días Inactivo</th>
                                <th>Items en Vault</th>
                                <th>2FA</th>
                                <th>Registrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inactiveUsers as $user)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $user['name'] }}</div>
                                        <small class="text-body-secondary">ID: {{ $user['id'] }}</small>
                                    </td>
                                    <td>{{ $user['email'] }}</td>
                                    <td>
                                        @if($user['last_login_at'])
                                            <small class="text-body-secondary">
                                                {{ \Carbon\Carbon::parse($user['last_login_at'])->format('d/m/Y H:i') }}
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user['days_inactive'] !== null)
                                            <span class="badge bg-{{ $user['days_inactive'] > 180 ? 'danger' : ($user['days_inactive'] > 90 ? 'warning' : 'info') }}">
                                                {{ $user['days_inactive'] }} días
                                            </span>
                                        @else
                                            <span class="badge bg-danger">Nunca inició sesión</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user['vault_items_count'] }}</span>
                                    </td>
                                    <td>
                                        @if($user['has_2fa'])
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y') }}
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
                    No se encontraron usuarios inactivos por más de {{ $daysInactive }} días.
                </p>
            </div>
        </div>
    @endif
</x-admin-layout>
