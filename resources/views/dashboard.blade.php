<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan stok, pembelian, penjualan, invoice historis, piutang, stock opname, dan laporan.
                </p>
            </div>

            <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg shadow-sm border">
                {{ now('Asia/Jakarta')->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </x-slot>

    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277';
    $totalPotensiMargin = ($totalEstimasiNilaiJual ?? 0) - ($totalNilaiStok ?? 0);
    $maxGrafik = max($grafikPenjualan7Hari->max('total') ?? 0, 1);
    @endphp

    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div>
                        <p class="text-sm font-semibold text-blue-700">
                            {{ $namaPerusahaan }}
                        </p>

                        <h1 class="text-2xl font-bold text-gray-900 mt-1">
                            Selamat datang, {{ Auth::user()->nama_user ?? 'Admin' }}
                        </h1>

                        <p class="text-sm text-gray-500 mt-2 max-w-3xl">
                            {{ $alamatPerusahaan }} · Telp: {{ $teleponPerusahaan }}
                        </p>

                        <p class="text-sm text-gray-500 mt-1 max-w-3xl">
                            Pantau aktivitas utama perusahaan tanpa menghilangkan konteks sistem: transaksi berjalan mempengaruhi stok, invoice historis masuk laporan tetapi tidak mengubah stok saat ini.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                        <a href="{{ route('pembelian.create') }}"
                            class="px-4 py-3 rounded-xl bg-blue-600 text-white text-sm font-semibold text-center hover:bg-blue-700 transition">
                            + Pembelian
                        </a>

                        <a href="{{ route('penjualan.create') }}"
                            class="px-4 py-3 rounded-xl bg-gray-900 text-white text-sm font-semibold text-center hover:bg-gray-800 transition">
                            + Penjualan
                        </a>

                        <a href="{{ route('stock-opname.create') }}"
                            class="px-4 py-3 rounded-xl bg-yellow-500 text-white text-sm font-semibold text-center hover:bg-yellow-600 transition">
                            Stock Opname
                        </a>

                        <a href="{{ route('invoice-historis.index') }}"
                            class="px-4 py-3 rounded-xl bg-purple-600 text-white text-sm font-semibold text-center hover:bg-purple-700 transition">
                            Invoice History
                        </a>

                        <a href="{{ route('laporan.penjualan') }}"
                            class="px-4 py-3 rounded-xl bg-white border text-gray-700 text-sm font-semibold text-center hover:bg-gray-50 transition">
                            Laporan
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Barang</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalBarang ?? 0, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ number_format($totalBarangAktif ?? 0, 0, ',', '.') }} · Nonaktif: {{ number_format($totalBarangNonaktif ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalStok ?? 0, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('laporan.stokBarang') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Kosong: {{ $totalBarangKosong ?? 0 }} · Rendah: {{ $totalBarangStokRendah ?? 0 }} · Batas: {{ $batasStokRendah ?? 5 }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Customer</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalCustomer ?? 0, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ number_format($totalCustomerAktif ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Supplier</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ number_format($totalSupplier ?? 0, 0, ',', '.') }}
                        </h3>
                        <a href="{{ route('suppliers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ number_format($totalSupplierAktif ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Pembelian Hari Ini</h3>
                            <p class="text-sm text-gray-500">Mengikuti fitur DO, surat jalan, status penerimaan, dan pengaruh stok.</p>
                        </div>
                        <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Total Nilai</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                Rp {{ number_format($pembelianHariIni ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ number_format($jumlahPembelianHariIni ?? 0, 0, ',', '.') }} transaksi
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Barang Dipesan</p>
                            <p class="text-xl font-bold text-blue-700 mt-1">
                                {{ number_format($barangDipesanHariIni ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Berdasarkan detail pembelian.</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Barang Diterima</p>
                            <p class="text-xl font-bold text-green-700 mt-1">
                                {{ number_format($barangDiterimaHariIni ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Yang benar-benar menambah stok.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Penjualan Hari Ini</h3>
                            <p class="text-sm text-gray-500">Memisahkan tunai, kredit, dan jumlah barang terjual.</p>
                        </div>
                        <a href="{{ route('laporan.penjualan') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Total Nilai</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                Rp {{ number_format($penjualanHariIni ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ number_format($jumlahPenjualanHariIni ?? 0, 0, ',', '.') }} invoice
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Tunai</p>
                            <p class="text-lg font-bold text-green-700 mt-1">
                                Rp {{ number_format($penjualanTunaiHariIni ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Kredit</p>
                            <p class="text-lg font-bold text-yellow-700 mt-1">
                                Rp {{ number_format($penjualanKreditHariIni ?? 0, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Barang Terjual</p>
                            <p class="text-lg font-bold text-blue-700 mt-1">
                                {{ number_format($barangTerjualHariIni ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Piutang Belum Lunas</p>
                    <h3 class="text-xl font-bold text-red-600 mt-2">
                        Rp {{ number_format($totalPiutangBelumLunas ?? 0, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ number_format($jumlahPiutangBelumLunas ?? 0, 0, ',', '.') }} data belum lunas.
                    </p>
                    <a href="{{ route('laporan.piutang') }}" class="text-sm text-red-600 hover:underline mt-3 inline-block">
                        Laporan piutang
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Lewat Jatuh Tempo</p>
                    <h3 class="text-xl font-bold text-red-700 mt-2">
                        {{ number_format($jumlahPiutangLewatTempo ?? 0, 0, ',', '.') }} invoice
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Sisa: Rp {{ number_format($sisaPiutangLewatTempo ?? 0, 0, ',', '.') }}
                    </p>
                    <a href="{{ route('piutang.index') }}" class="text-sm text-red-600 hover:underline mt-3 inline-block">
                        Kelola piutang
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Invoice Penjualan</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        {{ number_format(($invoiceSistemBerjalan ?? 0) + ($invoiceHistoris ?? 0), 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Sistem: {{ $invoiceSistemBerjalan ?? 0 }} · Historis: {{ $invoiceHistoris ?? 0 }} · Hari ini: {{ $invoiceHariIni ?? 0 }}
                    </p>
                    <a href="{{ route('invoice-historis.index') }}" class="text-sm text-purple-600 hover:underline mt-3 inline-block">
                        Invoice history
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Riwayat Stok Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        +{{ number_format($stokMasukHariIni ?? 0, 0, ',', '.') }} / -{{ number_format($stokKeluarHariIni ?? 0, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Penyesuaian: {{ $penyesuaianStokHariIni ?? 0 }} · Opname: {{ $stockOpnameHariIni ?? 0 }}
                    </p>
                    <a href="{{ route('laporan.riwayatStok') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                        Laporan stok
                    </a>
                </div>
            </div>

            <div class="bg-white border rounded-2xl shadow-sm p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div>
                        <h3 class="font-bold text-gray-900">
                            Konsistensi Fitur Sistem
                        </h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-3xl">
                            Dashboard ini menampilkan konteks fitur terbaru: invoice historis, stok opname, pembelian sebagian/belum dikirim, pajak tampil saja, piutang kredit, serta perhitungan harga normal dan isi kemasan.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="rounded-xl bg-purple-50 px-4 py-3">
                            <p class="text-gray-500">Pembelian Historis</p>
                            <p class="font-bold text-purple-700">{{ $pembelianHistoris ?? 0 }}</p>
                        </div>

                        <div class="rounded-xl bg-blue-50 px-4 py-3">
                            <p class="text-gray-500">Pembelian Sistem</p>
                            <p class="font-bold text-blue-700">{{ $pembelianSistemBerjalan ?? 0 }}</p>
                        </div>

                        <div class="rounded-xl bg-orange-50 px-4 py-3">
                            <p class="text-gray-500">Tidak Ubah Stok</p>
                            <p class="font-bold text-orange-700">{{ $pembelianTidakMempengaruhiStok ?? 0 }}</p>
                        </div>

                        <div class="rounded-xl bg-green-50 px-4 py-3">
                            <p class="text-gray-500">Ubah Stok</p>
                            <p class="font-bold text-green-700">{{ $pembelianMempengaruhiStok ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white border rounded-2xl shadow-sm p-5 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Penjualan 7 Hari Terakhir
                            </h3>
                            <p class="text-sm text-gray-500">
                                Berdasarkan total akhir invoice penjualan.
                            </p>
                        </div>

                        <a href="{{ route('laporan.penjualan') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse ($grafikPenjualan7Hari as $item)
                        @php
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
                                max="{{ $maxGrafik }}"
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

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Stok Terendah
                            </h3>
                            <p class="text-sm text-gray-500">
                                Barang aktif dengan stok paling kecil.
                            </p>
                        </div>

                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($stokTerendah as $item)
                        @php
                        $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
                        @endphp
                        <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                            <div>
                                <p class="font-semibold text-sm text-gray-900">
                                    {{ $item->nama_barang }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->kode_barang }} · {{ strtoupper($item->satuan ?? '-') }} · {{ $tipePerhitungan === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-bold {{ $item->stok_saat_ini <= $batasStokRendah ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ number_format($item->stok_saat_ini, 0, ',', '.') }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Status Penerimaan Pembelian
                            </h3>
                            <p class="text-sm text-gray-500">
                                Kontrol barang dipesan dan diterima.
                            </p>
                        </div>

                        <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-600">Lengkap</span>
                            <strong class="text-green-700">{{ number_format($pembelianLengkap ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-600">Sebagian</span>
                            <strong class="text-yellow-700">{{ number_format($pembelianSebagian ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Belum Dikirim</span>
                            <strong class="text-red-700">{{ number_format($pembelianBelumDikirim ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Perhitungan Harga Barang
                            </h3>
                            <p class="text-sm text-gray-500">
                                Ringkasan normal dan isi kemasan.
                            </p>
                        </div>

                        <a href="{{ route('laporan.stokBarang') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-gray-500">Barang Normal</p>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($totalBarangNormal ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-purple-50 p-3">
                            <p class="text-xs text-gray-500">Isi Kemasan</p>
                            <p class="text-xl font-bold text-purple-700">{{ number_format($totalBarangIsiKemasan ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500">
                        Detail penjualan: normal {{ number_format($detailPenjualanNormal ?? 0, 0, ',', '.') }} baris, isi kemasan {{ number_format($detailPenjualanIsiKemasan ?? 0, 0, ',', '.') }} baris.
                    </p>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Nilai Persediaan
                            </h3>
                            <p class="text-sm text-gray-500">
                                Estimasi nilai stok dan potensi jual.
                            </p>
                        </div>

                        <a href="{{ route('laporan.stokBarang') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Estimasi Nilai Stok</p>
                            <p class="font-bold text-gray-900">Rp {{ number_format($totalNilaiStok ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Estimasi Nilai Jual</p>
                            <p class="font-bold text-green-700">Rp {{ number_format($totalEstimasiNilaiJual ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Estimasi Margin Kotor</p>
                            <p class="font-bold {{ $totalPotensiMargin >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                                Rp {{ number_format($totalPotensiMargin, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Piutang Mendekati Tempo
                            </h3>
                            <p class="text-sm text-gray-500">
                                Piutang belum lunas sampai 7 hari ke depan.
                            </p>
                        </div>

                        <a href="{{ route('piutang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($piutangJatuhTempo as $item)
                        @php
                        $isHistoris = (bool) ($item->penjualan->is_historical ?? false);
                        $lewatTempo = $item->tanggal_jatuh_tempo && $item->tanggal_jatuh_tempo->isPast();
                        @endphp
                        <div class="py-2 border-b last:border-b-0">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <a href="{{ route('piutang.show', $item->id_piutang) }}" class="font-semibold text-sm text-gray-900 hover:underline">
                                        {{ $item->nomor_invoice }}
                                    </a>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->customer->nama_customer ?? '-' }} · {{ $isHistoris ? 'Historis' : 'Sistem' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-red-600 text-sm">
                                        Rp {{ number_format($item->sisa_piutang, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs {{ $lewatTempo ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
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

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-900">
                            Transaksi Terbaru
                        </h3>
                        <p class="text-sm text-gray-500">
                            Ringkasan aktivitas penjualan dan pembelian terakhir.
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
                            @php
                            $isHistoris = (bool) ($item->is_historical ?? false);
                            @endphp
                            <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $item->nomor_invoice }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->customer->nama_customer ?? '-' }} · {{ $isHistoris ? 'Historis' : ucfirst($item->metode_pembayaran ?? '-') }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
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
                            @php
                            $isHistoris = (bool) ($item->is_historical ?? false);
                            $statusPenerimaan = $item->status_penerimaan ?? 'lengkap';
                            $statusText = match ($statusPenerimaan) {
                            'sebagian' => 'Sebagian',
                            'belum_dikirim' => 'Belum Dikirim',
                            default => 'Lengkap',
                            };
                            @endphp
                            <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $item->nomor_pembelian }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->supplier->nama_supplier ?? '-' }} · {{ $isHistoris ? 'Historis' : $statusText }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Barang Isi Kemasan
                            </h3>
                            <p class="text-sm text-gray-500">
                                Contoh barang yang harga jualnya dihitung per isi kemasan.
                            </p>
                        </div>

                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($barangIsiKemasan as $item)
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $item->nama_barang }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    1 {{ strtoupper($item->satuan ?? '-') }} = {{ rtrim(rtrim(number_format((float) ($item->isi_per_satuan ?? 1), 3, ',', '.'), '0'), ',') }} {{ strtoupper($item->satuan_hitung_harga ?? $item->satuan ?? '-') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    Rp {{ number_format($item->harga_jual_default ?? 0, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    / {{ strtoupper($item->satuan_hitung_harga ?? $item->satuan ?? '-') }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada barang isi kemasan.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Riwayat Stok Terbaru
                            </h3>
                            <p class="text-sm text-gray-500">
                                Aktivitas stok dari pembelian, penjualan, dan stock opname.
                            </p>
                        </div>

                        <a href="{{ route('riwayat-stok.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($riwayatStokTerbaru as $item)
                        @php
                        $jenis = $item->jenis_pergerakan ?? '-';
                        $jenisClass = match ($jenis) {
                        'masuk' => 'text-green-700',
                        'keluar' => 'text-red-700',
                        default => 'text-yellow-700',
                        };
                        $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');
                        @endphp
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $item->barang->nama_barang ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->sumber_transaksi ?? '-' }} · {{ $isOpname ? 'Opname' : 'Transaksi' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold {{ $jenisClass }}">
                                    {{ ucfirst($jenis) }} {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada riwayat stok.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>