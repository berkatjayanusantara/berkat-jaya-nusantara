<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Piutang
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan piutang dari invoice penjualan sistem berjalan dan invoice penjualan historis.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.piutang.exportExcel', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Export Excel
                </a>

                <a href="{{ route('laporan.piutang.exportPdf', request()->query()) }}"
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
                <form method="GET" action="{{ route('laporan.piutang') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Jatuh Tempo Awal</label>
                            <input type="date"
                                name="tanggal_awal"
                                value="{{ request('tanggal_awal') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jatuh Tempo Akhir</label>
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
                            <label class="block mb-1 font-medium">Status Piutang</label>
                            <select name="status_piutang"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="belum_lunas" {{ request('status_piutang') === 'belum_lunas' ? 'selected' : '' }}>
                                    Belum Lunas
                                </option>
                                <option value="sebagian_dibayar" {{ request('status_piutang') === 'sebagian_dibayar' ? 'selected' : '' }}>
                                    Sebagian Dibayar
                                </option>
                                <option value="jatuh_tempo" {{ request('status_piutang') === 'jatuh_tempo' ? 'selected' : '' }}>
                                    Jatuh Tempo
                                </option>
                                <option value="lunas" {{ request('status_piutang') === 'lunas' ? 'selected' : '' }}>
                                    Lunas
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Kondisi Tempo</label>
                            <select name="jatuh_tempo"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="lewat" {{ request('jatuh_tempo') === 'lewat' ? 'selected' : '' }}>
                                    Lewat Jatuh Tempo
                                </option>
                                <option value="belum" {{ request('jatuh_tempo') === 'belum' ? 'selected' : '' }}>
                                    Belum Lewat
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
                        <a href="{{ route('laporan.piutang') }}"
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

            @php
            $persentaseTertagih = ($totalPiutang ?? 0) > 0
            ? (($totalDibayar ?? 0) / ($totalPiutang ?? 1)) * 100
            : 0;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Data</p>
                    <p class="text-2xl font-bold">{{ $totalData ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Sistem: {{ $totalSistemBerjalan ?? 0 }} | Historis: {{ $totalHistoris ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Piutang</p>
                    <p class="text-2xl font-bold">
                        Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Dibayar</p>
                    <p class="text-2xl font-bold text-blue-700">
                        Rp {{ number_format($totalDibayar ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Tertagih: {{ number_format($persentaseTertagih, 2, ',', '.') }}%
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Sisa Piutang</p>
                    <p class="text-2xl font-bold text-red-700">
                        Rp {{ number_format($totalSisa ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Belum Lunas</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $totalBelumLunas ?? 0 }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Sebagian Dibayar</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $totalSebagian ?? 0 }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Lunas</p>
                    <p class="text-2xl font-bold text-green-700">{{ $totalLunas ?? 0 }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Lewat Jatuh Tempo</p>
                    <p class="text-2xl font-bold text-red-700">{{ $totalLewatJatuhTempo ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Invoice</th>
                                <th class="border px-3 py-2 text-left">Customer</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Jatuh Tempo</th>
                                <th class="border px-3 py-2 text-left">Status</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-right">Total Piutang</th>
                                <th class="border px-3 py-2 text-right">Dibayar</th>
                                <th class="border px-3 py-2 text-right">Sisa</th>
                                <th class="border px-3 py-2 text-center">Keterangan</th>
                                <th class="border px-3 py-2 text-center">Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($piutang as $item)
                            @php
                            $isHistoris = (bool) ($item->penjualan->is_historical ?? false);
                            $nomorInvoiceTampil = $isHistoris && !empty($item->penjualan?->nomor_dokumen_asli)
                            ? $item->penjualan->nomor_dokumen_asli
                            : $item->nomor_invoice;

                            $lewatJatuhTempo = $item->status_piutang !== 'lunas'
                            && $item->tanggal_jatuh_tempo
                            && $item->tanggal_jatuh_tempo->isPast();

                            if ($item->status_piutang === 'lunas') {
                            $statusLabel = 'Lunas';
                            $statusClass = 'bg-green-100 text-green-700';
                            } elseif ($item->status_piutang === 'sebagian_dibayar') {
                            $statusLabel = 'Sebagian Dibayar';
                            $statusClass = 'bg-blue-100 text-blue-700';
                            } elseif ($item->status_piutang === 'jatuh_tempo') {
                            $statusLabel = 'Jatuh Tempo';
                            $statusClass = 'bg-red-100 text-red-700';
                            } else {
                            $statusLabel = 'Belum Lunas';
                            $statusClass = 'bg-yellow-100 text-yellow-700';
                            }

                            if ($item->status_piutang === 'lunas') {
                            $keteranganLabel = 'Selesai';
                            $keteranganClass = 'text-green-700';
                            } elseif ($lewatJatuhTempo || $item->status_piutang === 'jatuh_tempo') {
                            $keteranganLabel = 'Lewat Tempo';
                            $keteranganClass = 'text-red-700';
                            } else {
                            $keteranganLabel = 'Berjalan';
                            $keteranganClass = 'text-yellow-700';
                            }
                            @endphp

                            <tr class="{{ $lewatJatuhTempo ? 'bg-red-50' : '' }}">
                                <td class="border px-3 py-2">
                                    {{ $piutang->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-semibold">
                                        {{ $nomorInvoiceTampil }}
                                    </div>

                                    @if (!$isHistoris && $item->penjualan?->nomor_dokumen_asli)
                                    <div class="text-xs text-gray-500">
                                        Dok. Asli: {{ $item->penjualan->nomor_dokumen_asli }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-medium">
                                        {{ $item->customer->nama_customer ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Telp: {{ $item->customer->nomor_telepon ?? '-' }}
                                    </div>

                                    @if ($item->customer?->npwp)
                                    <div class="text-xs text-gray-500">
                                        NPWP: {{ $item->customer->npwp }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item->penjualan?->tanggal_penjualan ? $item->penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item->tanggal_jatuh_tempo ? $item->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
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
                                    Rp {{ number_format($item->total_piutang ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->total_dibayar ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    Rp {{ number_format($item->sisa_piutang ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="{{ $keteranganClass }} font-semibold">
                                        {{ $keteranganLabel }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <a href="{{ route('piutang.show', ['piutang' => $item->id_piutang, 'back_url' => request()->fullUrl()]) }}"
                                        class="text-blue-600 hover:underline">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="border px-3 py-6 text-center text-gray-500">
                                    Data laporan piutang belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $piutang->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>