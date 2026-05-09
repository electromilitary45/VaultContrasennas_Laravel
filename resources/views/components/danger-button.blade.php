<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-outline-danger']) }}>
    {{ $slot }}
</button>
