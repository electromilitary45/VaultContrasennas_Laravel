@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success border-0 shadow-sm']) }}>
        {{ $status }}
    </div>
@endif
