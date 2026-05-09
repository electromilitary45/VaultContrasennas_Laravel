@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label small fw-medium']) }}>
    {{ $value ?? $slot }}
</label>
