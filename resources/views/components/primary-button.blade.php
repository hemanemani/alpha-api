<button {{ $attributes->merge(['type' => 'submit', 'class' => 'login-submit w-100 px-4 py-2 border border-transparent rounded-md font-semibold text-white transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
