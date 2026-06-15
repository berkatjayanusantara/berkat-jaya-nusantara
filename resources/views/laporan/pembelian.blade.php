<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Pembelian
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan invoice pembelian sistem berjalan dan invoice pembelian historis.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.pembelian.exportExcel', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Export Excel
                </a>

                <a href="{{ route('laporan.pembelian.exportPdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277';
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
                <form method="GET" action="{{ route('laporan.pembelian') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
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
                            <label class="block mb-1 font-medium">Supplier</label>
                            <select name="id_supplier"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Supplier</option>

                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id_supplier }}"
                                    {{ request('id_supplier') == $supplier->id_supplier ? 'selected' : '' }}>
                                    {{ $supplier->nama_supplier }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status Terima</label>
                            <select name="status_penerimaan"
                                class="w-full border-gray-300 rounded-md shadow-sm">
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
                            <label class="block mb-1 font-medium">Tipe Invoice</label>
                            <select name="tipe_invoice"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="sistem" {{ request('tipe_invoice') === 'sistem' ? 'selected' : '' }}>
                                    Sistem Berjalan
                                </option>
                                <option value="historis" {{ request('tipe_invoice') === 'historis' ? 'selected' : '' }}>
                                    Historis / Lama
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Pengaruh Stok</label>
                            <select name="pengaruh_stok"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="mempengaruhi" {{ request('pengaruh_stok') === 'mempengaruhi' ? 'selected' : '' }}>
                                    Mempengaruhi
                                </option>
                                <option value="tidak_mempengaruhi" {{ request('pengaruh_stok') === 'tidak_mempengaruhi' ? 'selected' : '' }}>
                                    Tidak Mempengaruhi
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nota/DO/SJ/supplier..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('laporan.pembelian') }}"
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
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <p class="text-2xl font-bold">{{ $totalTransaksi ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Sistem: {{ $totalSistemBerjalan ?? 0 }} | Historis: {{ $totalHistoris ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Subtotal</p>
                    <p class="text-2xl font-bold">
                        Rp {{ number_format($totalSubtotal ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Pajak</p>
                    <p class="text-2xl font-bold">
                        Rp {{ number_format($totalPajak ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Akhir</p>
                    <p class="text-2xl font-bold text-green-700">
                        Rp {{ number_format($totalAkhir ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Dipesan</p>
                    <p class="text-2xl font-bold">
                        {{ number_format($totalDipesan ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Diterima</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ number_format($totalDiterima ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Sisa Belum Dikirim</p>
                    <p class="text-2xl font-bold text-yellow-700">
                        {{ number_format($totalSisa ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pengaruh Stok</p>
                    <p class="text-xl font-bold">
                        {{ $totalMemengaruhiStok ?? 0 }} transaksi
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Tidak memengaruhi: {{ $totalTidakMemengaruhiStok ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status Lengkap</p>
                    <p class="text-2xl font-bold text-green-700">
                        {{ $totalLengkap ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status Sebagian</p>
                    <p class="text-2xl font-bold text-yellow-700">
                        {{ $totalSebagian ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Belum Dikirim</p>
                    <p class="text-2xl font-bold text-red-700">
                        {{ $totalBelumDikirim ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Dokumen</th>
                                <th class="border px-3 py-2 text-left">Supplier</th>
                                <th class="border px-3 py-2 text-left">Alamat</th>
                                <th class="border px-3 py-2 text-left">Status</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-center">Stok</th>
                                <th class="border px-3 py-2 text-right">Dipesan</th>
                                <th class="border px-3 py-2 text-right">Diterima</th>
                                <th class="border px-3 py-2 text-right">Sisa</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">Pajak</th>
                                <th class="border px-3 py-2 text-right">Total</th>
                                <th class="border px-3 py-2 text-center">Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pembelian as $item)
                            @php
                            $jumlahDipesan = 0;
                            $jumlahDiterima = 0;

                            foreach ($item->detailPembelian as $detail) {
                            $jumlahDipesan += $detail->jumlah_dipesan ?? $detail->jumlah;
                            $jumlahDiterima += $detail->jumlah;
                            }

                            $sisa = max($jumlahDipesan - $jumlahDiterima, 0);
                            $statusPenerimaan = $item->status_penerimaan ?? 'lengkap';
                            $isHistoris = (bool) ($item->is_historical ?? false);
                            $affectStock = (bool) ($item->affect_stock ?? true);
                            $pajakDitambahkan = $item->pajak_ditambahkan ?? true;

                            if ($statusPenerimaan === 'lengkap') {
                            $statusLabel = 'Lengkap';
                            $statusClass = 'bg-green-100 text-green-700';
                            } elseif ($statusPenerimaan === 'sebagian') {
                            $statusLabel = 'Sebagian';
                            $statusClass = 'bg-yellow-100 text-yellow-700';
                            } else {
                            $statusLabel = 'Belum Dikirim';
                            $statusClass = 'bg-red-100 text-red-700';
                            }

                            $detailRoute = $isHistoris
                            ? route('invoice-historis.pembelian.show', [
                            'pembelian' => $item->id_pembelian,
                            'back_url' => request()->fullUrl(),
                            ])
                            : route('pembelian.show', [
                            'pembelian' => $item->id_pembelian,
                            'back_url' => request()->fullUrl(),
                            ]);
                            @endphp

                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $pembelian->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-semibold">
                                        {{ $item->nomor_pembelian }}
                                    </div>

                                    @if ($item->nomor_dokumen_asli)
                                    <div class="text-xs text-gray-500">
                                        Dok. Asli: {{ $item->nomor_dokumen_asli }}
                                    </div>
                                    @endif

                                    @if ($item->nomor_delivery_order)
                                    <div class="text-xs text-gray-500">
                                        DO: {{ $item->nomor_delivery_order }}
                                    </div>
                                    @endif

                                    @if ($item->nomor_surat_jalan)
                                    <div class="text-xs text-gray-500">
                                        SJ: {{ $item->nomor_surat_jalan }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-medium">
                                        {{ $item->supplier->nama_supplier ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Telp: {{ $item->supplier->nomor_telepon ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        NPWP: {{ $item->supplier->npwp ?? '-' }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 min-w-[180px]">
                                    {{ $item->supplier->alamat ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>

                                    @if (!$pajakDitambahkan)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Pajak tampil saja
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($isHistoris)
                                    <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">
                                        Historis
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                        Sistem
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($affectStock)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        Ya
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">
                                        Tidak
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ number_format($jumlahDipesan, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ number_format($jumlahDiterima, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    @if ($sisa > 0)
                                    <span class="text-yellow-700 font-semibold">
                                        {{ number_format($sisa, 0, ',', '.') }}
                                    </span>
                                    @else
                                    0
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->nilai_pajak, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <a href="{{ $detailRoute }}"
                                        class="text-blue-600 hover:underline">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="15" class="border px-3 py-6 text-center text-gray-500">
                                    Data laporan pembelian belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pembelian->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>