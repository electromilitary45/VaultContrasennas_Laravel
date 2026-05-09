<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.security.index') }}" class="btn btn-outline-secondary btn-sm" title="Volver">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2 flex-grow-1">
                <i class="bi bi-shield-exclamation text-danger"></i>
                <span>Intentos de Login Fallidos</span>
            </h2>
        </div>
    </x-slot>

    <!-- Filtro de días -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.security.failed-logins') }}" class="d-flex align-items-center gap-3">
                <label class="form-label mb-0">Mostrar intentos fallidos de los últimos:</label>
                <select name="days" class="form-select" style="width: auto;" onchange="this.form.submit()">
                    <option value="1" {{ $days == 1 ? 'selected' : '' }}>1 día</option>
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 días</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 días</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 días</option>
                </select>
            </form>
        </div>
    </div>

    @if($total > 0)
        <div class="alert alert-danger border-0 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i>
                <div>
                    <strong>Se encontraron {{ number_format($total) }} intentos de login fallidos en los últimos {{ $days }} días.</strong>
                    Revise estos intentos para detectar posibles ataques o problemas de seguridad.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Email</th>
                                <th>Usuario</th>
                                <th>IP Address</th>
                                <th>Razón</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedLogins as $attempt)
                                <tr>
                                    <td>
                                        <small class="text-body-secondary">
                                            {{ $attempt['timestamp']->format('d/m/Y H:i:s') }}
                                        </small>
                                    </td>
                                    <td>
                                        <code class="small">{{ $attempt['email'] }}</code>
                                    </td>
                                    <td>
                                        @if($attempt['user_id'])
                                            <span class="badge bg-info">ID: {{ $attempt['user_id'] }}</span>
                                        @elseif($attempt['user_exists'] === false)
                                            <span class="badge bg-secondary">Usuario no existe</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Desconocido</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small">{{ $attempt['ip_address'] }}</code>
                                    </td>
                                    <td>
                                        @if($attempt['reason'] === 'account_inactive')
                                            <span class="badge bg-warning text-dark">Cuenta inactiva</span>
                                        @elseif($attempt['reason'] === 'invalid_credentials')
                                            <span class="badge bg-danger">Credenciales inválidas</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $attempt['reason'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-body-secondary" title="{{ $attempt['user_agent'] }}">
                                            {{ \Illuminate\Support\Str::limit($attempt['user_agent'], 50) }}
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
                    No se encontraron intentos de login fallidos en los últimos {{ $days }} días.
                </p>
            </div>
        </div>
    @endif
</x-admin-layout>
