<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-900">
            Daftar Akun Admin
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Buat akun untuk mengakses sistem Berkat Jaya Nusantara.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="nama_user" :value="__('Nama User')" />

            <x-text-input id="nama_user"
                class="block mt-1 w-full"
                type="text"
                name="nama_user"
                :value="old('nama_user')"
                required
                autofocus
                autocomplete="name" />

            <x-input-error :messages="$errors->get('nama_user')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />

            <x-text-input id="username"
                class="block mt-1 w-full"
                type="text"
                name="username"
                :value="old('username')"
                required
                autocomplete="username" />

            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Opsional')" />

            <x-text-input id="email"
                class="block mt-1 w-full"
                type="email"
                name="email"
                :value="old('email')"
                autocomplete="email" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password"
                class="block mt-1 w-full"
                type="password"
                name="password"
                required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />

            <x-text-input id="password_confirmation"
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-gray-600 hover:text-gray-900 underline"
                href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <x-primary-button class="ms-4">
                Daftar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>