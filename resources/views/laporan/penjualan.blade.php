<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Penjualan
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan invoice penjualan sistem berjalan dan invoice penjualan historis.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.penjualan.exportExcel', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Export Excel
                </a>

                <a href="{{ route('laporan.penjualan.exportPdf', request()->query()) }}"
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
                <form method="GET" action="{{ route('laporan.penjualan') }}">
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
                            <label class="block mb-1 font-medium">Customer</label>
                            <select name="id_customer"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Customer</option>

                                @foreach ($customers as $customer)
                                <option value="{{ $customer->id_customer }}"
                                    {{ request('id_customer') == $customer->id_customer ? 'selected' : '' }}>
                                    {{ $customer->nama_customer }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Metode Pembayaran</label>
                            <select name="metode_pembayaran"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>
                                    Tunai
                                </option>
                                <option value="kredit" {{ request('metode_pembayaran') === 'kredit' ? 'selected' : '' }}>
                                    Kredit / Piutang
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status Pembayaran</label>
                            <select name="status_pembayaran"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>
                                    Lunas
                                </option>
                                <option value="sebagian" {{ request('status_pembayaran') === 'sebagian' ? 'selected' : '' }}>
                                    Sebagian
                                </option>
                                <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>
                                    Belum Lunas
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
                            <label class="block mb-1 font-medium">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Invoice/dokumen/customer..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('laporan.penjualan') }}"
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
                    <p class="text-sm text-gray-500">Total Tunai</p>
                    <p class="text-xl font-bold">
                        Rp {{ number_format($totalTunai ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Kredit</p>
                    <p class="text-xl font-bold">
                        Rp {{ number_format($totalKredit ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Dibayar Piutang</p>
                    <p class="text-xl font-bold text-blue-700">
                        Rp {{ number_format($totalDibayar ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Sisa Piutang</p>
                    <p class="text-xl font-bold text-red-700">
                        Rp {{ number_format($totalSisaPiutang ?? 0, 0, ',', '.') }}
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
                                <th class="border px-3 py-2 text-left">Invoice</th>
                                <th class="border px-3 py-2 text-left">Customer</th>
                                <th class="border px-3 py-2 text-left">Metode</th>
                                <th class="border px-3 py-2 text-left">Status</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">Pajak</th>
                                <th class="border px-3 py-2 text-right">Total</th>
                                <th class="border px-3 py-2 text-right">Sisa Piutang</th>
                                <th class="border px-3 py-2 text-center">Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($penjualan as $item)
                            @php
                            $isHistoris = (bool) ($item->is_historical ?? false);
                            $pajakDitambahkan = $item->pajak_ditambahkan ?? true;

                            $statusLabel = match ($item->status_pembayaran) {
                            'lunas' => 'Lunas',
                            'sebagian' => 'Sebagian',
                            default => 'Belum Lunas',
                            };

                            $statusClass = match ($item->status_pembayaran) {
                            'lunas' => 'bg-green-100 text-green-700',
                            'sebagian' => 'bg-blue-100 text-blue-700',
                            default => 'bg-yellow-100 text-yellow-700',
                            };

                            $detailRoute = $isHistoris
                            ? route('invoice-historis.penjualan.show', [
                            'penjualan' => $item->id_penjualan,
                            'back_url' => request()->fullUrl(),
                            ])
                            : route('penjualan.show', [
                            'penjualan' => $item->id_penjualan,
                            'back_url' => request()->fullUrl(),
                            ]);
                            @endphp

                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $penjualan->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-semibold">
                                        {{ $item->nomor_invoice }}
                                    </div>

                                    @if ($item->nomor_dokumen_asli)
                                    <div class="text-xs text-gray-500">
                                        Dok. Asli: {{ $item->nomor_dokumen_asli }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-medium">
                                        {{ $item->customer->nama_customer ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $item->customer->nomor_telepon ?? '-' }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2">
                                    {{ ucfirst($item->metode_pembayaran ?? '-') }}
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

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->nilai_pajak ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    Rp {{ number_format($item->total_akhir ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    @if ($item->piutang)
                                    Rp {{ number_format($item->piutang->sisa_piutang ?? 0, 0, ',', '.') }}
                                    @else
                                    -
                                    @endif
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
                                <td colspan="12" class="border px-3 py-6 text-center text-gray-500">
                                    Data laporan penjualan belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $penjualan->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>