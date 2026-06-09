<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-900">
            Login Admin
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Masuk untuk mengelola data perusahaan.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username"
                class="block mt-1 w-full"
                type="text"
                name="username"
                :value="old('username')"
                required
                autofocus
                autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me"
                    type="checkbox"
                    class="rounded border-gray-300 text-gray-900 shadow-sm focus:ring-gray-900"
                    name="remember">

                <span class="ms-2 text-sm text-gray-600">
                    Ingat saya
                </span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('register') }}"
                class="text-sm text-gray-600 hover:text-gray-900 underline">
                Daftar akun baru
            </a>

            <x-primary-button class="ms-3">
                Login
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>