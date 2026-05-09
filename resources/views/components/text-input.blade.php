@props(['disabled' => false, 'type' => 'text'])

@php
    // Si ya tiene clase form-control, no agregar otra vez
    $classes = $attributes->get('class', '');
    $hasFormControl = str_contains($classes, 'form-control');
    // Usar border en lugar de border-light para mejor compatibilidad con modo oscuro
    $baseClass = $hasFormControl ? '' : 'form-control';
@endphp

<input 
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $baseClass]) }}
>
