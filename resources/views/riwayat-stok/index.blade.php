<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Riwayat Stok
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Jejak pergerakan stok barang dari pembelian, penjualan, edit transaksi, dan stock opname.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock-opname.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                    Stock Opname
                </a>

                <a href="{{ route('laporan.riwayatStok', request()->query()) }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900 transition">
                    Laporan Riwayat Stok
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('riwayat-stok.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-8 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block mb-1 font-medium text-sm text-gray-700">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Barang, sumber, keterangan, admin..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Tanggal Awal</label>
                            <input type="date"
                                name="tanggal_mulai"
                                value="{{ $tanggalMulai }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Tanggal Akhir</label>
                            <input type="date"
                                name="tanggal_selesai"
                                value="{{ $tanggalSelesai }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Barang</label>
                            <select name="id_barang"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Barang</option>
                                @foreach ($barang as $item)
                                <option value="{{ $item->id_barang }}" {{ (string) $idBarang === (string) $item->id_barang ? 'selected' : '' }}>
                                    {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Jenis</label>
                            <select name="jenis_pergerakan"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Jenis</option>
                                <option value="masuk" {{ $jenis === 'masuk' ? 'selected' : '' }}>
                                    Masuk
                                </option>
                                <option value="keluar" {{ $jenis === 'keluar' ? 'selected' : '' }}>
                                    Keluar
                                </option>
                                <option value="penyesuaian" {{ $jenis === 'penyesuaian' ? 'selected' : '' }}>
                                    Penyesuaian
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Tipe Riwayat</label>
                            <select name="tipe_riwayat"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="opname" {{ $tipeRiwayat === 'opname' ? 'selected' : '' }}>
                                    Stock Opname
                                </option>
                                <option value="non_opname" {{ $tipeRiwayat === 'non_opname' ? 'selected' : '' }}>
                                    Selain Opname
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Status Barang</label>
                            <select name="status_barang"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="1" {{ (string) $statusBarang === '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="0" {{ (string) $statusBarang === '0' ? 'selected' : '' }}>
                                    Nonaktif
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Tipe Harga</label>
                            <select name="tipe_harga"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="normal" {{ $tipeHarga === 'normal' ? 'selected' : '' }}>
                                    Normal
                                </option>
                                <option value="isi_kemasan" {{ $tipeHarga === 'isi_kemasan' ? 'selected' : '' }}>
                                    Isi Kemasan
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium text-sm text-gray-700">Status PPN</label>
                            <select name="status_ppn"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="kena_ppn" {{ $statusPpn === 'kena_ppn' ? 'selected' : '' }}>
                                    Kena PPN
                                </option>
                                <option value="non_ppn" {{ $statusPpn === 'non_ppn' ? 'selected' : '' }}>
                                    Non PPN
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-2 mt-4">
                        <a href="{{ route('riwayat-stok.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">
                            Reset
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Data</p>
                    <p class="text-2xl font-bold">
                        {{ number_format($totalData ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang unik: {{ number_format($totalBarangUnik ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Masuk</p>
                    <p class="text-2xl font-bold text-green-700">
                        +{{ number_format($totalMasuk ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($totalTransaksiMasuk ?? 0, 0, ',', '.') }} transaksi
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Keluar</p>
                    <p class="text-2xl font-bold text-red-700">
                        -{{ number_format($totalKeluar ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($totalTransaksiKeluar ?? 0, 0, ',', '.') }} transaksi
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Penyesuaian</p>
                    <p class="text-2xl font-bold text-yellow-700">
                        {{ number_format($totalPenyesuaian ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Total qty penyesuaian: {{ number_format($totalJumlahPenyesuaian ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Stock Opname</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ number_format($totalOpname ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Selain opname: {{ number_format($totalNonOpname ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Selisih Bertambah</p>
                    <p class="text-2xl font-bold text-green-700">
                        +{{ number_format($totalSelisihBertambah ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Selisih Berkurang</p>
                    <p class="text-2xl font-bold text-red-700">
                        -{{ number_format($totalSelisihBerkurang ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Netto Perubahan</p>
                    <p class="text-2xl font-bold {{ ($nettoPerubahan ?? 0) >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                        {{ ($nettoPerubahan ?? 0) > 0 ? '+' : '' }}{{ number_format($nettoPerubahan ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Selisih stok sesudah - sebelum.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tipe Harga Barang Terkait</p>
                    <p class="text-lg font-bold">
                        Normal: {{ number_format($totalBarangNormal ?? 0, 0, ',', '.') }} | Isi Kemasan: {{ number_format($totalBarangIsiKemasan ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status PPN Barang Terkait</p>
                    <p class="text-lg font-bold">
                        Kena PPN: {{ number_format($totalBarangKenaPpn ?? 0, 0, ',', '.') }} | Non PPN: {{ number_format($totalBarangNonPpn ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Data Riwayat Stok
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Riwayat bersifat audit trail, jadi tidak disediakan tombol edit atau hapus.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Barang</th>
                                <th class="border px-3 py-2 text-center">Jenis</th>
                                <th class="border px-3 py-2 text-center">Tipe</th>
                                <th class="border px-3 py-2 text-right">Jumlah</th>
                                <th class="border px-3 py-2 text-right">Sebelum</th>
                                <th class="border px-3 py-2 text-right">Sesudah</th>
                                <th class="border px-3 py-2 text-right">Selisih</th>
                                <th class="border px-3 py-2 text-left">Sumber</th>
                                <th class="border px-3 py-2 text-left">Keterangan</th>
                                <th class="border px-3 py-2 text-left">Admin</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayatStok as $item)
                            @php
                            $stokSebelum = (int) ($item->stok_sebelum ?? 0);
                            $stokSesudah = (int) ($item->stok_sesudah ?? 0);
                            $selisih = $stokSesudah - $stokSebelum;
                            $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');
                            $tipePerhitungan = $item->barang->tipe_perhitungan_harga ?? 'normal';
                            $satuan = $item->barang->satuan ?? '-';
                            $satuanHitung = $item->barang->satuan_hitung_harga ?? $satuan;
                            $isiPerSatuan = (float) ($item->barang->isi_per_satuan ?? 1);
                            $kenaPpn = (bool) ($item->barang->kena_ppn ?? true);

                            if ($item->jenis_pergerakan === 'masuk') {
                            $jenisLabel = 'Masuk';
                            $jenisClass = 'bg-green-100 text-green-700';
                            $rowClass = '';
                            } elseif ($item->jenis_pergerakan === 'keluar') {
                            $jenisLabel = 'Keluar';
                            $jenisClass = 'bg-red-100 text-red-700';
                            $rowClass = '';
                            } else {
                            $jenisLabel = 'Penyesuaian';
                            $jenisClass = 'bg-yellow-100 text-yellow-700';
                            $rowClass = 'bg-yellow-50';
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

                                <tr class="{{ $rowClass }}">
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($riwayatStok->currentPage() - 1) * $riwayatStok->perPage() }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    <div class="font-medium">
                                        {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->created_at ? $item->created_at->format('H:i') : '-' }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 min-w-[230px]">
                                    <div class="font-semibold">
                                        {{ $item->barang->kode_barang ?? '-' }}
                                    </div>
                                    <div>
                                        {{ $item->barang->nama_barang ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Satuan: {{ strtoupper($satuan) }}
                                    </div>

                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @if ($tipePerhitungan === 'isi_kemasan')
                                        <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">
                                            Isi Kemasan
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                            1 {{ strtoupper($satuan) }} = {{ rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') }} {{ strtoupper($satuanHitung) }}
                                        </span>
                                        @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                            Normal
                                        </span>
                                        @endif

                                        @if ($kenaPpn)
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                            Kena PPN
                                        </span>
                                        @else
                                        <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">
                                            Non PPN
                                        </span>
                                        @endif
                                    </div>
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

                                <td class="border px-3 py-2 text-right font-semibold">
                                    {{ number_format($item->jumlah ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ number_format($stokSebelum, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    {{ number_format($stokSesudah, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold {{ $selisihClass }}">
                                    {{ $selisihText }}
                                </td>

                                <td class="border px-3 py-2 min-w-[160px]">
                                    <div class="font-medium break-words">
                                        {{ $item->sumber_transaksi ?? '-' }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 min-w-[220px]">
                                    {{ $item->keterangan ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    <div class="font-medium">
                                        {{ $item->user->nama_user ?? '-' }}
                                    </div>
                                    @if ($item->user?->username)
                                    <div class="text-xs text-gray-500">
                                        {{ $item->user->username }}
                                    </div>
                                    @endif
                                </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="border px-3 py-6 text-center text-gray-500">
                                        Data riwayat stok belum tersedia.
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