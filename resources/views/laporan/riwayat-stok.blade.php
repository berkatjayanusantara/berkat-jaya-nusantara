<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Laporan Riwayat Stok
            </h2>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('laporan.riwayatStok.exportExcel', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-center">
                    Export Excel
                </a>

                <a href="{{ route('laporan.riwayatStok.exportPdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-center">
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('laporan.riwayatStok') }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
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
                            <label class="block mb-1 font-medium">Barang</label>
                            <select name="id_barang"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Barang</option>
                                @foreach ($barang as $item)
                                <option value="{{ $item->id_barang }}" {{ (string) request('id_barang') === (string) $item->id_barang ? 'selected' : '' }}>
                                    {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jenis Pergerakan</label>
                            <select name="jenis_pergerakan"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Jenis</option>
                                <option value="masuk" {{ request('jenis_pergerakan') === 'masuk' ? 'selected' : '' }}>
                                    Barang Masuk
                                </option>
                                <option value="keluar" {{ request('jenis_pergerakan') === 'keluar' ? 'selected' : '' }}>
                                    Barang Keluar
                                </option>
                                <option value="penyesuaian" {{ request('jenis_pergerakan') === 'penyesuaian' ? 'selected' : '' }}>
                                    Penyesuaian
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tipe Riwayat</label>
                            <select name="tipe_riwayat"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="opname" {{ request('tipe_riwayat') === 'opname' ? 'selected' : '' }}>
                                    Stock Opname
                                </option>
                                <option value="non_opname" {{ request('tipe_riwayat') === 'non_opname' ? 'selected' : '' }}>
                                    Selain Opname
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Barang / sumber / keterangan..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('laporan.riwayatStok') }}"
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
                    <p class="text-sm text-gray-500">Total Data</p>
                    <p class="text-2xl font-bold">{{ number_format($totalData, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Masuk</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($totalMasuk, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Keluar</p>
                    <p class="text-2xl font-bold text-red-700">{{ number_format($totalKeluar, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Jumlah Penyesuaian</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ number_format($totalPenyesuaian, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Jumlah Stock Opname</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($totalOpname, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Selisih Bertambah</p>
                    <p class="text-2xl font-bold text-green-700">+{{ number_format($totalSelisihPlus, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Selisih Berkurang</p>
                    <p class="text-2xl font-bold text-red-700">-{{ number_format($totalSelisihMinus, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Kode Barang</th>
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-center">Jenis</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-right">Jumlah</th>
                                <th class="border px-3 py-2 text-right">Stok Sebelum</th>
                                <th class="border px-3 py-2 text-right">Stok Sesudah</th>
                                <th class="border px-3 py-2 text-right">Selisih</th>
                                <th class="border px-3 py-2 text-left">Sumber</th>
                                <th class="border px-3 py-2 text-left">Keterangan</th>
                                <th class="border px-3 py-2 text-left">Dibuat Oleh</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayatStok as $item)
                            @php
                            $selisih = (int) $item->stok_sesudah - (int) $item->stok_sebelum;
                            $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');

                            if ($item->jenis_pergerakan === 'masuk') {
                            $jenisLabel = 'Masuk';
                            $jenisClass = 'bg-green-100 text-green-700';
                            } elseif ($item->jenis_pergerakan === 'keluar') {
                            $jenisLabel = 'Keluar';
                            $jenisClass = 'bg-red-100 text-red-700';
                            } else {
                            $jenisLabel = 'Penyesuaian';
                            $jenisClass = 'bg-yellow-100 text-yellow-700';
                            }

                            if ($selisih > 0) {
                            $selisihText = '+' . number_format($selisih, 0, ',', '.');
                            $selisihClass = 'text-green-700';
                            } elseif ($selisih < 0) {
                                $selisihText=number_format($selisih, 0, ',' , '.' );
                                $selisihClass='text-red-700' ;
                                } else {
                                $selisihText='0' ;
                                $selisihClass='text-gray-700' ;
                                }
                                @endphp

                                <tr>
                                <td class="border px-3 py-2">
                                    {{ $riwayatStok->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->barang->kode_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->barang->nama_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $jenisClass }}">
                                        {{ $jenisLabel }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($isOpname)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        Opname
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                        Transaksi
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ number_format($item->jumlah, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ number_format($item->stok_sebelum, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    {{ number_format($item->stok_sesudah, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold {{ $selisihClass }}">
                                    {{ $selisihText }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->sumber_transaksi ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->keterangan ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->user->nama_user ?? '-' }}
                                </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="border px-3 py-6 text-center text-gray-500">
                                        Data laporan riwayat stok belum tersedia.
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>    
                    </table>
                </div>

                <div class="mt-4">
                    {{ $riwayatStok->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>