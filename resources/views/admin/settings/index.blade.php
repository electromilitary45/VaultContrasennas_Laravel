<x-admin-layout>
    <x-slot name="header">
        <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-gear text-secondary"></i>
            <span>Configuración del Sistema</span>
        </h2>
    </x-slot>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs de navegación -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                <i class="bi bi-sliders"></i> General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                <i class="bi bi-envelope"></i> Email/SMTP
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                <i class="bi bi-shield-lock"></i> Seguridad
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                <i class="bi bi-tools"></i> Mantenimiento
            </button>
        </li>
    </ul>

    <!-- Contenido de tabs -->
    <div class="tab-content" id="settingsTabsContent">
        <!-- Tab: Configuración General -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-sliders"></i> Configuración General del Sistema
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Nombre de la aplicación -->
                            <div class="col-md-6">
                                <label for="app_name" class="form-label">Nombre de la Aplicación</label>
                                <input type="text" 
                                       class="form-control @error('app_name') is-invalid @enderror" 
                                       id="app_name" 
                                       name="app_name" 
                                       value="{{ old('app_name', \App\Models\SystemSetting::get('app_name', $defaultSettings['general']['app_name'])) }}">
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">Nombre que se mostrará en la aplicación</small>
                            </div>

                            <!-- URL de la aplicación -->
                            <div class="col-md-6">
                                <label for="app_url" class="form-label">URL de la Aplicación</label>
                                <input type="url" 
                                       class="form-control @error('app_url') is-invalid @enderror" 
                                       id="app_url" 
                                       name="app_url" 
                                       value="{{ old('app_url', \App\Models\SystemSetting::get('app_url', $defaultSettings['general']['app_url'])) }}">
                                @error('app_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">URL base de la aplicación</small>
                            </div>

                            <!-- Zona horaria -->
                            <div class="col-md-6">
                                <label for="timezone" class="form-label">Zona Horaria</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" 
                                        id="timezone" 
                                        name="timezone">
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" 
                                                {{ old('timezone', \App\Models\SystemSetting::get('timezone', $defaultSettings['general']['timezone'])) === $tz ? 'selected' : '' }}>
                                            {{ $tz }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Idioma -->
                            <div class="col-md-6">
                                <label for="locale" class="form-label">Idioma</label>
                                <select class="form-select @error('locale') is-invalid @enderror" 
                                        id="locale" 
                                        name="locale">
                                    <option value="es" {{ old('locale', \App\Models\SystemSetting::get('locale', $defaultSettings['general']['locale'])) === 'es' ? 'selected' : '' }}>Español</option>
                                    <option value="en" {{ old('locale', \App\Models\SystemSetting::get('locale', $defaultSettings['general']['locale'])) === 'en' ? 'selected' : '' }}>English</option>
                                </select>
                                @error('locale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Configuración General
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Email/SMTP -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-envelope"></i> Configuración de Email/SMTP
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update-email') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Mailer -->
                            <div class="col-md-6">
                                <label for="mail_mailer" class="form-label">Mailer</label>
                                <select class="form-select @error('mail_mailer') is-invalid @enderror" 
                                        id="mail_mailer" 
                                        name="mail_mailer">
                                    <option value="smtp" {{ old('mail_mailer', \App\Models\SystemSetting::get('mail_mailer', $defaultSettings['email']['mail_mailer'])) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ old('mail_mailer', \App\Models\SystemSetting::get('mail_mailer', $defaultSettings['email']['mail_mailer'])) === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun" {{ old('mail_mailer', \App\Models\SystemSetting::get('mail_mailer', $defaultSettings['email']['mail_mailer'])) === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ old('mail_mailer', \App\Models\SystemSetting::get('mail_mailer', $defaultSettings['email']['mail_mailer'])) === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                                @error('mail_mailer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Host -->
                            <div class="col-md-6">
                                <label for="mail_host" class="form-label">Host SMTP</label>
                                <input type="text" 
                                       class="form-control @error('mail_host') is-invalid @enderror" 
                                       id="mail_host" 
                                       name="mail_host" 
                                       value="{{ old('mail_host', \App\Models\SystemSetting::get('mail_host', $defaultSettings['email']['mail_host'])) }}">
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Puerto -->
                            <div class="col-md-6">
                                <label for="mail_port" class="form-label">Puerto</label>
                                <input type="number" 
                                       class="form-control @error('mail_port') is-invalid @enderror" 
                                       id="mail_port" 
                                       name="mail_port" 
                                       value="{{ old('mail_port', \App\Models\SystemSetting::get('mail_port', $defaultSettings['email']['mail_port'])) }}">
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Encriptación -->
                            <div class="col-md-6">
                                <label for="mail_encryption" class="form-label">Encriptación</label>
                                <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                        id="mail_encryption" 
                                        name="mail_encryption">
                                    <option value="">Ninguna</option>
                                    <option value="tls" {{ old('mail_encryption', \App\Models\SystemSetting::get('mail_encryption', $defaultSettings['email']['mail_encryption'])) === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption', \App\Models\SystemSetting::get('mail_encryption', $defaultSettings['email']['mail_encryption'])) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Usuario -->
                            <div class="col-md-6">
                                <label for="mail_username" class="form-label">Usuario</label>
                                <input type="text" 
                                       class="form-control @error('mail_username') is-invalid @enderror" 
                                       id="mail_username" 
                                       name="mail_username" 
                                       value="{{ old('mail_username', \App\Models\SystemSetting::get('mail_username', $defaultSettings['email']['mail_username'])) }}">
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Contraseña -->
                            <div class="col-md-6">
                                <label for="mail_password" class="form-label">Contraseña</label>
                                <input type="password" 
                                       class="form-control @error('mail_password') is-invalid @enderror" 
                                       id="mail_password" 
                                       name="mail_password" 
                                       placeholder="Dejar vacío para no cambiar">
                                @error('mail_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">La contraseña se almacenará cifrada</small>
                            </div>

                            <!-- Dirección del remitente -->
                            <div class="col-md-6">
                                <label for="mail_from_address" class="form-label">Dirección del Remitente</label>
                                <input type="email" 
                                       class="form-control @error('mail_from_address') is-invalid @enderror" 
                                       id="mail_from_address" 
                                       name="mail_from_address" 
                                       value="{{ old('mail_from_address', \App\Models\SystemSetting::get('mail_from_address', $defaultSettings['email']['mail_from_address'])) }}">
                                @error('mail_from_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre del remitente -->
                            <div class="col-md-6">
                                <label for="mail_from_name" class="form-label">Nombre del Remitente</label>
                                <input type="text" 
                                       class="form-control @error('mail_from_name') is-invalid @enderror" 
                                       id="mail_from_name" 
                                       name="mail_from_name" 
                                       value="{{ old('mail_from_name', \App\Models\SystemSetting::get('mail_from_name', $defaultSettings['email']['mail_from_name'])) }}">
                                @error('mail_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Configuración de Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Políticas de Seguridad -->
        <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-shield-lock"></i> Políticas de Seguridad
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update-security') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Longitud mínima de contraseña -->
                            <div class="col-md-6">
                                <label for="password_min_length" class="form-label">Longitud Mínima de Contraseña</label>
                                <input type="number" 
                                       class="form-control @error('password_min_length') is-invalid @enderror" 
                                       id="password_min_length" 
                                       name="password_min_length" 
                                       min="8" 
                                       max="128"
                                       value="{{ old('password_min_length', \App\Models\SystemSetting::get('password_min_length', $defaultSettings['security']['password_min_length'])) }}">
                                @error('password_min_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">Mínimo recomendado: 12 caracteres</small>
                            </div>

                            <!-- Requisitos de contraseña -->
                            <div class="col-12">
                                <label class="form-label">Requisitos de Contraseña</label>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="password_require_uppercase" 
                                                   name="password_require_uppercase" 
                                                   value="1"
                                                   {{ old('password_require_uppercase', \App\Models\SystemSetting::get('password_require_uppercase', $defaultSettings['security']['password_require_uppercase'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="password_require_uppercase">
                                                Requerir mayúsculas
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="password_require_lowercase" 
                                                   name="password_require_lowercase" 
                                                   value="1"
                                                   {{ old('password_require_lowercase', \App\Models\SystemSetting::get('password_require_lowercase', $defaultSettings['security']['password_require_lowercase'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="password_require_lowercase">
                                                Requerir minúsculas
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="password_require_numbers" 
                                                   name="password_require_numbers" 
                                                   value="1"
                                                   {{ old('password_require_numbers', \App\Models\SystemSetting::get('password_require_numbers', $defaultSettings['security']['password_require_numbers'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="password_require_numbers">
                                                Requerir números
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="password_require_symbols" 
                                                   name="password_require_symbols" 
                                                   value="1"
                                                   {{ old('password_require_symbols', \App\Models\SystemSetting::get('password_require_symbols', $defaultSettings['security']['password_require_symbols'])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="password_require_symbols">
                                                Requerir símbolos
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Duración de sesión -->
                            <div class="col-md-6">
                                <label for="session_lifetime" class="form-label">Duración de Sesión (minutos)</label>
                                <input type="number" 
                                       class="form-control @error('session_lifetime') is-invalid @enderror" 
                                       id="session_lifetime" 
                                       name="session_lifetime" 
                                       min="1" 
                                       max="525600"
                                       value="{{ old('session_lifetime', \App\Models\SystemSetting::get('session_lifetime', $defaultSettings['security']['session_lifetime'])) }}">
                                @error('session_lifetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Intentos máximos de login -->
                            <div class="col-md-6">
                                <label for="max_login_attempts" class="form-label">Intentos Máximos de Login</label>
                                <input type="number" 
                                       class="form-control @error('max_login_attempts') is-invalid @enderror" 
                                       id="max_login_attempts" 
                                       name="max_login_attempts" 
                                       min="1" 
                                       max="20"
                                       value="{{ old('max_login_attempts', \App\Models\SystemSetting::get('max_login_attempts', $defaultSettings['security']['max_login_attempts'])) }}">
                                @error('max_login_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duración de bloqueo -->
                            <div class="col-md-6">
                                <label for="lockout_duration" class="form-label">Duración de Bloqueo (minutos)</label>
                                <input type="number" 
                                       class="form-control @error('lockout_duration') is-invalid @enderror" 
                                       id="lockout_duration" 
                                       name="lockout_duration" 
                                       min="1" 
                                       max="1440"
                                       value="{{ old('lockout_duration', \App\Models\SystemSetting::get('lockout_duration', $defaultSettings['security']['lockout_duration'])) }}">
                                @error('lockout_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Requerir 2FA para admins -->
                            <div class="col-md-6">
                                <label class="form-label d-block">Requerir 2FA para Administradores</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="require_2fa_for_admins" 
                                           name="require_2fa_for_admins" 
                                           value="1"
                                           {{ old('require_2fa_for_admins', \App\Models\SystemSetting::get('require_2fa_for_admins', $defaultSettings['security']['require_2fa_for_admins'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_2fa_for_admins">
                                        Activar 2FA obligatorio para administradores
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Políticas de Seguridad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Modo de Mantenimiento -->
        <div class="tab-pane fade" id="maintenance" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body border-0">
                    <h6 class="mb-0 fw-light">
                        <i class="bi bi-tools"></i> Modo de Mantenimiento
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update-maintenance') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Activar/Desactivar mantenimiento -->
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="maintenance_mode" 
                                           name="maintenance_mode" 
                                           value="1"
                                           {{ old('maintenance_mode', \App\Models\SystemSetting::get('maintenance_mode', $defaultSettings['maintenance']['maintenance_mode'])) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="maintenance_mode">
                                        Activar Modo de Mantenimiento
                                    </label>
                                </div>
                                <div class="alert alert-warning border-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Advertencia:</strong> Cuando el modo de mantenimiento está activo, solo los super administradores pueden acceder al sistema.
                                </div>
                            </div>

                            <!-- Mensaje de mantenimiento -->
                            <div class="col-12">
                                <label for="maintenance_message" class="form-label">Mensaje de Mantenimiento</label>
                                <textarea class="form-control @error('maintenance_message') is-invalid @enderror" 
                                          id="maintenance_message" 
                                          name="maintenance_message" 
                                          rows="3"
                                          maxlength="500">{{ old('maintenance_message', \App\Models\SystemSetting::get('maintenance_message', $defaultSettings['maintenance']['maintenance_message'])) }}</textarea>
                                @error('maintenance_message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">Mensaje que se mostrará a los usuarios cuando el sistema esté en mantenimiento</small>
                            </div>

                            <!-- IPs permitidas -->
                            <div class="col-12">
                                <label for="maintenance_allowed_ips" class="form-label">IPs Permitidas (Opcional)</label>
                                <input type="text" 
                                       class="form-control @error('maintenance_allowed_ips') is-invalid @enderror" 
                                       id="maintenance_allowed_ips" 
                                       name="maintenance_allowed_ips" 
                                       placeholder="192.168.1.1, 10.0.0.1"
                                       value="{{ old('maintenance_allowed_ips', \App\Models\SystemSetting::get('maintenance_allowed_ips', $defaultSettings['maintenance']['maintenance_allowed_ips'])) }}">
                                @error('maintenance_allowed_ips')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-body-secondary">Separar múltiples IPs con comas. Dejar vacío para no permitir ninguna IP específica.</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Configuración de Mantenimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
