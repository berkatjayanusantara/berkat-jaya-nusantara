<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Arsip Invoice
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Arsip seluruh invoice penjualan dan pembelian, baik transaksi berjalan maupun invoice historis.
                </p>
            </div>
        </div>
    </x-slot>

    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';

    $formatRupiah = function ($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    };

    $formatMetode = function ($metode) {
    if (!$metode || $metode === '-') {
    return '-';
    }

    return ucwords(str_replace('_', ' ', $metode));
    };
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="text-center">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $namaPerusahaan }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $alamatPerusahaan }}
                    </p>
                    <p class="text-sm text-gray-600">
                        Telp: {{ $teleponPerusahaan }}
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('arsip-invoice.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Jenis Invoice</label>
                            <select name="jenis_invoice" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="penjualan" {{ request('jenis_invoice') === 'penjualan' ? 'selected' : '' }}>
                                    Penjualan
                                </option>
                                <option value="pembelian" {{ request('jenis_invoice') === 'pembelian' ? 'selected' : '' }}>
                                    Pembelian
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tipe Invoice</label>
                            <select name="tipe_invoice" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="berjalan" {{ request('tipe_invoice') === 'berjalan' ? 'selected' : '' }}>
                                    Berjalan
                                </option>
                                <option value="historis" {{ request('tipe_invoice') === 'historis' ? 'selected' : '' }}>
                                    Historis
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Awal</label>
                            <input type="date"
                                name="tanggal_awal"
                                value="{{ request('tanggal_awal') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Akhir</label>
                            <input type="date"
                                name="tanggal_akhir"
                                value="{{ request('tanggal_akhir') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Metode Penjualan</label>
                            <select name="metode_pembayaran" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>
                                    Tunai
                                </option>
                                <option value="kredit" {{ request('metode_pembayaran') === 'kredit' ? 'selected' : '' }}>
                                    Kredit
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Per Halaman</label>
                            <select name="per_page" class="w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ([10, 25, 50, 100] as $jumlah)
                                <option value="{{ $jumlah }}" {{ (int) request('per_page', $perPage ?? 10) === $jumlah ? 'selected' : '' }}>
                                    {{ $jumlah }} data
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 font-medium">Status Pembayaran Penjualan</label>
                            <select name="status_pembayaran" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>
                                    Lunas
                                </option>
                                <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>
                                    Belum Lunas
                                </option>
                                <option value="sebagian_dibayar" {{ request('status_pembayaran') === 'sebagian_dibayar' ? 'selected' : '' }}>
                                    Sebagian Dibayar
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status Penerimaan Pembelian</label>
                            <select name="status_penerimaan" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="lengkap" {{ request('status_penerimaan') === 'lengkap' ? 'selected' : '' }}>
                                    Lengkap
                                </option>
                                <option value="sebagian" {{ request('status_penerimaan') === 'sebagian' ? 'selected' : '' }}>
                                    Sebagian
                                </option>
                                <option value="belum_dikirim" {{ request('status_penerimaan') === 'belum_dikirim' ? 'selected' : '' }}>
                                    Belum Dikirim
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Cari Invoice</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nomor invoice, customer, supplier, faktur, DO, SJ..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('arsip-invoice.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Reset
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Arsip Invoice</p>
                    <p class="text-2xl font-bold">{{ number_format($totalArsip ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Berjalan: {{ number_format($totalBerjalan ?? 0, 0, ',', '.') }} |
                        Historis: {{ number_format($totalHistoris ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Invoice Penjualan</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($totalPenjualan ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Tunai: {{ number_format($totalPenjualanTunai ?? 0, 0, ',', '.') }} |
                        Kredit: {{ number_format($totalPenjualanKredit ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Invoice Pembelian</p>
                    <p class="text-2xl font-bold text-purple-700">{{ number_format($totalPembelian ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Lengkap: {{ number_format($totalPembelianLengkap ?? 0, 0, ',', '.') }} |
                        Sebagian: {{ number_format($totalPembelianSebagian ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Nilai Invoice</p>
                    <p class="text-2xl font-bold text-green-700">{{ $formatRupiah($totalNilaiInvoice ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Penjualan + Pembelian sesuai filter.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Nilai Penjualan</p>
                    <p class="text-xl font-bold text-blue-700">{{ $formatRupiah($totalNilaiPenjualan ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Lunas: {{ number_format($totalPenjualanLunas ?? 0, 0, ',', '.') }} |
                        Belum/sebagian: {{ number_format($totalPenjualanBelumLunas ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Nilai Pembelian</p>
                    <p class="text-xl font-bold text-purple-700">{{ $formatRupiah($totalNilaiPembelian ?? 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Termasuk invoice pembelian berjalan dan historis sesuai filter.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Catatan Arsip</p>
                    <p class="text-sm text-gray-700 mt-1">
                        Tombol detail memakai tampilan invoice asli dari fitur penjualan, pembelian, dan invoice history.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-900">Daftar Arsip Invoice</h3>
                        <p class="text-sm text-gray-500">
                            Menampilkan seluruh invoice yang sesuai filter, diurutkan dari tanggal terbaru.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Jenis</th>
                                <th class="border px-3 py-2 text-left">No Invoice / Nota</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Customer / Supplier</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-center">Stok</th>
                                <th class="border px-3 py-2 text-center">Metode</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">PPN</th>
                                <th class="border px-3 py-2 text-right">Total</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($arsipInvoice as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $arsipInvoice->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $item['jenis_class'] }}">
                                        {{ $item['jenis_label'] }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 min-w-[190px]">
                                    <div class="font-semibold">
                                        {{ $item['nomor_invoice'] ?: '-' }}
                                    </div>

                                    @if (!empty($item['nomor_sistem']) && $item['nomor_sistem'] !== $item['nomor_invoice'])
                                    <div class="text-xs text-gray-500">
                                        No Sistem: {{ $item['nomor_sistem'] }}
                                    </div>
                                    @endif

                                    @if (!empty($item['nomor_delivery_order']))
                                    <div class="text-xs text-gray-500">
                                        DO: {{ $item['nomor_delivery_order'] }}
                                    </div>
                                    @endif

                                    @if (!empty($item['nomor_surat_jalan']))
                                    <div class="text-xs text-gray-500">
                                        SJ: {{ $item['nomor_surat_jalan'] }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item['tanggal'] ? $item['tanggal']->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2 min-w-[180px]">
                                    <div class="text-xs text-gray-500">
                                        {{ $item['pihak_label'] }}
                                    </div>
                                    <div class="font-medium">
                                        {{ $item['pihak_nama'] }}
                                    </div>

                                    @if (!empty($item['pihak_kode']))
                                    <div class="text-xs text-gray-500">
                                        {{ $item['pihak_kode'] }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $item['tipe_class'] }}">
                                        {{ $item['tipe_label'] }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item['affect_stock'])
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Pengaruh Stok
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                        Tidak Ubah Stok
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ $formatMetode($item['metode']) }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $item['status_class'] }}">
                                        {{ $item['status_label'] }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $formatRupiah($item['subtotal']) }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $formatRupiah($item['nilai_pajak']) }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    {{ $formatRupiah($item['total']) }}
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <a href="{{ $item['show_route'] }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Detail / Print
                                        </a>

                                        <a href="{{ $item['excel_route'] }}"
                                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            Excel
                                        </a>

                                        <a href="{{ $item['edit_route'] }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="border px-3 py-6 text-center text-gray-500">
                                    Data arsip invoice belum tersedia sesuai filter.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $arsipInvoice->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>