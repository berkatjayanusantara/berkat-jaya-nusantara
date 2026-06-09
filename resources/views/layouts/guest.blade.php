<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Berkat Jaya Nusantara') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 bg-gradient-to-br from-slate-100 via-gray-100 to-slate-200">

        <div class="mb-6 text-center">
            <a href="/" class="inline-flex flex-col items-center gap-3">
                <div class="w-20 h-20 rounded-2xl bg-gray-900 flex items-center justify-center shadow-lg">
                    <span class="text-white text-2xl font-bold tracking-widest">
                        BJN
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Berkat Jaya Nusantara
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Sistem Digitalisasi Stok, Pembelian, Penjualan & Piutang
                    </p>
                </div>
            </a>
        </div>

        <div class="w-full sm:max-w-md px-6 py-6 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-100">
            {{ $slot }}
        </div>
    </div>
</body>

</html>