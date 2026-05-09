<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle text-primary"></i>
                <span>Crear Item en Grupo: {{ $group->name }}</span>
            </h2>
            <a href="{{ route('groups.items.index', $group) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> 
                    <strong>Por favor, corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="alert alert-info border-0 mb-4">
                <i class="bi bi-info-circle"></i>
                <strong>Nota:</strong> Este item se creará como tuyo y se compartirá automáticamente con todos los miembros del grupo con permiso de edición.
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('groups.items.store', $group) }}" id="vaultItemForm">
                        @csrf

                        <!-- Tipo de Item -->
                        <div class="mb-4">
                            <label for="type" class="form-label fw-semibold">Tipo de Item <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="auth" {{ old('type') === 'auth' ? 'selected' : '' }}>
                                    <i class="bi bi-key"></i> Autenticación (Login)
                                </option>
                                <option value="card" {{ old('type') === 'card' ? 'selected' : '' }}>Tarjeta</option>
                                <option value="note" {{ old('type') === 'note' ? 'selected' : '' }}>Nota Segura</option>
                                <option value="api_key" {{ old('type') === 'api_key' ? 'selected' : '' }}>API Key</option>
                                <option value="ssh_key" {{ old('type') === 'ssh_key' ? 'selected' : '' }}>SSH Key</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Título -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Ej: Gmail, Facebook, Banco XYZ" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Carpeta -->
                        <div class="mb-4">
                            <label for="folder_id" class="form-label fw-semibold">Carpeta</label>
                            <div class="input-group">
                                <select class="form-select @error('folder_id') is-invalid @enderror" id="folder_id" name="folder_id">
                                    <option value="">Sin carpeta</option>
                                    @if(isset($folders) && $folders->count() > 0)
                                        @foreach($folders as $folder)
                                            <option value="{{ $folder->id }}" {{ old('folder_id') == $folder->id ? 'selected' : '' }}>
                                                {{ $folder->getFullPath() }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createFolderModalFromForm" title="Nueva carpeta">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <small class="text-body-secondary d-block mt-1">
                                <i class="bi bi-info-circle"></i> Opcional. Organiza este item en una carpeta.
                            </small>
                            @error('folder_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campos para tipo Auth (Login) -->
                        <div id="authFields" class="item-type-fields" style="display: none;">
                            <div class="border-top pt-4 mb-4">
                                <h5 class="fw-light mb-3">Información de Login</h5>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Usuario/Nombre de Usuario</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                           id="username" name="username" value="{{ old('username') }}"
                                           placeholder="usuario@ejemplo.com">
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" value="{{ old('password') }}"
                                               placeholder="••••••••">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'passwordToggleIcon')">
                                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()" title="Generar contraseña">
                                            <i class="bi bi-shuffle"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">URIs (Sitios web)</label>
                                    <div id="uriContainer">
                                        <div class="input-group mb-2">
                                            <input type="url" class="form-control uri-input" name="uri[]" 
                                                   placeholder="https://ejemplo.com">
                                            <button class="btn btn-outline-danger" type="button" onclick="removeUri(this)" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addUri()">
                                        <i class="bi bi-plus"></i> Agregar URI
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label for="totp_secret" class="form-label d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check text-primary"></i>
                                        TOTP (Autenticación de dos factores)
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace @error('totp_secret') is-invalid @enderror" 
                                               id="totp_secret" name="totp_secret" value="{{ old('totp_secret') }}"
                                               placeholder="JBSWY3DPEHPK3PXP (opcional)"
                                               oninput="updateTotpQrButton()">
                                        <button class="btn btn-outline-primary" type="button" onclick="generateTotpSecret(this)" title="Generar secreto aleatorio">
                                            <i class="bi bi-arrow-clockwise"></i> Generar
                                        </button>
                                        <button class="btn btn-outline-success" type="button" id="showQrBtn" onclick="showTotpQrFromForm()" title="Mostrar QR para escanear" style="display: none;">
                                            <i class="bi bi-qr-code"></i> QR
                                        </button>
                                    </div>
                                    <small class="text-body-secondary d-block mt-1">
                                        <i class="bi bi-info-circle"></i> 
                                        Código secreto Base32 para autenticación de dos factores. 
                                        Compatible con Google Authenticator, Microsoft Authenticator, etc.
                                        <span id="qrHint" style="display: none;" class="d-block mt-1">
                                            <i class="bi bi-lightbulb"></i> 
                                            Haz clic en "QR" para escanear el código con tu app autenticadora.
                                        </span>
                                    </small>
                                    @error('totp_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Modal para QR Code -->
                                <div class="modal fade" id="totpQrModal" tabindex="-1" aria-labelledby="totpQrModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-sm">
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-light" id="totpQrModalLabel">
                                                    <i class="bi bi-qr-code text-primary"></i> Escanear Código QR
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center p-4">
                                                <p class="text-body-secondary mb-3">
                                                    Escanea este código QR con tu app autenticadora (Google Authenticator, Microsoft Authenticator, etc.)
                                                </p>
                                                <div class="d-flex justify-content-center mb-3">
                                                    <canvas id="totpQrCanvas" class="border rounded"></canvas>
                                                </div>
                                                <div class="input-group mb-3" style="max-width: 400px; margin: 0 auto;">
                                                    <input type="text" class="form-control font-monospace" id="totpUriDisplay" readonly>
                                                    <button class="btn btn-outline-secondary" type="button" onclick="copyTotpUri()">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </div>
                                                <small class="text-body-secondary">
                                                    O copia la URI manualmente si prefieres no usar QR
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para tipo Card -->
                        <div id="cardFields" class="item-type-fields" style="display: none;">
                            <div class="border-top pt-4 mb-4">
                                <h5 class="fw-light mb-3">Información de Tarjeta</h5>
                                
                                <div class="mb-3">
                                    <label for="cardholder_name" class="form-label">Nombre del Titular</label>
                                    <input type="text" class="form-control @error('cardholder_name') is-invalid @enderror" 
                                           id="cardholder_name" name="cardholder_name" value="{{ old('cardholder_name') }}"
                                           placeholder="JUAN PEREZ">
                                    @error('cardholder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control @error('card_number') is-invalid @enderror" 
                                           id="card_number" name="card_number" value="{{ old('card_number') }}"
                                           placeholder="1234 5678 9012 3456" maxlength="19">
                                    @error('card_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="brand" class="form-label">Marca</label>
                                        <select class="form-select @error('brand') is-invalid @enderror" id="brand" name="brand">
                                            <option value="">Seleccionar</option>
                                            <option value="Visa" {{ old('brand') === 'Visa' ? 'selected' : '' }}>Visa</option>
                                            <option value="Mastercard" {{ old('brand') === 'Mastercard' ? 'selected' : '' }}>Mastercard</option>
                                            <option value="American Express" {{ old('brand') === 'American Express' ? 'selected' : '' }}>American Express</option>
                                            <option value="Discover" {{ old('brand') === 'Discover' ? 'selected' : '' }}>Discover</option>
                                            <option value="Other" {{ old('brand') === 'Other' ? 'selected' : '' }}>Otra</option>
                                        </select>
                                        @error('brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="exp_month" class="form-label">Mes</label>
                                        <select class="form-select @error('exp_month') is-invalid @enderror" id="exp_month" name="exp_month">
                                            <option value="">MM</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ old('exp_month') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('exp_month')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="exp_year" class="form-label">Año</label>
                                        <select class="form-select @error('exp_year') is-invalid @enderror" id="exp_year" name="exp_year">
                                            <option value="">YYYY</option>
                                            @for($i = date('Y'); $i <= date('Y') + 20; $i++)
                                                <option value="{{ $i }}" {{ old('exp_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error('exp_year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="security_code" class="form-label">Código de Seguridad (CVV)</label>
                                    <div class="input-group" style="max-width: 200px;">
                                        <input type="password" class="form-control @error('security_code') is-invalid @enderror" 
                                               id="security_code" name="security_code" value="{{ old('security_code') }}"
                                               placeholder="123" maxlength="4">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('security_code', 'cvvToggleIcon')">
                                            <i class="bi bi-eye" id="cvvToggleIcon"></i>
                                        </button>
                                    </div>
                                    @error('security_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campos para tipo API Key -->
                        <div id="apiKeyFields" class="item-type-fields" style="display: none;">
                            <div class="border-top pt-4 mb-4">
                                <h5 class="fw-light mb-3">Información de API Key</h5>
                                
                                <div class="mb-3">
                                    <label for="api_key" class="form-label">API Key</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('api_key') is-invalid @enderror" 
                                               id="api_key" name="api_key" value="{{ old('api_key') }}"
                                               placeholder="sk_live_...">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('api_key', 'apiKeyToggleIcon')">
                                            <i class="bi bi-eye" id="apiKeyToggleIcon"></i>
                                        </button>
                                    </div>
                                    @error('api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="api_key_host" class="form-label">Host/URL</label>
                                    <input type="text" class="form-control @error('host') is-invalid @enderror" 
                                           id="api_key_host" name="host" value="{{ old('host') }}"
                                           placeholder="https://api.ejemplo.com">
                                    @error('host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campos para tipo SSH Key -->
                        <div id="sshKeyFields" class="item-type-fields" style="display: none;">
                            <div class="border-top pt-4 mb-4">
                                <h5 class="fw-light mb-3">Información de SSH Key</h5>
                                
                                <div class="mb-3">
                                    <label for="public_key" class="form-label">Clave Pública</label>
                                    <textarea class="form-control font-monospace @error('public_key') is-invalid @enderror" 
                                              id="public_key" name="public_key" rows="4"
                                              placeholder="ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQ...">{{ old('public_key') }}</textarea>
                                    @error('public_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="private_key" class="form-label">Clave Privada</label>
                                    <div class="input-group">
                                        <textarea class="form-control font-monospace @error('private_key') is-invalid @enderror" 
                                                  id="private_key" name="private_key" rows="4"
                                                  placeholder="-----BEGIN OPENSSH PRIVATE KEY-----...">{{ old('private_key') }}</textarea>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('private_key', 'privateKeyToggleIcon')" style="align-self: start; margin-top: 0.375rem;">
                                            <i class="bi bi-eye" id="privateKeyToggleIcon"></i>
                                        </button>
                                    </div>
                                    @error('private_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="passphrase" class="form-label">Frase de Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('passphrase') is-invalid @enderror" 
                                               id="passphrase" name="passphrase" value="{{ old('passphrase') }}"
                                               placeholder="Frase de contraseña para la clave privada">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passphrase', 'passphraseToggleIcon')">
                                            <i class="bi bi-eye" id="passphraseToggleIcon"></i>
                                        </button>
                                    </div>
                                    @error('passphrase')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="ssh_key_host" class="form-label">Host</label>
                                    <input type="text" class="form-control @error('host') is-invalid @enderror" 
                                           id="ssh_key_host" name="host" value="{{ old('host') }}"
                                           placeholder="servidor.ejemplo.com">
                                    @error('host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campos para tipo Note -->
                        <div id="noteFields" class="item-type-fields" style="display: none;">
                            <div class="border-top pt-4 mb-4">
                                <h5 class="fw-light mb-3">Contenido de la Nota</h5>
                            </div>
                        </div>

                        <!-- Notas (común para todos) -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Información adicional...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Favorito -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="favorite" name="favorite" value="1"
                                       {{ old('favorite') ? 'checked' : '' }}>
                                <label class="form-check-label" for="favorite">
                                    <i class="bi bi-star-fill text-warning"></i> Marcar como favorito
                                </label>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('groups.items.index', $group) }}" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Crear y Compartir con Grupo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type');
            if (!typeSelect) return;

            // Función para mostrar/ocultar campos según el tipo
            function toggleFieldsByType() {
                const type = typeSelect.value;
                const allFields = document.querySelectorAll('.item-type-fields');
                
                // Ocultar todos los campos
                allFields.forEach(field => {
                    field.style.display = 'none';
                });
                
                // Mostrar campos según el tipo
                if (type === 'auth') {
                    const authFields = document.getElementById('authFields');
                    if (authFields) authFields.style.display = 'block';
                } else if (type === 'card') {
                    const cardFields = document.getElementById('cardFields');
                    if (cardFields) cardFields.style.display = 'block';
                } else if (type === 'api_key') {
                    const apiKeyFields = document.getElementById('apiKeyFields');
                    if (apiKeyFields) apiKeyFields.style.display = 'block';
                } else if (type === 'ssh_key') {
                    const sshKeyFields = document.getElementById('sshKeyFields');
                    if (sshKeyFields) sshKeyFields.style.display = 'block';
                } else if (type === 'note') {
                    const noteFields = document.getElementById('noteFields');
                    if (noteFields) noteFields.style.display = 'block';
                }
            }

            // Agregar event listener al select
            typeSelect.addEventListener('change', toggleFieldsByType);

            // Mostrar campos si hay error y el tipo está seleccionado
            @if(old('type'))
                toggleFieldsByType();
            @endif
        });

        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password' || input.type === 'text') {
                if (input.tagName === 'TEXTAREA') {
                    // Para textareas, cambiar el tipo no funciona, usar un workaround
                    const isHidden = input.style.webkitTextSecurity === 'disc';
                    input.style.webkitTextSecurity = isHidden ? 'none' : 'disc';
                } else {
                    input.type = input.type === 'password' ? 'text' : 'password';
                }
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            }
        }

        // Agregar URI
        function addUri() {
            const container = document.getElementById('uriContainer');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="url" class="form-control uri-input" name="uri[]" placeholder="https://ejemplo.com">
                <button class="btn btn-outline-danger" type="button" onclick="removeUri(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
            updateUriButtons();
        }

        // Remover URI
        function removeUri(button) {
            button.closest('.input-group').remove();
            updateUriButtons();
        }

        // Actualizar visibilidad de botones de eliminar URI
        function updateUriButtons() {
            const uriInputs = document.querySelectorAll('.uri-input');
            uriInputs.forEach((input, index) => {
                const removeBtn = input.closest('.input-group').querySelector('.btn-outline-danger');
                removeBtn.style.display = uriInputs.length > 1 ? 'block' : 'none';
            });
        }

        // Generar secreto TOTP
        async function generateTotpSecret(button) {
            try {
                const response = await window.axios.post('{{ route('vault.totp.generate-secret') }}', {}, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.secret) {
                    document.getElementById('totp_secret').value = response.data.secret;
                    updateTotpQrButton(); // Actualizar visibilidad del botón QR
                    
                    // Feedback visual
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="bi bi-check"></i> Generado';
                    button.classList.add('btn-success');
                    button.classList.remove('btn-outline-primary');
                    
                    setTimeout(function() {
                        button.innerHTML = originalHTML;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-outline-primary');
                    }, 2000);
                }
            } catch (error) {
                console.error('Error al generar secreto TOTP:', error);
                showAlert('Error', 'Error al generar secreto TOTP. Por favor, inténtalo de nuevo.');
            }
        }

        // Actualizar visibilidad del botón QR
        function updateTotpQrButton() {
            const secretInput = document.getElementById('totp_secret');
            const qrBtn = document.getElementById('showQrBtn');
            const qrHint = document.getElementById('qrHint');
            
            if (secretInput && qrBtn && qrHint) {
                const hasSecret = secretInput.value.trim().length > 0;
                qrBtn.style.display = hasSecret ? 'block' : 'none';
                qrHint.style.display = hasSecret ? 'block' : 'none';
            }
        }

        // Mostrar QR desde el formulario
        function showTotpQrFromForm() {
            const secretInput = document.getElementById('totp_secret');
            const titleInput = document.getElementById('title');
            const usernameInput = document.getElementById('username');
            
            if (!secretInput || !secretInput.value.trim()) {
                showAlert('Aviso', 'Por favor, ingresa o genera un secreto TOTP primero.');
                return;
            }
            
            const secret = secretInput.value.trim();
            const title = titleInput ? titleInput.value.trim() : 'Vault Item';
            const username = usernameInput ? usernameInput.value.trim() : '';
            
            // Generar URI TOTP
            const label = username ? `${title}:${username}` : title;
            const issuer = '{{ config('app.name') }}';
            const totpUri = `otpauth://totp/${encodeURIComponent(label)}?secret=${encodeURIComponent(secret)}&issuer=${encodeURIComponent(issuer)}`;
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('totpQrModal'));
            modal.show();
            
            // Generar QR cuando se muestre el modal
            const canvas = document.getElementById('totpQrCanvas');
            const uriDisplay = document.getElementById('totpUriDisplay');
            
            if (uriDisplay) {
                uriDisplay.value = totpUri;
            }
            
            if (canvas) {
                // Usar API de QR code online
                const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' + encodeURIComponent(totpUri);
                const img = document.createElement('img');
                img.src = qrUrl;
                img.alt = 'QR Code TOTP';
                img.className = 'img-fluid';
                img.onload = function() {
                    const ctx = canvas.getContext('2d');
                    canvas.width = 256;
                    canvas.height = 256;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0, 256, 256);
                };
                img.onerror = function() {
                    // Fallback: mostrar URL directamente
                    canvas.parentElement.innerHTML = '<p class="text-body-secondary">Escanea usando la URI:</p><code class="d-block p-2 bg-body-secondary rounded">' + totpUri + '</code>';
                };
            }
        }

        // Función para copiar URI TOTP
        function copyTotpUri() {
            const uriInput = document.getElementById('totpUriDisplay');
            if (uriInput) {
                uriInput.select();
                document.execCommand('copy');
                
                // Feedback visual
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-secondary');
                
                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }
        }

        // Inicializar visibilidad del botón QR al cargar
        document.addEventListener('DOMContentLoaded', function() {
            updateTotpQrButton();
        });

        // Generar contraseña
        function generatePassword() {
            const length = 16;
            const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById('password').value = password;
            document.getElementById('password').type = 'text';
            document.getElementById('passwordToggleIcon').classList.remove('bi-eye');
            document.getElementById('passwordToggleIcon').classList.add('bi-eye-slash');
        }

        // Formatear número de tarjeta
        document.getElementById('card_number')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    </script>
    @endpush

    <!-- Modal para crear carpeta del grupo desde el formulario -->
    @if(isset($folders))
        @include('groups.items.partials.folder-modal', [
            'modalId' => 'createFolderModalFromForm', 
            'title' => 'Nueva Carpeta del Grupo', 
            'action' => route('groups.folders.store', $group), 
            'method' => 'POST',
            'allFolders' => $folders,
            'group' => $group
        ])
    @endif
</x-app-layout>
