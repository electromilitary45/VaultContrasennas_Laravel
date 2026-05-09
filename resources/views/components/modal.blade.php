@props([
    'name',
    'show' => false,
    'maxWidth' => 'md'
])

@php
$modalId = 'modal-' . $name;
$maxWidthClass = match($maxWidth) {
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    default => '',
};
@endphp

<!-- Bootstrap Modal -->
<div 
    class="modal fade" 
    id="{{ $modalId }}" 
    tabindex="-1" 
    aria-labelledby="{{ $modalId }}Label" 
    aria-hidden="true"
    x-data="{ show: @js($show) }"
    x-init="$watch('show', value => {
        const modalEl = document.getElementById('{{ $modalId }}');
        if (value) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
>
    <div class="modal-dialog {{ $maxWidthClass }}">
        <div class="modal-content border-0 shadow-sm">
            {{ $slot }}
        </div>
    </div>
</div>
