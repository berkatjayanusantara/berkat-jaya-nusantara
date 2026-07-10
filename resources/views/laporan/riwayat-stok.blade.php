<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Riwayat Stok
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan seluruh pergerakan stok barang dari pembelian, penjualan, stock opname, dan penyesuaian stok.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
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

    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';

    $nettoPerubahan = $totalNettoPerubahan ?? (($totalSelisihPlus ?? 0) - ($totalSelisihMinus ?? 0));

    $normalisasiJenisPpn = function ($item) {
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

    $labelJenisPpn = function ($jenisPpn) {
    return match ($jenisPpn) {
    'non_ppn' => 'Non PPN',
    'ppn_normal' => 'PPN Normal',
    'ppn_dpp_nilai_lain' => 'PPN DPP Nilai Lain',
    default => 'PPN Normal',
    };
    };

    $classJenisPpn = function ($jenisPpn) {
    return match ($jenisPpn) {
    'non_ppn' => 'bg-gray-100 text-gray-700',
    'ppn_normal' => 'bg-blue-100 text-blue-700',
    'ppn_dpp_nilai_lain' => 'bg-purple-100 text-purple-700',
    default => 'bg-blue-100 text-blue-700',
    };
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
                <form method="GET" action="{{ route('laporan.riwayatStok') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-8 gap-4">
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
                            <select name="id_barang" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua Barang</option>

                                @foreach ($barang as $itemBarang)
                                <option value="{{ $itemBarang->id_barang }}"
                                    {{ (string) request('id_barang') === (string) $itemBarang->id_barang ? 'selected' : '' }}>
                                    {{ $itemBarang->kode_barang }} - {{ $itemBarang->nama_barang }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jenis Pergerakan</label>
                            <select name="jenis_pergerakan" class="w-full border-gray-300 rounded-md shadow-sm">
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
                            <select name="tipe_riwayat" class="w-full border-gray-300 rounded-md shadow-sm">
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
                            <label class="block mb-1 font-medium">Tipe Harga</label>
                            <select name="tipe_harga" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="normal" {{ request('tipe_harga') === 'normal' ? 'selected' : '' }}>
                                    Normal
                                </option>
                                <option value="isi_kemasan" {{ request('tipe_harga') === 'isi_kemasan' ? 'selected' : '' }}>
                                    Isi Kemasan
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">PPN Barang</label>
                            <select name="status_ppn_barang" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="kena_ppn" {{ request('status_ppn_barang') === 'kena_ppn' ? 'selected' : '' }}>
                                    Semua Kena PPN
                                </option>
                                <option value="ppn_normal" {{ request('status_ppn_barang') === 'ppn_normal' ? 'selected' : '' }}>
                                    PPN Normal
                                </option>
                                <option value="ppn_dpp_nilai_lain" {{ request('status_ppn_barang') === 'ppn_dpp_nilai_lain' ? 'selected' : '' }}>
                                    PPN DPP Nilai Lain
                                </option>
                                <option value="non_ppn" {{ request('status_ppn_barang') === 'non_ppn' ? 'selected' : '' }}>
                                    Non PPN
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status Barang</label>
                            <select name="status_barang" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="1" {{ request('status_barang') === '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="0" {{ request('status_barang') === '0' ? 'selected' : '' }}>
                                    Nonaktif
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 font-medium">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Barang / sumber transaksi / keterangan / admin..."
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
                    <p class="text-2xl font-bold">
                        {{ number_format($totalData ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang unik: {{ number_format($totalBarangUnik ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Masuk</p>
                    <p class="text-2xl font-bold text-green-700">
                        +{{ number_format($totalMasuk ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($totalTransaksiMasuk ?? 0, 0, ',', '.') }} transaksi masuk
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Barang Keluar</p>
                    <p class="text-2xl font-bold text-red-700">
                        -{{ number_format($totalKeluar ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($totalTransaksiKeluar ?? 0, 0, ',', '.') }} transaksi keluar
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Netto Perubahan Stok</p>
                    <p class="text-2xl font-bold {{ $nettoPerubahan >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                        {{ $nettoPerubahan > 0 ? '+' : '' }}{{ number_format($nettoPerubahan, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Selisih stok sesudah - stok sebelum.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Jumlah Penyesuaian</p>
                    <p class="text-2xl font-bold text-yellow-700">
                        {{ number_format($totalPenyesuaian ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Qty penyesuaian: {{ number_format($totalJumlahPenyesuaian ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Stock Opname</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ number_format($totalOpname ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Non-opname: {{ number_format($totalNonOpname ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Selisih Bertambah</p>
                    <p class="text-2xl font-bold text-green-700">
                        +{{ number_format($totalSelisihPlus ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Selisih Berkurang</p>
                    <p class="text-2xl font-bold text-red-700">
                        -{{ number_format($totalSelisihMinus ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tipe Harga Normal</p>
                    <p class="text-2xl font-bold">
                        {{ number_format($totalBarangNormal ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Berdasarkan baris riwayat.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tipe Isi Kemasan</p>
                    <p class="text-2xl font-bold text-purple-700">
                        {{ number_format($totalBarangIsiKemasan ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Berdasarkan baris riwayat.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Kena PPN</p>
                    <p class="text-2xl font-bold text-green-700">
                        {{ number_format($totalBarangKenaPpn ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        PPN normal + PPN DPP nilai lain.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Non PPN</p>
                    <p class="text-2xl font-bold text-gray-700">
                        {{ number_format($totalBarangNonPpn ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Berdasarkan baris riwayat.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang PPN Normal</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ number_format($totalBarangPpnNormal ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang dengan PPN standar pada riwayat stok.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang PPN DPP Nilai Lain</p>
                    <p class="text-2xl font-bold text-purple-700">
                        {{ number_format($totalBarangPpnDppNilaiLain ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang dengan perlakuan PPN khusus / nilai lain.
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
                                <th class="border px-3 py-2 text-left">Barang</th>
                                <th class="border px-3 py-2 text-center">Jenis / Tipe</th>
                                <th class="border px-3 py-2 text-right">Jumlah</th>
                                <th class="border px-3 py-2 text-right">Stok Sebelum</th>
                                <th class="border px-3 py-2 text-right">Stok Sesudah</th>
                                <th class="border px-3 py-2 text-right">Selisih</th>
                                <th class="border px-3 py-2 text-left">Perhitungan</th>
                                <th class="border px-3 py-2 text-center">PPN</th>
                                <th class="border px-3 py-2 text-left">Sumber</th>
                                <th class="border px-3 py-2 text-left">Keterangan</th>
                                <th class="border px-3 py-2 text-left">Admin</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayatStok as $item)
                            @php
                            $barangItem = $item->barang;
                            $stokSebelum = (int) ($item->stok_sebelum ?? 0);
                            $stokSesudah = (int) ($item->stok_sesudah ?? 0);
                            $selisih = $stokSesudah - $stokSebelum;
                            $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');

                            $tipePerhitungan = $barangItem->tipe_perhitungan_harga ?? 'normal';
                            $satuan = $barangItem->satuan ?? '-';
                            $satuanHitung = $barangItem->satuan_hitung_harga ?? $satuan;
                            $isiPerSatuan = (float) ($barangItem->isi_per_satuan ?? 1);

                            $jenisPpn = $normalisasiJenisPpn($barangItem);
                            $labelPpn = $labelJenisPpn($jenisPpn);
                            $classPpn = $classJenisPpn($jenisPpn);

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
                                    {{ $riwayatStok->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    <div>{{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $item->created_at ? $item->created_at->format('H:i') : '-' }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 min-w-[190px]">
                                    <div class="font-semibold">
                                        {{ $barangItem->kode_barang ?? '-' }}
                                    </div>
                                    <div>
                                        {{ $barangItem->nama_barang ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Satuan: {{ strtoupper($satuan) }}
                                    </div>

                                    @if (!($barangItem->status_aktif ?? true))
                                    <div class="text-xs text-red-600 mt-1">
                                        Barang nonaktif
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $jenisClass }}">
                                        {{ $jenisLabel }}
                                    </span>

                                    <div class="mt-2">
                                        @if ($isOpname)
                                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                            Opname
                                        </span>
                                        @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                            Transaksi
                                        </span>
                                        @endif
                                    </div>
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

                                <td class="border px-3 py-2 min-w-[170px]">
                                    @if ($tipePerhitungan === 'isi_kemasan')
                                    <div class="font-medium text-purple-700">Isi Kemasan</div>
                                    <div class="text-xs text-gray-500">
                                        1 {{ strtoupper($satuan) }} =
                                        {{ rtrim(rtrim(number_format($isiPerSatuan, 3, ',', '.'), '0'), ',') }}
                                        {{ strtoupper($satuanHitung) }}
                                    </div>
                                    @else
                                    <div class="font-medium">Normal</div>
                                    <div class="text-xs text-gray-500">
                                        Per {{ strtoupper($satuan) }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center min-w-[120px]">
                                    <span class="px-2 py-1 text-xs rounded {{ $classPpn }}">
                                        {{ $labelPpn }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 min-w-[160px]">
                                    {{ $item->sumber_transaksi ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 min-w-[220px]">
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