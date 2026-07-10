<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Dashboard
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Ringkasan operasional Berkat Jaya Nusantara: stok, pembelian, penjualan, piutang, stock opname, dan laporan.
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
    $teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';
    $maxGrafik = max($grafikPenjualan7Hari->max('total') ?? 0, 1);

    $formatRupiah = function ($nilai) {
    return 'Rp ' . number_format((float) ($nilai ?? 0), 0, ',', '.');
    };

    $formatAngka = function ($nilai) {
    return number_format((float) ($nilai ?? 0), 0, ',', '.');
    };

    $formatDesimal = function ($nilai, $desimal = 3) {
    return rtrim(rtrim(number_format((float) ($nilai ?? 0), $desimal, ',', '.'), '0'), ',');
    };

    $nomorPenjualanTampil = function ($penjualan) {
    if (!$penjualan) {
    return '-';
    }

    return (bool) ($penjualan->is_historical ?? false) && !empty($penjualan->nomor_dokumen_asli)
    ? $penjualan->nomor_dokumen_asli
    : $penjualan->nomor_invoice;
    };

    $nomorPembelianTampil = function ($pembelian) {
    if (!$pembelian) {
    return '-';
    }

    return (bool) ($pembelian->is_historical ?? false) && !empty($pembelian->nomor_dokumen_asli)
    ? $pembelian->nomor_dokumen_asli
    : $pembelian->nomor_pembelian;
    };

    $statusPenerimaanText = function ($status) {
    return [
    'lengkap' => 'Lengkap',
    'sebagian' => 'Sebagian',
    'belum_dikirim' => 'Belum Dikirim',
    ][$status ?? 'lengkap'] ?? 'Lengkap';
    };

    $normalisasiJenisPpnBarang = function ($item) {
    if (!$item) {
    return 'ppn_normal';
    }

    $jenisPpn = $item->jenis_ppn ?? null;

    if (in_array($jenisPpn, ['non_ppn', 'ppn_normal', 'ppn_dpp_nilai_lain'], true)) {
    return $jenisPpn;
    }

    $kenaPpnLegacy = (bool) ($item->kena_ppn ?? true);

    return $kenaPpnLegacy ? 'ppn_normal' : 'non_ppn';
    };

    $labelJenisPpnBarang = function ($jenisPpn) {
    return match ($jenisPpn) {
    'non_ppn' => 'Non PPN',
    'ppn_normal' => 'PPN Normal',
    'ppn_dpp_nilai_lain' => 'PPN DPP Nilai Lain',
    default => 'PPN Normal',
    };
    };
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
                            Dashboard ini disesuaikan dengan update terbaru: invoice historis penjualan mengikuti format penjualan baru, nomor dokumen asli tetap tampil, transaksi historis tidak mengubah stok, piutang historis tetap terpantau, stok barang mendukung isi kemasan dan klasifikasi PPN terbaru, serta stock opname massal tercatat di riwayat stok.
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

                        <a href="{{ route('stock-opname.index') }}"
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

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Prioritas Stok</p>
                            <h3 class="text-2xl font-bold text-red-700 mt-2">
                                {{ $formatAngka(($totalBarangKosong ?? 0) + ($totalBarangStokRendah ?? 0)) }} barang
                            </h3>
                        </div>
                        <a href="{{ route('laporan.stokBarang', ['kondisi_stok' => 'rendah']) }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        Kosong: {{ $formatAngka($totalBarangKosong ?? 0) }} · Rendah: {{ $formatAngka($totalBarangStokRendah ?? 0) }} · Aman: {{ $formatAngka($totalBarangStokAman ?? 0) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Piutang Belum Lunas</p>
                            <h3 class="text-2xl font-bold text-red-700 mt-2">
                                {{ $formatRupiah($totalPiutangBelumLunas ?? 0) }}
                            </h3>
                        </div>
                        <a href="{{ route('laporan.piutang') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        Data: {{ $formatAngka($jumlahPiutangBelumLunas ?? 0) }} · Lewat tempo: {{ $formatAngka($jumlahPiutangLewatTempo ?? 0) }} · Tertagih: {{ number_format($persentasePiutangTertagih ?? 0, 2, ',', '.') }}%
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Pembelian Belum Selesai</p>
                            <h3 class="text-2xl font-bold text-yellow-700 mt-2">
                                {{ $formatAngka(($pembelianSebagian ?? 0) + ($pembelianBelumDikirim ?? 0)) }} transaksi
                            </h3>
                        </div>
                        <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        Sebagian: {{ $formatAngka($pembelianSebagian ?? 0) }} · Belum dikirim: {{ $formatAngka($pembelianBelumDikirim ?? 0) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-gray-500">Stock Opname Hari Ini</p>
                            <h3 class="text-2xl font-bold text-blue-700 mt-2">
                                {{ $formatAngka($totalNomorStockOpnameHariIni ?? 0) }} sesi
                            </h3>
                        </div>
                        <a href="{{ route('riwayat-stok.index', ['tipe_riwayat' => 'opname']) }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">
                        Baris barang: {{ $formatAngka($totalBarisStockOpnameHariIni ?? 0) }} · Penyesuaian: {{ $formatAngka($penyesuaianStokHariIni ?? 0) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Barang</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $formatAngka($totalBarang ?? 0) }}
                        </h3>
                        <a href="{{ route('barang.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ $formatAngka($totalBarangAktif ?? 0) }} · Nonaktif: {{ $formatAngka($totalBarangNonaktif ?? 0) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $formatAngka($totalStok ?? 0) }}
                        </h3>
                        <a href="{{ route('laporan.stokBarang') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Batas stok rendah: {{ $formatAngka($batasStokRendah ?? 5) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Customer</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $formatAngka($totalCustomer ?? 0) }}
                        </h3>
                        <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ $formatAngka($totalCustomerAktif ?? 0) }} · Nonaktif: {{ $formatAngka($totalCustomerNonaktif ?? 0) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Supplier</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $formatAngka($totalSupplier ?? 0) }}
                        </h3>
                        <a href="{{ route('suppliers.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Aktif: {{ $formatAngka($totalSupplierAktif ?? 0) }} · Nonaktif: {{ $formatAngka($totalSupplierNonaktif ?? 0) }}
                    </p>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Kop Surat / Surat</p>
                    <div class="flex items-end justify-between mt-2">
                        <h3 class="text-2xl font-bold text-gray-900">
                            {{ $formatAngka($totalSuratKeluar ?? 0) }}
                        </h3>
                        <a href="{{ route('kop-surat.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Final: {{ $formatAngka($totalSuratFinal ?? 0) }} · Draft: {{ $formatAngka($totalSuratDraft ?? 0) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Pembelian Hari Ini</h3>
                            <p class="text-sm text-gray-500">Mengikuti DO, surat jalan, status penerimaan, dan pengaruh stok.</p>
                        </div>
                        <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Total Nilai</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">
                                {{ $formatRupiah($pembelianHariIni ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $formatAngka($jumlahPembelianHariIni ?? 0) }} transaksi
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Penyesuaian</p>
                            <p class="text-xl font-bold text-orange-700 mt-1">
                                {{ $formatRupiah(($biayaLainPembelianHariIni ?? 0) - ($potonganDiskonPembelianHariIni ?? 0)) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Biaya: {{ $formatAngka($biayaLainPembelianHariIni ?? 0) }} · Diskon: {{ $formatAngka($potonganDiskonPembelianHariIni ?? 0) }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Barang Dipesan</p>
                            <p class="text-xl font-bold text-blue-700 mt-1">
                                {{ $formatAngka($barangDipesanHariIni ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Dari detail pembelian.</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Barang Diterima</p>
                            <p class="text-xl font-bold text-green-700 mt-1">
                                {{ $formatAngka($barangDiterimaHariIni ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Yang benar-benar diterima.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm lg:col-span-2">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">Penjualan Hari Ini</h3>
                            <p class="text-sm text-gray-500">Memisahkan tunai, kredit, PPN, faktur pajak, invoice sistem, dan invoice historis.</p>
                        </div>
                        <a href="{{ route('laporan.penjualan') }}" class="text-sm text-blue-600 hover:underline">
                            Laporan
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">Total Nilai</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">
                                {{ $formatRupiah($penjualanHariIni ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $formatAngka($jumlahPenjualanHariIni ?? 0) }} invoice
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Tunai</p>
                            <p class="text-lg font-bold text-green-700 mt-1">
                                {{ $formatRupiah($penjualanTunaiHariIni ?? 0) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Kredit</p>
                            <p class="text-lg font-bold text-yellow-700 mt-1">
                                {{ $formatRupiah($penjualanKreditHariIni ?? 0) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">PPN</p>
                            <p class="text-lg font-bold text-blue-700 mt-1">
                                {{ $formatRupiah($ppnPenjualanHariIni ?? 0) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Penyesuaian</p>
                            <p class="text-lg font-bold {{ ($penyesuaianBersihPenjualanHariIni ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' }} mt-1">
                                {{ ($penyesuaianBersihPenjualanHariIni ?? 0) > 0 ? '+' : '' }}{{ $formatRupiah($penyesuaianBersihPenjualanHariIni ?? 0) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                +{{ $formatAngka($penyesuaianTambahPenjualanHariIni ?? 0) }} / -{{ $formatAngka($penyesuaianKurangPenjualanHariIni ?? 0) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Terjual</p>
                            <p class="text-lg font-bold text-purple-700 mt-1">
                                {{ $formatAngka($barangTerjualHariIni ?? 0) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                        Sistem hari ini: {{ $formatRupiah($penjualanSistemHariIni ?? 0) }}
                        ({{ $formatAngka($jumlahPenjualanSistemHariIni ?? 0) }} invoice) ·
                        Historis hari ini: {{ $formatRupiah($penjualanHistorisHariIni ?? 0) }}
                        ({{ $formatAngka($jumlahPenjualanHistorisHariIni ?? 0) }} invoice) ·
                        DPP PPN: {{ $formatRupiah($dppPpnPenjualanHariIni ?? 0) }} ·
                        Faktur pajak: {{ $formatAngka($penjualanButuhFakturPajakHariIni ?? 0) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Invoice Penjualan</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        {{ $formatAngka(($invoiceSistemBerjalan ?? 0) + ($invoiceHistoris ?? 0)) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Sistem: {{ $formatAngka($invoiceSistemBerjalan ?? 0) }} · Historis: {{ $formatAngka($invoiceHistoris ?? 0) }} · Hari ini: {{ $formatAngka($invoiceHariIni ?? 0) }} · Historis hari ini: {{ $formatAngka($invoiceHistorisHariIni ?? 0) }}
                    </p>
                    <a href="{{ route('arsip-invoice.index') }}" class="text-sm text-purple-600 hover:underline mt-3 inline-block">
                        Arsip Invoice
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Pembelian Sistem & Historis</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        {{ $formatAngka(($pembelianSistemBerjalan ?? 0) + ($pembelianHistoris ?? 0)) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Sistem: {{ $formatAngka($pembelianSistemBerjalan ?? 0) }} · Historis: {{ $formatAngka($pembelianHistoris ?? 0) }}
                    </p>
                    <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                        Laporan pembelian
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Riwayat Stok Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        +{{ $formatAngka($stokMasukHariIni ?? 0) }} / -{{ $formatAngka($stokKeluarHariIni ?? 0) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        Netto: {{ ($nettoPerubahanStokHariIni ?? 0) > 0 ? '+' : '' }}{{ $formatAngka($nettoPerubahanStokHariIni ?? 0) }} · Penyesuaian: {{ $formatAngka($penyesuaianStokHariIni ?? 0) }}
                    </p>
                    <a href="{{ route('laporan.riwayatStok') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
                        Laporan riwayat stok
                    </a>
                </div>

                <div class="bg-white border rounded-2xl p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Status PPN Barang</p>
                    <h3 class="text-xl font-bold text-gray-900 mt-2">
                        {{ $formatAngka($totalBarangKenaPpn ?? 0) }} kena PPN
                    </h3>
                    <p class="text-xs text-gray-500 mt-2">
                        PPN Normal: {{ $formatAngka($totalBarangPpnNormal ?? 0) }} ·
                        DPP Nilai Lain: {{ $formatAngka($totalBarangPpnDppNilaiLain ?? 0) }} ·
                        Non PPN: {{ $formatAngka($totalBarangNonPpn ?? 0) }}
                    </p>
                    <a href="{{ route('laporan.stokBarang') }}" class="text-sm text-blue-600 hover:underline mt-3 inline-block">
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
                            Bagian ini membantu admin melihat fitur penting yang sudah disesuaikan: invoice history penjualan mengikuti fitur penjualan baru, invoice historis tetap masuk laporan dan piutang, transaksi historis tidak mengubah stok, pembelian bisa lengkap/sebagian/belum dikirim, dan stock opname tercatat sebagai audit trail.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
                        <div class="rounded-xl bg-purple-50 px-4 py-3">
                            <p class="text-gray-500">Invoice Historis</p>
                            <p class="font-bold text-purple-700">{{ $formatAngka($invoiceHistoris ?? 0) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Nilai: {{ $formatRupiah($totalNilaiInvoiceHistoris ?? 0) }}</p>
                        </div>

                        <div class="rounded-xl bg-indigo-50 px-4 py-3">
                            <p class="text-gray-500">Piutang Historis</p>
                            <p class="font-bold text-indigo-700">{{ $formatAngka($piutangDariInvoiceHistoris ?? 0) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Sisa: {{ $formatRupiah($sisaPiutangInvoiceHistoris ?? 0) }}</p>
                        </div>

                        <div class="rounded-xl bg-blue-50 px-4 py-3">
                            <p class="text-gray-500">Pembelian Historis</p>
                            <p class="font-bold text-blue-700">{{ $formatAngka($pembelianHistoris ?? 0) }}</p>
                        </div>

                        <div class="rounded-xl bg-orange-50 px-4 py-3">
                            <p class="text-gray-500">Tidak Ubah Stok</p>
                            <p class="font-bold text-orange-700">{{ $formatAngka(($pembelianTidakMempengaruhiStok ?? 0) + ($penjualanTidakMempengaruhiStok ?? 0)) }}</p>
                        </div>

                        <div class="rounded-xl bg-green-50 px-4 py-3">
                            <p class="text-gray-500">Ubah Stok</p>
                            <p class="font-bold text-green-700">{{ $formatAngka(($pembelianMempengaruhiStok ?? 0) + ($penjualanMempengaruhiStok ?? 0)) }}</p>
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

                            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                                <progress value="{{ $item->total }}" max="{{ $maxGrafik }}" class="w-full h-3 block"></progress>
                            </div>
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
                        <div class="flex items-center justify-between py-2 border-b last:border-b-0 gap-3">
                            <div>
                                <p class="font-semibold text-sm text-gray-900">
                                    {{ $item->nama_barang }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->kode_barang }} · {{ strtoupper($item->satuan ?? '-') }} · {{ $tipePerhitungan === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal' }} · {{ $labelJenisPpnBarang($normalisasiJenisPpnBarang($item)) }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="font-bold {{ $item->stok_saat_ini <= 0 ? 'text-red-700' : ($item->stok_saat_ini <= $batasStokRendah ? 'text-yellow-700' : 'text-gray-900') }}">
                                    {{ $formatAngka($item->stok_saat_ini) }}
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
                            <strong class="text-green-700">{{ $formatAngka($pembelianLengkap ?? 0) }}</strong>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-sm text-gray-600">Sebagian</span>
                            <strong class="text-yellow-700">{{ $formatAngka($pembelianSebagian ?? 0) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Belum Dikirim</span>
                            <strong class="text-red-700">{{ $formatAngka($pembelianBelumDikirim ?? 0) }}</strong>
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
                            <p class="text-xl font-bold text-gray-900">{{ $formatAngka($totalBarangNormal ?? 0) }}</p>
                        </div>
                        <div class="rounded-xl bg-purple-50 p-3">
                            <p class="text-xs text-gray-500">Isi Kemasan</p>
                            <p class="text-xl font-bold text-purple-700">{{ $formatAngka($totalBarangIsiKemasan ?? 0) }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500">
                        Detail penjualan: normal {{ $formatAngka($detailPenjualanNormal ?? 0) }} baris, isi kemasan {{ $formatAngka($detailPenjualanIsiKemasan ?? 0) }} baris. PPN normal {{ $formatAngka($detailPenjualanPpnNormal ?? 0) }}, DPP nilai lain {{ $formatAngka($detailPenjualanPpnDppNilaiLain ?? 0) }}, non PPN {{ $formatAngka($detailPenjualanNonPpn ?? 0) }}.
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
                            <p class="font-bold text-gray-900">{{ $formatRupiah($totalNilaiStok ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Estimasi Nilai Jual</p>
                            <p class="font-bold text-green-700">{{ $formatRupiah($totalEstimasiNilaiJual ?? 0) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Estimasi Margin Kotor</p>
                            <p class="font-bold {{ ($totalPotensiMargin ?? 0) >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                                {{ $formatRupiah($totalPotensiMargin ?? 0) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
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
                        $invoiceTampil = $isHistoris && !empty($item->penjualan->nomor_dokumen_asli)
                        ? $item->penjualan->nomor_dokumen_asli
                        : $item->nomor_invoice;
                        @endphp
                        <div class="py-2 border-b last:border-b-0">
                            <div class="flex justify-between gap-3">
                                <div>
                                    <a href="{{ route('piutang.show', $item->id_piutang) }}" class="font-semibold text-sm text-gray-900 hover:underline">
                                        {{ $invoiceTampil }}
                                    </a>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->customer->nama_customer ?? '-' }} · {{ $isHistoris ? 'Historis' : 'Sistem' }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-red-600 text-sm">
                                        {{ $formatRupiah($item->sisa_piutang) }}
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
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Pembelian Belum Selesai
                            </h3>
                            <p class="text-sm text-gray-500">
                                Pembelian dengan status sebagian atau belum dikirim.
                            </p>
                        </div>

                        <a href="{{ route('laporan.pembelian') }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($pembelianBelumSelesai as $item)
                        @php
                        $isHistoris = (bool) ($item->is_historical ?? false);
                        $dokumenTampil = $nomorPembelianTampil($item);
                        $statusText = $statusPenerimaanText($item->status_penerimaan ?? 'lengkap');
                        @endphp
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $dokumenTampil }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->supplier->nama_supplier ?? '-' }} · {{ $isHistoris ? 'Historis' : 'Sistem' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold {{ ($item->status_penerimaan ?? '') === 'belum_dikirim' ? 'text-red-700' : 'text-yellow-700' }}">
                                    {{ $statusText }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Tidak ada pembelian yang belum selesai.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Stock Opname Terbaru
                            </h3>
                            <p class="text-sm text-gray-500">
                                Hasil opname terbaru dari halaman stock opname.
                            </p>
                        </div>

                        <a href="{{ route('riwayat-stok.index', ['tipe_riwayat' => 'opname']) }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($stockOpnameTerbaru as $item)
                        @php
                        $selisih = (int) ($item->stok_sesudah ?? 0) - (int) ($item->stok_sebelum ?? 0);
                        @endphp
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $item->barang->nama_barang ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->sumber_transaksi ?? '-' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold {{ $selisih > 0 ? 'text-green-700' : ($selisih < 0 ? 'text-red-700' : 'text-gray-700') }}">
                                    {{ $selisih > 0 ? '+' : '' }}{{ $formatAngka($selisih) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada stock opname.
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
                                Transaksi Penjualan Terbaru
                            </h3>
                            <p class="text-sm text-gray-500">
                                Nomor historis tampil memakai dokumen asli.
                            </p>
                        </div>

                        <a href="{{ route('penjualan.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($penjualanTerbaru as $item)
                        @php
                        $isHistoris = (bool) ($item->is_historical ?? false);
                        $invoiceTampil = $nomorPenjualanTampil($item);
                        @endphp
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $invoiceTampil }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->customer->nama_customer ?? '-' }} · {{ $isHistoris ? 'Historis' : ucfirst($item->metode_pembayaran ?? '-') }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $formatRupiah($item->total_akhir) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada penjualan.
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Transaksi Pembelian Terbaru
                            </h3>
                            <p class="text-sm text-gray-500">
                                Nomor historis tampil memakai dokumen asli.
                            </p>
                        </div>

                        <a href="{{ route('pembelian.index') }}" class="text-sm text-blue-600 hover:underline">
                            Lihat
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($pembelianTerbaru as $item)
                        @php
                        $isHistoris = (bool) ($item->is_historical ?? false);
                        $dokumenTampil = $nomorPembelianTampil($item);
                        $statusText = $statusPenerimaanText($item->status_penerimaan ?? 'lengkap');
                        @endphp
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $dokumenTampil }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->supplier->nama_supplier ?? '-' }} · {{ $isHistoris ? 'Historis' : $statusText }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $formatRupiah($item->total_akhir) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada pembelian.
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
                        $selisih = (int) ($item->stok_sesudah ?? 0) - (int) ($item->stok_sebelum ?? 0);
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
                                    {{ ucfirst($jenis) }} {{ $formatAngka($item->jumlah ?? 0) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Selisih: {{ $selisih > 0 ? '+' : '' }}{{ $formatAngka($selisih) }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border rounded-2xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900">
                                Barang Isi Kemasan
                            </h3>
                            <p class="text-sm text-gray-500">
                                Barang yang harga jualnya dihitung per isi kemasan.
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
                                    1 {{ strtoupper($item->satuan ?? '-') }} = {{ $formatDesimal($item->isi_per_satuan ?? 1) }} {{ strtoupper($item->satuan_hitung_harga ?? $item->satuan ?? '-') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $formatRupiah($item->harga_jual_default ?? 0) }}
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
                                Barang Non PPN
                            </h3>
                            <p class="text-sm text-gray-500">
                                Contoh barang yang tidak dikenakan PPN.
                            </p>
                        </div>

                        <a href="{{ route('laporan.stokBarang', ['status_ppn' => 'non_ppn']) }}" class="text-sm text-blue-600 hover:underline">
                            Detail
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse ($barangNonPpn as $item)
                        <div class="flex justify-between gap-3 py-2 border-b last:border-b-0">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $item->nama_barang }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $item->kode_barang }} · {{ strtoupper($item->satuan ?? '-') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $formatAngka($item->stok_saat_ini ?? 0) }}
                                </p>
                                <p class="text-xs text-gray-500">stok</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-sm text-gray-500 py-6 text-center border rounded-xl">
                            Belum ada barang non PPN.
                        </div>
                        @endforelse
                    </div>
                </div>

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
            </div>

        </div>
    </div>
</x-app-layout>