<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-light mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-ticket-perforated text-primary"></i>
                <span>Códigos de invitación</span>
            </h2>
            <form method="POST" action="{{ route('admin.invitation-codes.store') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> Generar código
                </button>
            </form>
        </div>
    </x-slot>

    @if($newCode ?? null)
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-check-circle fs-4"></i>
                <div class="flex-grow-1">
                    <strong>Código generado.</strong> Comparte el código o el enlace; solo podrá usarse una vez.
                    <div class="mt-3">
                        <label class="form-label small mb-1">Código</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" value="{{ $newCode }}" id="inv-code" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('inv-code').value); this.textContent='Copiado';">Copiar</button>
                        </div>
                    </div>
                    @if($newCodeUrl ?? null)
                        <div class="mt-2">
                            <label class="form-label small mb-1">Enlace de registro</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" value="{{ $newCodeUrl }}" id="inv-url" readonly>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('inv-url').value); this.textContent='Copiado';">Copiar</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0">
            <h6 class="mb-0 fw-light">
                <i class="bi bi-list-ul"></i> Códigos ({{ $codes->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($codes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-body-secondary">
                            <tr>
                                <th class="ps-4">Código</th>
                                <th>Estado</th>
                                <th>Creado por</th>
                                <th>Usado por</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($codes as $c)
                                <tr>
                                    <td class="ps-4">
                                        <code class="small">{{ $c->code }}</code>
                                    </td>
                                    <td>
                                        @if($c->used_at)
                                            <span class="badge bg-secondary">Usado</span>
                                        @else
                                            <span class="badge bg-success">Disponible</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($c->createdBy)
                                            {{ $c->createdBy->name }}
                                        @else
                                            <span class="text-body-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($c->usedBy)
                                            {{ $c->usedBy->name }}
                                        @else
                                            <span class="text-body-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-body-secondary">{{ $c->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0">
                    {{ $codes->links() }}
                </div>
            @else
                <div class="text-center text-body-secondary py-5">
                    <i class="bi bi-ticket-perforated" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No hay códigos</p>
                    <form method="POST" action="{{ route('admin.invitation-codes.store') }}" class="mt-3 d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-lg"></i> Generar el primero
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
