<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan stok, transaksi, invoice, customer, supplier, dan piutang.
                </p>
            </div>

            <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg shadow-sm border">
                {{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header Summary --}}
            <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div>

                        <h1 class="text-2xl font-bold text-gray-900 mt-1">
                            Selamat datang, {{ Auth::user()->nama_user ?? 'Admin' }}
                        </h1>

                        <p class="text-sm text-gray-500 mt-2 max-w-2xl">
                            Pantau aktivitas utama perusahaan mulai dari stok, pembelian, penjualan, invoice historis, customer, supplier, hingga piutang.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <a href="{{ route('pembelian.create') }}"
                            class="px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-semibold text-center hover:bg-blue-700 transition">
                            + Pembelian
                        </a>

                        <a href="{{ route('penjualan.create') }}"
                            class="px-4 py-3 rounded-xl bg-gray-900 text-white text-sm font-semibold text-center hover:bg-gray-800 transition">
                            + Penjualan
                        </a>

                        <a href="{{ route('customers.create') }}"
                            class="px-4 py-3 rounded-xl bg-white border text-gray-700 text-sm font-semibold text-center hover:bg-gray-50 transition">
                            + Customer
                        </a>

                        <a href="{{ route('invoice-historis.index') }}"
                            class="px-4 py-3 rounded-xl bg-white border text-gray-700 text-sm font-semibold text-center hover:bg-gray-50 transition">
                            Invoice History
                        </a>
                    </div>
                </div>
            </div>

            {{-- Main Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Barang</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalBarang, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Customer</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalCustomer, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Supplier</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalSupplier, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('suppliers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalStok, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('riwayat-stok.index') }}" class="text-sm text-blue-600 hover:underline">
                            Riwayat
                        </a>
                    </div>
                </div>
            </div>

            {{-- Transaction Stats --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Pembelian Hari Ini</p>
                            <h3 class="text-xl font-bold text-gray-900 mt-2">
                                Rp {{ number_format($pembelianHariIni, 0, ',', '.') }}
                            </h3>
                            <a href="{{ route('pembelian.index') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                                Lihat pembelian
                            </a>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                            <h3 class="text-xl font-bold text-gray-900 mt-2">
                                Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}
                            </h3>
                            <a href="{{ route('penjualan.index') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                                Lihat penjualan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Piutang Belum Lunas</p>
                    <h3 class="text-xl font-bold text-red-600 mt-2">
                        Rp {{ number_format($totalPiutangBelumLunas, 0, ',', '.') }}
                    </h3>
                    <a href="{{ route('piutang.index') }}" class="text-sm text-red-600 hover:underline mt-3 inline-block">
                        Kelola piutang
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Invoice Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        {{ number_format($invoiceHariIni, 0, ',', '.') }}
                    </h3>
                    <a href="{{ route('penjualan.index') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                        Lihat invoice
                    </a>
                </div>
            </div>

            {{-- Invoice History Compact --}}
            <div class="bg-white border rounded-2xl shadow-sm p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-gray-900">
                            Invoice Historis / Transaksi Lama
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Input invoice pembelian dan penjualan lama sebelum sistem digitalisasi. Data masuk laporan, tetapi tidak mengubah stok saat ini.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('invoice-historis.index') }}"
                            class="px-4 py-2 rounded-lg border text-sm font-semibold text-gray-700 hover:bg-gray-50 text-center">
                            Lihat History
                        </a>

                        <a href="{{ route('invoice-historis.pembelian.create') }}"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-sm font-semibold text-white hover:bg-purple-700 text-center">
                            + Pembelian Lama
                        </a>

                        <a href="{{ route('invoice-historis.penjualan.create') }}"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 text-center">
                            + Penjualan Lama
                        </a>
                    </div>
                </div>
            </div>

            {{-- Middle Content --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                {{-- Sales Chart --}}
                <div class="bg-white border rounded-2xl shadow-sm p-5 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Penjualan 7 Hari Terakhir
                            </h3>
                            <p class="text-sm text-gray-500">
                                Berdasarkan total akhir penjualan.
                            </p>
                        </div>

                        <a href="{{ route('penjualan.index') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse ($grafikPenjualan7Hari as $item)
                        @php
                        $maxTotal = max($grafikPenjualan7Hari->max('total'), 1);
                        $tanggalGrafik = \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y');
                        $totalGrafik = number_format($item->total, 0, ',', '.');
                        @endphp

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $tanggalGrafik }}</span>
                                <strong class="text-gray-900">Rp {{ $totalGrafik }}</strong>
                            </div>

                            <progress
                                value="{{ $item->total }}"
                                max="{{ $maxTotal }}"
                                class="w-full h-3">
                            </progress>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada data penjualan dalam 7 hari terakhir.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Low Stock --}}
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Stok Terendah
                            </h3>
                            <p class="text-sm text-gray-500">
                                5 barang dengan stok paling kecil.
                            </p>
                        </div>

                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($stokTerendah as $item)
                        <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                            <div>
                                <p class="font-semibold text-sm text-gray-900">
                                    {{ $item->nama_barang }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->kode_barang }} · {{ $item->satuan }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-bold {{ $item->stok_saat_ini <= 5 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $item->stok_saat_ini }}
                                </p>
                                <p class="text-xs text-gray-500">stok</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada data barang.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Bottom Content --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Customer Terbaru --}}
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Customer Terbaru
                            </h3>
                            <p class="text-sm text-gray-500">
                                Data customer terbaru.
                            </p>
                        </div>

                        <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($customerTerbaru as $customer)
                        <div class="py-2 border-b last:border-b-0">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-sm text-gray-900">
                                        {{ $customer->nama_customer }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $customer->kode_customer }} · {{ $customer->nomor_telepon ?? '-' }}
                                    </p>
                                </div>

                                @if ($customer->status_aktif)
                                <span class="h-fit px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                    Aktif
                                </span>
                                @else
                                <span class="h-fit px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                    Nonaktif
                                </span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada data customer.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Piutang Jatuh Tempo --}}
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Piutang Jatuh Tempo
                            </h3>
                            <p class="text-sm text-gray-500">
                                Piutang mendekati tempo.
                            </p>
                        </div>

                        <a href="{{ route('piutang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($piutangJatuhTempo as $item)
                        <div class="py-2 border-b last:border-b-0">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-sm text-gray-900">
                                        {{ $item->nomor_invoice }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->customer->nama_customer ?? '-' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-red-600 text-sm">
                                        Rp {{ number_format($item->sisa_piutang, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_jatuh_tempo ? $item->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Tidak ada piutang mendekati jatuh tempo.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Transaksi Terbaru --}}
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-900">
                            Transaksi Terbaru
                        </h3>
                        <p class="text-sm text-gray-500">
                            Ringkasan aktivitas terakhir.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <p class="font-semibold text-sm text-gray-700">
                                    Penjualan
                                </p>
                                <a href="{{ route('penjualan.index') }}" class="text-xs text-blue-600 hover:underline">
                                    Lihat
                                </a>
                            </div>

                            @forelse ($penjualanTerbaru->take(3) as $item)
                            <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $item->nomor_invoice }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->customer->nama_customer ?? '-' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_penjualan->format('d-m-Y') }}
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">
                                Belum ada penjualan.
                            </p>
                            @endforelse
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <p class="font-semibold text-sm text-gray-700">
                                    Pembelian
                                </p>
                                <a href="{{ route('pembelian.index') }}" class="text-xs text-blue-600 hover:underline">
                                    Lihat
                                </a>
                            </div>

                            @forelse ($pembelianTerbaru->take(3) as $item)
                            <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $item->nomor_pembelian }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->supplier->nama_supplier ?? '-' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_pembelian->format('d-m-Y') }}
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500">
                                Belum ada pembelian.
                            </p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>