<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Berkat Jaya Nusantara</title>

    {{-- Favicon / icon tab browser --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo-bjn.png') }}?v=bjn-20260620">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/logo-bjn.png') }}?v=bjn-20260620">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo-bjn.png') }}?v=bjn-20260620">

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        {{-- Navbar --}}
        @include('layouts.navigation')

        {{-- Page Heading --}}
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        {{-- Page Content --}}
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
    
    <script>
        // Mencegah submit form otomatis dengan tombol Enter pada form transaksi
        // (Penjualan, Pembelian, Historis) agar tidak terjadi human error.
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                const target = event.target;
                
                // Tetap izinkan enter jika sedang mengetik di textarea
                if (target.tagName === 'TEXTAREA') {
                    return;
                }
                
                // Cegah default action jika berada di dalam form transaksi
                const form = target.closest('#formPenjualan, #formPembelian, #formPembelianHistoris');
                if (form) {
                    event.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>

</html>