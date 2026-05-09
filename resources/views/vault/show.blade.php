<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-primary"></i>
                <span>{{ $item->title }}</span>
                @if($item->favorite)
                    <i class="bi bi-star-fill text-warning"></i>
                @endif
            </h2>
            <div class="d-flex gap-2">
                @php
                    $userPermission = $userPermission ?? ($item->owner_user_id === Auth::id() ? 'owner' : 'view');
                @endphp
                
                @if(in_array($userPermission, ['owner', 'admin', 'edit']))
                    <a href="{{ route('vault.edit', $item) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                @endif
                
                <a href="{{ route('vault.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8 mx-auto">
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

            <!-- Información del Item -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-semibold text-body-secondary">Tipo:</div>
                        <div class="col-sm-9">
                            <span class="badge bg-secondary">
                                @if($item->type === 'auth')
                                    <i class="bi bi-key"></i> Autenticación
                                @elseif($item->type === 'note')
                                    <i class="bi bi-sticky"></i> Nota
                                @elseif($item->type === 'card')
                                    <i class="bi bi-credit-card"></i> Tarjeta
                                @elseif($item->type === 'api_key')
                                    <i class="bi bi-code"></i> API Key
                                @elseif($item->type === 'ssh_key')
                                    <i class="bi bi-terminal"></i> SSH Key
                                @elseif($item->type === 'env_file')
                                    <i class="bi bi-file-earmark-code"></i> Archivo .env
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3 fw-semibold text-body-secondary">Estado:</div>
                        <div class="col-sm-9">
                            @if($item->status === 'active')
                                <span class="badge bg-success">Activo</span>
                            @elseif($item->status === 'archived')
                                <span class="badge bg-secondary">Archivado</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3 fw-semibold text-body-secondary">Creado:</div>
                        <div class="col-sm-9">{{ $item->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3 fw-semibold text-body-secondary">Última actualización:</div>
                        <div class="col-sm-9">{{ $item->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Detalles del Secreto (visible para quien tenga acceso: owner, admin, edit o view) -->
            @if($item->secret && isset($secretData))
                @php
                    $hasTotp = isset($secretData['totp_secret']) && !empty($secretData['totp_secret']);
                @endphp

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-body border-0">
                        <h5 class="mb-0 fw-light">Detalles del Secreto</h5>
                    </div>
                    <div class="card-body p-4">
                        @if($item->type === 'auth')
                            @if(isset($secretData['username']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Usuario/Nombre de Usuario</label>
                                    <div class="p-2 bg-body-secondary rounded">{{ $secretData['username'] }}</div>
                                </div>
                            @endif

                            @if(isset($secretData['password']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="passwordDisplay" 
                                               value="{{ $secretData['password'] }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay()" title="Mostrar/ocultar">
                                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('passwordDisplay', event)" title="Copiar al portapapeles">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(isset($secretData['uri']) && is_array($secretData['uri']) && count($secretData['uri']) > 0)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">URIs</label>
                                    <div>
                                        @foreach($secretData['uri'] as $uri)
                                            <div class="mb-1">
                                                <a href="{{ $uri }}" target="_blank" class="text-decoration-none">
                                                    {{ $uri }}
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(isset($totpCode) && $totpCode)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check"></i>
                                        Código TOTP (2FA)
                                        <span class="badge bg-primary ms-auto" id="totpRemainingTime">{{ $totpRemainingTime }}s</span>
                                    </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-3 bg-body-secondary rounded border" style="font-family: 'Courier New', monospace; font-size: 1.5rem; font-weight: bold; letter-spacing: 0.2em; min-width: 120px; text-align: center;" id="totpCode">
                                            {{ $totpCode }}
                                        </div>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyTotpCode()" title="Copiar código">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        @if(isset($totpUri))
                                            <button class="btn btn-outline-primary" type="button" onclick="showTotpQr()" title="Mostrar QR para escanear">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <small class="text-body-secondary d-block mt-2">
                                        <i class="bi bi-info-circle"></i> 
                                        Este código se actualiza automáticamente cada 30 segundos. 
                                        Compatible con Google Authenticator, Microsoft Authenticator, etc.
                                    </small>
                                    
                                    @if(isset($totpUri))
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
                                                            <input type="text" class="form-control font-monospace" id="totpUriDisplay" value="{{ $totpUri }}" readonly>
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
                                    @endif
                                </div>
                            @endif

                        @elseif($item->type === 'card')
                            @if(isset($secretData['cardholder_name']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Nombre del Titular</label>
                                    <div class="p-2 bg-body-secondary rounded">{{ $secretData['cardholder_name'] }}</div>
                                </div>
                            @endif

                            @if(isset($secretData['card_number']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Número de Tarjeta</label>
                                    <div class="p-2 bg-body-secondary rounded font-monospace">{{ $secretData['card_number'] }}</div>
                                </div>
                            @endif

                            <div class="row">
                                @if(isset($secretData['brand']))
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-body-secondary small">Marca</label>
                                        <div class="p-2 bg-body-secondary rounded">{{ $secretData['brand'] }}</div>
                                    </div>
                                @endif

                                @if(isset($secretData['exp_month']) || isset($secretData['exp_year']))
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-body-secondary small">Fecha de Expiración</label>
                                        <div class="p-2 bg-body-secondary rounded">
                                            @if(isset($secretData['exp_month']) && isset($secretData['exp_year']))
                                                {{ str_pad($secretData['exp_month'], 2, '0', STR_PAD_LEFT) }}/{{ $secretData['exp_year'] }}
                                            @elseif(isset($secretData['exp_month']))
                                                {{ str_pad($secretData['exp_month'], 2, '0', STR_PAD_LEFT) }}/--
                                            @elseif(isset($secretData['exp_year']))
                                                --/{{ $secretData['exp_year'] }}
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if(isset($secretData['security_code']))
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold text-body-secondary small">Código de Seguridad (CVV)</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="cvvDisplay" 
                                                   value="{{ $secretData['security_code'] }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay('cvvDisplay', 'cvvToggleIcon')" title="Mostrar/ocultar">
                                                <i class="bi bi-eye" id="cvvToggleIcon"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('cvvDisplay', event)" title="Copiar al portapapeles">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif($item->type === 'api_key')
                            @if(isset($secretData['api_key']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">API Key</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control font-monospace" id="apiKeyDisplay" 
                                               value="{{ $secretData['api_key'] }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay('apiKeyDisplay', 'apiKeyToggleIcon')" title="Mostrar/ocultar">
                                            <i class="bi bi-eye" id="apiKeyToggleIcon"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('apiKeyDisplay', event)" title="Copiar al portapapeles">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(isset($secretData['host']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Host/URL</label>
                                    <div>
                                        <a href="{{ $secretData['host'] }}" target="_blank" class="text-decoration-none">
                                            {{ $secretData['host'] }}
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif

                        @elseif($item->type === 'ssh_key')
                            @if(isset($secretData['public_key']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Clave Pública</label>
                                    <div class="p-3 bg-body-secondary rounded font-monospace" style="white-space: pre-wrap; font-size: 0.875rem;">{{ $secretData['public_key'] }}</div>
                                </div>
                            @endif

                            @if(isset($secretData['private_key']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Clave Privada</label>
                                    <div class="input-group">
                                        <textarea class="form-control font-monospace" id="privateKeyDisplay" 
                                                  rows="6" readonly style="font-size: 0.875rem;">{{ $secretData['private_key'] }}</textarea>
                                        <div class="btn-group-vertical" style="align-self: start; margin-top: 0.375rem;">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay('privateKeyDisplay', 'privateKeyToggleIcon')" title="Mostrar/ocultar">
                                                <i class="bi bi-eye" id="privateKeyToggleIcon"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('privateKeyDisplay', event)" title="Copiar al portapapeles">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($secretData['passphrase']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Frase de Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="passphraseDisplay" 
                                               value="{{ $secretData['passphrase'] }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordDisplay('passphraseDisplay', 'passphraseToggleIcon')" title="Mostrar/ocultar">
                                            <i class="bi bi-eye" id="passphraseToggleIcon"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('passphraseDisplay', event)" title="Copiar al portapapeles">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(isset($secretData['host']))
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-body-secondary small">Host</label>
                                    <div class="p-2 bg-body-secondary rounded font-monospace">{{ $secretData['host'] }}</div>
                                </div>
                            @endif

                        @elseif($item->type === 'env_file')
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-body-secondary small d-flex justify-content-between align-items-center">
                                    Contenido
                                    @if(!empty($envLines))
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyEnvContent()" title="Copiar todo">
                                            <i class="bi bi-clipboard"></i> Copiar
                                        </button>
                                    @endif
                                </label>
                                @if(empty($envLines))
                                    <div class="text-center text-body-secondary py-4">
                                        <i class="bi bi-file-earmark-code" style="font-size: 3rem;"></i>
                                        <p class="mt-3 mb-0">No hay contenido en este archivo.</p>
                                    </div>
                                @else
                                    <div class="p-3 bg-body-secondary rounded font-monospace" style="white-space: pre-wrap; font-size: 0.85rem; max-height: 400px; overflow-y: auto;" id="envContentDisplay">@foreach($envLines as $line){{ $line['content'] }}{{ !$loop->last ? "\n" : '' }}@endforeach</div>
                                @endif
                            </div>

                        @elseif($item->type === 'note')
                            {{-- Para notas, solo se muestra el contenido en la sección de notas más abajo --}}
                            @if(empty($secretData['notes']))
                                <div class="text-center text-body-secondary py-4">
                                    <i class="bi bi-sticky" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">Esta nota no tiene contenido aún.</p>
                                </div>
                            @endif
                        @endif

                        @if(isset($secretData['notes']) && !empty($secretData['notes']))
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-body-secondary small">Notas</label>
                                <div class="p-3 bg-body-secondary rounded" style="white-space: pre-wrap;">{{ $secretData['notes'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Compartición (solo para propietarios o admins) -->
            @if(in_array($userPermission ?? 'view', ['owner', 'admin']))
                @php
                    $userShares = $userShares ?? collect();
                    $groupShares = $groupShares ?? collect();
                    $availableUsers = $availableUsers ?? collect();
                    $availableGroups = $availableGroups ?? collect();
                @endphp

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-light">
                            <i class="bi bi-share text-primary"></i> Compartición
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="bi bi-plus-circle"></i> Compartir
                        </button>
                    </div>
                    <div class="card-body p-4">
                        @if($userShares->isEmpty() && $groupShares->isEmpty())
                            <div class="text-center text-body-secondary py-3">
                                <i class="bi bi-share" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Este item no está compartido con nadie.</p>
                                <small>Haz clic en "Compartir" para compartir con usuarios o grupos.</small>
                            </div>
                        @else
                            <!-- Compartires con usuarios -->
                            @if($userShares->isNotEmpty())
                                <div class="mb-4">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-person"></i> Usuarios
                                    </h6>
                                    <div class="list-group">
                                        @foreach($userShares as $share)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <strong>{{ $share->user->name }}</strong>
                                                        <span class="badge bg-{{ $share->permission === 'admin' ? 'danger' : ($share->permission === 'edit' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst($share->permission) }}
                                                        </span>
                                                    </div>
                                                    <small class="text-body-secondary d-block">{{ $share->user->email }}</small>
                                                    @if($share->sharedBy)
                                                        <small class="text-body-secondary d-block">
                                                            <i class="bi bi-person-check"></i> Compartido por {{ $share->sharedBy->name }} el {{ $share->created_at->format('d/m/Y') }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="openEditPermissionModal('user', {{ $share->id }}, '{{ $share->permission }}', '{{ $share->user->name }}')" 
                                                            title="Editar permiso">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form id="form-revoke-user-{{ $share->id }}" method="POST" action="{{ route('vault.share.revoke', $item) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="share_type" value="user">
                                                        <input type="hidden" name="share_id" value="{{ $share->id }}">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Revocar acceso" data-bs-toggle="modal" data-bs-target="#modal-revoke-user-{{ $share->id }}">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-revoke-user-{{ $share->id }}"
                                                        form-id="form-revoke-user-{{ $share->id }}"
                                                        title="Revocar acceso"
                                                        :message="'¿Estás seguro de que deseas revocar el acceso de ' . e($share->user->name) . '?'"
                                                        confirm-text="Revocar"
                                                        confirm-class="btn-danger"
                                                    />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Compartires con grupos -->
                            @if($groupShares->isNotEmpty())
                                <div>
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-people"></i> Grupos
                                    </h6>
                                    <div class="list-group">
                                        @foreach($groupShares as $share)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <strong>{{ $share->group->name }}</strong>
                                                        <span class="badge bg-{{ $share->permission === 'admin' ? 'danger' : ($share->permission === 'edit' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst($share->permission) }}
                                                        </span>
                                                    </div>
                                                    @if($share->group->description)
                                                        <small class="text-body-secondary d-block">{{ $share->group->description }}</small>
                                                    @endif
                                                    @if($share->sharedBy)
                                                        <small class="text-body-secondary d-block">
                                                            <i class="bi bi-person-check"></i> Compartido por {{ $share->sharedBy->name }} el {{ $share->created_at->format('d/m/Y') }}
                                                        </small>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="openEditPermissionModal('group', {{ $share->id }}, '{{ $share->permission }}', '{{ $share->group->name }}')" 
                                                            title="Editar permiso">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form id="form-revoke-group-{{ $share->id }}" method="POST" action="{{ route('vault.share.revoke', $item) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="share_type" value="group">
                                                        <input type="hidden" name="share_id" value="{{ $share->id }}">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Revocar acceso" data-bs-toggle="modal" data-bs-target="#modal-revoke-group-{{ $share->id }}">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </form>
                                                    <x-confirm-modal
                                                        id="modal-revoke-group-{{ $share->id }}"
                                                        form-id="form-revoke-group-{{ $share->id }}"
                                                        title="Revocar acceso"
                                                        :message="'¿Estás seguro de que deseas revocar el acceso del grupo ' . e($share->group->name) . '?'"
                                                        confirm-text="Revocar"
                                                        confirm-class="btn-danger"
                                                    />
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Modal para compartir -->
                <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow-sm">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-light" id="shareModalLabel">
                                    <i class="bi bi-share text-primary"></i> Compartir Item
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('vault.share', $item) }}" id="shareForm">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="share_type" class="form-label fw-semibold">Tipo de compartir <span class="text-danger">*</span></label>
                                        <select class="form-select" id="share_type" name="share_type" required onchange="toggleShareType()">
                                            <option value="">Selecciona un tipo</option>
                                            <option value="user">Usuario</option>
                                            <option value="group">Grupo</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="userSelectContainer" style="display: none;">
                                        <label for="user_id" class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Selecciona un usuario</option>
                                            @foreach($availableUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3" id="groupSelectContainer" style="display: none;">
                                        <label for="group_id" class="form-label fw-semibold">Grupo <span class="text-danger">*</span></label>
                                        <select class="form-select" id="group_id" name="group_id">
                                            <option value="">Selecciona un grupo</option>
                                            @foreach($availableGroups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="permission" class="form-label fw-semibold">Permiso <span class="text-danger">*</span></label>
                                        <select class="form-select" id="permission" name="permission" required>
                                            <option value="view">Ver (solo lectura)</option>
                                            <option value="edit">Editar</option>
                                            <option value="admin">Administrar</option>
                                        </select>
                                        <small class="text-body-secondary d-block mt-1">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Ver:</strong> Solo puede ver el item. 
                                            <strong>Editar:</strong> Puede ver y editar el item. 
                                            <strong>Administrar:</strong> Puede ver, editar y administrar compartires.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Compartir
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal para editar permiso de compartición -->
                <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-labelledby="editPermissionModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0 shadow-sm">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-light" id="editPermissionModalLabel">
                                    <i class="bi bi-pencil text-primary"></i> Editar Permiso
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('vault.share.update-permission', $item) }}" id="editPermissionForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="share_type" id="edit_share_type">
                                <input type="hidden" name="share_id" id="edit_share_id">
                                <div class="modal-body">
                                    <p class="mb-3">
                                        Cambiar permiso de <strong id="edit_share_name"></strong>:
                                    </p>
                                    <div class="mb-3">
                                        <label for="edit_permission" class="form-label fw-semibold">Nuevo Permiso <span class="text-danger">*</span></label>
                                        <select class="form-select" id="edit_permission" name="permission" required>
                                            <option value="view">Ver (solo lectura)</option>
                                            <option value="edit">Editar</option>
                                            <option value="admin">Administrar</option>
                                        </select>
                                        <small class="text-body-secondary d-block mt-1">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Ver:</strong> Solo puede ver el item. 
                                            <strong>Editar:</strong> Puede ver y editar el item. 
                                            <strong>Administrar:</strong> Puede ver, editar y administrar compartires.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Actualizar Permiso
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Acciones -->
            @php
                $userPermission = $userPermission ?? ($item->owner_user_id === Auth::id() ? 'owner' : 'view');
            @endphp
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-light">Acciones</h6>
                            <p class="text-body-secondary small mb-0">
                                @if($userPermission === 'owner')
                                    Eres el propietario de este item
                                @elseif($userPermission === 'admin')
                                    Tienes permisos de administrador sobre este item
                                @elseif($userPermission === 'edit')
                                    Tienes permisos de edición sobre este item
                                @else
                                    Tienes permisos de solo lectura sobre este item
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($userPermission === 'owner')
                                <form id="form-destroy-vault-{{ $item->id }}" method="POST" action="{{ route('vault.destroy', $item) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-destroy-vault-{{ $item->id }}">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                                <x-confirm-modal
                                    id="modal-destroy-vault-{{ $item->id }}"
                                    form-id="form-destroy-vault-{{ $item->id }}"
                                    title="Eliminar item"
                                    message="¿Estás seguro de que deseas eliminar este item?"
                                    confirm-text="Eliminar"
                                    confirm-class="btn-danger"
                                />
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Función para mostrar/ocultar campos según el tipo de compartir
        function toggleShareType() {
            const shareType = document.getElementById('share_type').value;
            const userContainer = document.getElementById('userSelectContainer');
            const groupContainer = document.getElementById('groupSelectContainer');
            const userId = document.getElementById('user_id');
            const groupId = document.getElementById('group_id');

            if (shareType === 'user') {
                userContainer.style.display = 'block';
                groupContainer.style.display = 'none';
                userId.setAttribute('required', 'required');
                groupId.removeAttribute('required');
                groupId.value = '';
            } else if (shareType === 'group') {
                userContainer.style.display = 'none';
                groupContainer.style.display = 'block';
                groupId.setAttribute('required', 'required');
                userId.removeAttribute('required');
                userId.value = '';
            } else {
                userContainer.style.display = 'none';
                groupContainer.style.display = 'none';
                userId.removeAttribute('required');
                groupId.removeAttribute('required');
                userId.value = '';
                groupId.value = '';
            }
        }

        // Limpiar formulario cuando se cierra el modal
        document.getElementById('shareModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('shareForm').reset();
            toggleShareType();
        });

        function togglePasswordDisplay(inputId = 'passwordDisplay', iconId = 'passwordToggleIcon') {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (!passwordInput || !toggleIcon) return;
            
            if (passwordInput.tagName === 'TEXTAREA') {
                // Para textareas, usar webkitTextSecurity
                const isHidden = passwordInput.style.webkitTextSecurity === 'disc';
                passwordInput.style.webkitTextSecurity = isHidden ? 'none' : 'disc';
            } else {
                // Para inputs
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                } else {
                    passwordInput.type = 'password';
                }
            }
            
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        }

        // Funcionalidad TOTP
        @if(isset($totpCode) && $totpCode && isset($secretData['totp_secret']))
            (function() {
                const totpSecret = @json($secretData['totp_secret']);
                let totpUpdateInterval;
                let countdownInterval;
                let currentRemaining = {{ $totpRemainingTime ?? 30 }};

                // Función para actualizar el código TOTP
                async function updateTotpCode() {
                    try {
                        const response = await window.axios.post('{{ route('vault.totp.generate') }}', {
                            secret: totpSecret
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.data.code) {
                            document.getElementById('totpCode').textContent = response.data.code;
                            currentRemaining = response.data.remaining_time || 30;
                            document.getElementById('totpRemainingTime').textContent = currentRemaining + 's';
                        }
                    } catch (error) {
                        console.error('Error al actualizar código TOTP:', error);
                    }
                }

                // Función para actualizar el contador
                function updateCountdown() {
                    const remainingElement = document.getElementById('totpRemainingTime');
                    if (!remainingElement) return;

                    currentRemaining--;

                    if (currentRemaining <= 0) {
                        currentRemaining = 30;
                        updateTotpCode();
                    } else {
                        remainingElement.textContent = currentRemaining + 's';
                    }
                }

                // Iniciar actualización automática cuando el DOM esté listo
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        // Actualizar código cada 30 segundos
                        totpUpdateInterval = setInterval(updateTotpCode, 30000);
                        
                        // Actualizar contador cada segundo
                        countdownInterval = setInterval(updateCountdown, 1000);
                    });
                } else {
                    // Si el DOM ya está listo, iniciar inmediatamente
                    totpUpdateInterval = setInterval(updateTotpCode, 30000);
                    countdownInterval = setInterval(updateCountdown, 1000);
                }

                // Limpiar intervalos al salir de la página
                window.addEventListener('beforeunload', function() {
                    if (totpUpdateInterval) clearInterval(totpUpdateInterval);
                    if (countdownInterval) clearInterval(countdownInterval);
                });
            })();
        @endif

        // Función para copiar el valor de un input/textarea al portapapeles
        function copyInputToClipboard(inputId, ev) {
            const el = document.getElementById(inputId);
            if (!el) return;
            const text = el.value || el.textContent || '';
            navigator.clipboard.writeText(text).then(function() {
                const btn = (ev && ev.target && ev.target.closest('button')) || (typeof event !== 'undefined' && event.target && event.target.closest('button'));
                if (btn) {
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
            }).catch(function(err) {
                console.error('Error al copiar:', err);
            });
        }

        // Función para copiar contenido .env al portapapeles
        function copyEnvContent() {
            const el = document.getElementById('envContentDisplay');
            if (!el) return;
            const text = el.textContent || '';
            navigator.clipboard.writeText(text).then(function() {
                const btn = document.querySelector('[onclick="copyEnvContent()"]');
                if (btn) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check"></i> Copiado';
                    btn.classList.add('btn-success');
                    btn.classList.remove('btn-outline-secondary');
                    setTimeout(function() {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 2000);
                }
            }).catch(function(err) {
                console.error('Error al copiar:', err);
            });
        }

        // Función para copiar código TOTP
        function copyTotpCode() {
            const codeElement = document.getElementById('totpCode');
            if (codeElement) {
                const code = codeElement.textContent.trim();
                navigator.clipboard.writeText(code).then(function() {
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
                }).catch(function(err) {
                    console.error('Error al copiar:', err);
                });
            }
        }

        // Función para mostrar QR Code
        @if(isset($totpUri))
            function showTotpQr() {
                const modal = new bootstrap.Modal(document.getElementById('totpQrModal'));
                modal.show();
                
                // Generar QR cuando se muestre el modal usando API online
                const canvas = document.getElementById('totpQrCanvas');
                const totpUri = @json($totpUri);
                
                if (canvas && !canvas.dataset.generated) {
                    // Usar API de QR code online (simple y sin dependencias)
                    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=' + encodeURIComponent(totpUri);
                    const img = document.createElement('img');
                    img.src = qrUrl;
                    img.alt = 'QR Code TOTP';
                    img.className = 'img-fluid';
                    img.onload = function() {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0, 256, 256);
                        canvas.dataset.generated = 'true';
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
        @endif

        // Función para abrir el modal de edición de permiso
        function openEditPermissionModal(shareType, shareId, currentPermission, shareName) {
            document.getElementById('edit_share_type').value = shareType;
            document.getElementById('edit_share_id').value = shareId;
            document.getElementById('edit_permission').value = currentPermission;
            document.getElementById('edit_share_name').textContent = shareName;
            
            const modal = new bootstrap.Modal(document.getElementById('editPermissionModal'));
            modal.show();
        }
    </script>
    @endpush
</x-app-layout>
