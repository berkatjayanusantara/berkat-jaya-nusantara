<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Stok Barang
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan persediaan barang, kondisi stok, tipe perhitungan harga, status PPN, nilai stok, dan estimasi nilai jual.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.stokBarang.exportExcel', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Export Excel
                </a>

                <a href="{{ route('laporan.stokBarang.exportPdf', request()->query()) }}"
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
    $totalPotensiMargin = $totalEstimasiLabaKotor ?? (($totalEstimasiNilaiJual ?? 0) - ($totalNilaiStok ?? 0));
    $formatAngka = function ($angka) {
    return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
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
                <form method="GET" action="{{ route('laporan.stokBarang') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Status Barang</label>
                            <select name="status_barang"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="1" {{ request('status_barang') === '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="0" {{ request('status_barang') === '0' ? 'selected' : '' }}>
                                    Nonaktif
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Kondisi Stok</label>
                            <select name="kondisi_stok"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="kosong" {{ request('kondisi_stok') === 'kosong' ? 'selected' : '' }}>
                                    Stok Kosong
                                </option>
                                <option value="rendah" {{ request('kondisi_stok') === 'rendah' ? 'selected' : '' }}>
                                    Stok Rendah
                                </option>
                                <option value="tersedia" {{ request('kondisi_stok') === 'tersedia' ? 'selected' : '' }}>
                                    Stok Tersedia
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tipe Harga</label>
                            <select name="tipe_harga"
                                class="w-full border-gray-300 rounded-md shadow-sm">
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
                            <label class="block mb-1 font-medium">Status PPN</label>
                            <select name="status_ppn"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="kena_ppn" {{ request('status_ppn') === 'kena_ppn' ? 'selected' : '' }}>
                                    Kena PPN
                                </option>
                                <option value="non_ppn" {{ request('status_ppn') === 'non_ppn' ? 'selected' : '' }}>
                                    Non PPN
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Batas Stok Rendah</label>
                            <input type="number"
                                name="batas_stok_rendah"
                                value="{{ request('batas_stok_rendah', $batasStokRendah ?? 5) }}"
                                min="1"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block mb-1 font-medium">Cari Barang</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Kode/nama/satuan/keterangan..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('laporan.stokBarang') }}"
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
                    <p class="text-sm text-gray-500">Total Jenis Barang</p>
                    <p class="text-2xl font-bold">{{ $totalBarang ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Aktif: {{ $totalBarangAktif ?? 0 }} | Nonaktif: {{ $totalBarangNonaktif ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Stok</p>
                    <p class="text-2xl font-bold">
                        {{ number_format($totalStok ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Qty harga: {{ number_format($totalJumlahSatuanHarga ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Kosong</p>
                    <p class="text-2xl font-bold text-red-700">
                        {{ $totalBarangKosong ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Stok rendah: {{ $totalBarangStokRendah ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Stok Tersedia</p>
                    <p class="text-2xl font-bold text-green-700">
                        {{ $totalBarangTersedia ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Batas rendah: {{ $batasStokRendah ?? 5 }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Tipe Harga</p>
                    <p class="text-xl font-bold">
                        Normal: {{ $totalBarangNormal ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Isi kemasan: {{ $totalBarangIsiKemasan ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status PPN</p>
                    <p class="text-xl font-bold">
                        Kena PPN: {{ $totalBarangKenaPpn ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Non PPN: {{ $totalBarangNonPpn ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Estimasi Nilai Stok</p>
                    <p class="text-xl font-bold">
                        Rp {{ number_format($totalNilaiStok ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Stok saat ini × harga beli terakhir.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Estimasi Nilai Jual</p>
                    <p class="text-xl font-bold text-green-700">
                        Rp {{ number_format($totalEstimasiNilaiJual ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Mengikuti tipe harga normal / isi kemasan.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Estimasi Margin Kotor</p>
                    <p class="text-2xl font-bold {{ $totalPotensiMargin >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                        Rp {{ number_format($totalPotensiMargin, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Estimasi nilai jual dikurangi estimasi nilai stok. Nilai ini hanya perkiraan, bukan laba final.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Kode</th>
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-center">Satuan</th>
                                <th class="border px-3 py-2 text-left">Perhitungan Harga</th>
                                <th class="border px-3 py-2 text-center">PPN</th>
                                <th class="border px-3 py-2 text-center">Stok</th>
                                <th class="border px-3 py-2 text-center">Qty Harga</th>
                                <th class="border px-3 py-2 text-right">Harga Beli</th>
                                <th class="border px-3 py-2 text-right">Nilai Stok</th>
                                <th class="border px-3 py-2 text-right">Harga Jual</th>
                                <th class="border px-3 py-2 text-right">Estimasi Jual</th>
                                <th class="border px-3 py-2 text-right">Margin</th>
                                <th class="border px-3 py-2 text-center">Status Stok</th>
                                <th class="border px-3 py-2 text-center">Status Barang</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($barang as $item)
                            @php
                            $stokSaatIni = (float) ($item->stok_saat_ini ?? 0);
                            $hargaBeli = (float) ($item->harga_beli_terakhir ?? 0);
                            $hargaJual = (float) ($item->harga_jual_default ?? 0);

                            $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
                            $satuan = $item->satuan ?? '-';
                            $satuanHitung = $item->satuan_hitung_harga ?? $satuan;
                            $isiPerSatuan = $tipePerhitungan === 'isi_kemasan' ? (float) ($item->isi_per_satuan ?? 1) : 1;
                            $jumlahSatuanHarga = $stokSaatIni * $isiPerSatuan;

                            $nilaiStok = $stokSaatIni * $hargaBeli;
                            $estimasiNilaiJual = $jumlahSatuanHarga * $hargaJual;
                            $estimasiMargin = $estimasiNilaiJual - $nilaiStok;
                            $kenaPpn = (bool) ($item->kena_ppn ?? true);

                            if ($stokSaatIni <= 0) {
                                $statusStok='Kosong' ;
                                $statusClass='bg-red-100 text-red-700' ;
                                $rowClass='bg-red-50' ;
                                } elseif ($stokSaatIni <=($batasStokRendah ?? 5)) {
                                $statusStok='Stok Rendah' ;
                                $statusClass='bg-yellow-100 text-yellow-700' ;
                                $rowClass='bg-yellow-50' ;
                                } else {
                                $statusStok='Tersedia' ;
                                $statusClass='bg-green-100 text-green-700' ;
                                $rowClass='' ;
                                }
                                @endphp

                                <tr class="{{ $rowClass }}">
                                <td class="border px-3 py-2">
                                    {{ $barang->firstItem() + $loop->index }}
                                </td>

                                <td class="border px-3 py-2 font-semibold">
                                    {{ $item->kode_barang }}
                                </td>

                                <td class="border px-3 py-2 min-w-[180px]">
                                    <div class="font-medium">
                                        {{ $item->nama_barang }}
                                    </div>

                                    @if ($item->keterangan)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->keterangan }}
                                    </div>
                                    @endif

                                    @if (!$item->status_aktif)
                                    <div class="text-xs text-red-600 mt-1">
                                        Barang nonaktif
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ strtoupper($satuan) }}
                                </td>

                                <td class="border px-3 py-2 min-w-[170px]">
                                    @if ($tipePerhitungan === 'isi_kemasan')
                                    <div class="font-medium">
                                        Isi Kemasan
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        1 {{ strtoupper($satuan) }} =
                                        {{ $formatAngka($isiPerSatuan) }}
                                        {{ strtoupper($satuanHitung) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Harga jual per {{ strtoupper($satuanHitung) }}.
                                    </div>
                                    @else
                                    <div class="font-medium">
                                        Normal
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Harga jual per {{ strtoupper($satuan) }}.
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($kenaPpn)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        Kena
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                        Non
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center font-semibold">
                                    {{ number_format($stokSaatIni, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ $formatAngka($jumlahSatuanHarga) }}
                                    <div class="text-xs text-gray-500">
                                        {{ strtoupper($tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuan) }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($hargaBeli, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($nilaiStok, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                    <div class="text-xs text-gray-500">
                                        / {{ strtoupper($tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuan) }}
                                    </div>
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($estimasiNilaiJual, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold {{ $estimasiMargin >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                                    Rp {{ number_format($estimasiMargin, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">
                                        {{ $statusStok }}
                                    </span>
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->status_aktif)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Aktif
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                        Nonaktif
                                    </span>
                                    @endif
                                </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="border px-3 py-6 text-center text-gray-500">
                                        Data stok barang belum tersedia.
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $barang->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>