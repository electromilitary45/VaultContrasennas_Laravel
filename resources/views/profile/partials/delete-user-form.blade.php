<section>
    <header class="mb-4">
        <h3 class="h5 fw-light mb-2 text-danger d-flex align-items-center gap-2">
            <i class="bi bi-trash3-fill"></i>
            <span>{{ __('Eliminar Cuenta') }}</span>
        </h3>
        <p class="text-body-secondary small mb-0">
            {{ __('Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.') }}
        </p>
    </header>

    <button
        type="button"
        class="btn btn-outline-danger d-flex align-items-center gap-2"
        data-bs-toggle="modal"
        data-bs-target="#confirm-user-deletion-modal"
    >
        <i class="bi bi-trash3"></i>
        <span>{{ __('Eliminar Cuenta') }}</span>
    </button>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirm-user-deletion-modal" tabindex="-1" aria-labelledby="confirm-user-deletion-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-sm">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title h5 fw-light d-flex align-items-center gap-2" id="confirm-user-deletion-modalLabel">
                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                            <span>{{ __('¿Estás seguro de que deseas eliminar tu cuenta?') }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-body-secondary small mb-4">
                            {{ __('Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.') }}
                        </p>

                        <div class="mb-3">
                            <x-input-label for="password" :value="__('Contraseña')" />
                            <div class="input-group">
                                <span class="input-group-text bg-body-secondary">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <x-text-input
                                    id="password"
                                    name="password"
                                    type="password"
                                    placeholder="{{ __('Contraseña') }}"
                                    class="form-control {{ $errors->userDeletion->get('password') ? 'is-invalid' : '' }}"
                                />
                            </div>
                            <x-input-error :messages="$errors->userDeletion->get('password')" />
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i>
                            <span>{{ __('Cancelar') }}</span>
                        </button>
                        <button type="submit" class="btn btn-outline-danger d-flex align-items-center gap-2">
                            <i class="bi bi-trash3"></i>
                            <span>{{ __('Eliminar Cuenta') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
