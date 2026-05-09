<section>
    <header class="mb-4">
        <h3 class="h5 fw-light mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-person-circle text-primary"></i>
            <span>{{ __('Información del Perfil') }}</span>
        </h3>
        <p class="text-body-secondary small mb-0">
            {{ __("Actualiza la información de tu perfil y dirección de correo electrónico.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('Nombre')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-person"></i>
                </span>
                <x-text-input 
                    id="name" 
                    name="name" 
                    type="text" 
                    :value="old('name', $user->name)" 
                    required 
                    autofocus 
                    autocomplete="name"
                    class="form-control {{ $errors->get('name') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">
                    <i class="bi bi-envelope"></i>
                </span>
                <x-text-input 
                    id="email" 
                    name="email" 
                    type="email" 
                    :value="old('email', $user->email)" 
                    required 
                    autocomplete="username"
                    class="form-control {{ $errors->get('email') ? 'is-invalid' : '' }}"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div class="flex-grow-1">
                            <p class="small mb-1">
                                {{ __('Tu dirección de correo electrónico no está verificada.') }}
                            </p>
                            <button form="send-verification" class="btn btn-link p-0 text-decoration-none">
                                {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                            </button>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success border-0 shadow-sm mt-2 d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle"></i>
                <span>{{ __('Guardar') }}</span>
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <div class="d-flex align-items-center gap-2 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span class="small">{{ __('Guardado.') }}</span>
                </div>
            @endif
        </div>
    </form>
</section>
