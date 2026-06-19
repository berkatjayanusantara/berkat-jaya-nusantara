<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Stock Opname
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Penyesuaian stok barang berdasarkan hasil pengecekan fisik di gudang.
                </p>
            </div>

            <a href="{{ route('riwayat-stok.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900 transition">
                Lihat Riwayat Stok
            </a>
        </div>
    </x-slot>

    @php
    $tanggalDefault = old('tanggal', date('Y-m-d'));
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-md">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-md">
                <div class="font-semibold mb-2">
                    Terjadi kesalahan:
                </div>

                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Aktif Terfilter</p>
                    <p class="text-2xl font-bold">{{ number_format($totalBarang ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                        Normal: {{ $totalNormal ?? 0 }} | Isi kemasan: {{ $totalIsiKemasan ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Stok Sistem</p>
                    <p class="text-2xl font-bold text-blue-700">
                        {{ number_format($totalStokSistem ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Kondisi Stok</p>
                    <p class="text-xl font-bold">
                        Kosong: {{ $totalStokKosong ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Rendah: {{ $totalStokRendah ?? 0 }} | Tersedia: {{ $totalStokTersedia ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Status PPN Barang</p>
                    <p class="text-xl font-bold">
                        PPN: {{ $totalKenaPpn ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Non PPN: {{ $totalNonPpn ?? 0 }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="mb-5">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Opname Satu Barang
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Cocok untuk koreksi cepat pada satu barang tertentu.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('stock-opname.store') }}" class="space-y-4" id="singleOpnameForm">
                            @csrf
                            <input type="hidden" name="mode_opname" value="single">

                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Opname <span class="text-red-500">*</span>
                                </label>

                                <input type="date"
                                    id="tanggal"
                                    name="tanggal"
                                    value="{{ $tanggalDefault }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>

                                @error('tanggal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="id_barang" class="block text-sm font-medium text-gray-700 mb-1">
                                    Barang <span class="text-red-500">*</span>
                                </label>

                                <select id="id_barang"
                                    name="id_barang"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                    <option value="">Pilih Barang</option>

                                    @foreach ($semuaBarangAktif as $item)
                                    @php
                                    $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
                                    $satuan = $item->satuan ?? '-';
                                    $satuanHitung = $item->satuan_hitung_harga ?? $satuan;
                                    $isiPerSatuan = (float) ($item->isi_per_satuan ?? 1);
                                    @endphp
                                    <option value="{{ $item->id_barang }}"
                                        data-kode="{{ $item->kode_barang }}"
                                        data-nama="{{ $item->nama_barang }}"
                                        data-stok="{{ $item->stok_saat_ini }}"
                                        data-satuan="{{ $satuan }}"
                                        data-tipe-harga="{{ $tipePerhitungan }}"
                                        data-satuan-hitung="{{ $satuanHitung }}"
                                        data-isi-per-satuan="{{ $isiPerSatuan }}"
                                        data-kena-ppn="{{ ($item->kena_ppn ?? true) ? '1' : '0' }}"
                                        {{ (string) old('id_barang') === (string) $item->id_barang ? 'selected' : '' }}>
                                        {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('id_barang')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="stok_sistem" class="block text-sm font-medium text-gray-700 mb-1">
                                        Stok Sistem
                                    </label>

                                    <input type="number"
                                        id="stok_sistem"
                                        value=""
                                        class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-700"
                                        readonly>
                                </div>

                                <div>
                                    <label for="satuan_barang" class="block text-sm font-medium text-gray-700 mb-1">
                                        Satuan
                                    </label>

                                    <input type="text"
                                        id="satuan_barang"
                                        value=""
                                        class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-700"
                                        readonly>
                                </div>
                            </div>

                            <div id="infoBarangSingle" class="hidden p-3 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-700 space-y-1">
                                <div>
                                    <span class="font-semibold">Perhitungan Harga:</span>
                                    <span id="tipeHargaSingle">-</span>
                                </div>
                                <div>
                                    <span class="font-semibold">Status PPN:</span>
                                    <span id="statusPpnSingle">-</span>
                                </div>
                            </div>

                            <div>
                                <label for="stok_fisik" class="block text-sm font-medium text-gray-700 mb-1">
                                    Stok Fisik <span class="text-red-500">*</span>
                                </label>

                                <input type="number"
                                    id="stok_fisik"
                                    name="stok_fisik"
                                    value="{{ old('stok_fisik') }}"
                                    min="0"
                                    step="1"
                                    placeholder="Masukkan hasil hitung fisik"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>

                                @error('stok_fisik')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="selisih_stok" class="block text-sm font-medium text-gray-700 mb-1">
                                        Selisih
                                    </label>

                                    <input type="text"
                                        id="selisih_stok"
                                        value=""
                                        class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-700"
                                        readonly>
                                </div>

                                <div>
                                    <label for="status_selisih" class="block text-sm font-medium text-gray-700 mb-1">
                                        Status
                                    </label>

                                    <input type="text"
                                        id="status_selisih"
                                        value=""
                                        class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-700"
                                        readonly>
                                </div>
                            </div>

                            <div>
                                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">
                                    Keterangan
                                </label>

                                <textarea id="keterangan"
                                    name="keterangan"
                                    rows="4"
                                    placeholder="Contoh: Hasil pengecekan fisik rak gudang utama."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>

                                @error('keterangan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                <div class="text-sm text-yellow-800">
                                    <span class="font-semibold">Catatan:</span>
                                    Sistem akan mengunci data barang saat menyimpan agar stok tidak bentrok dengan transaksi lain.
                                    Riwayat stok tetap tercatat meskipun selisihnya 0.
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                <button type="submit"
                                    class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                    Simpan Satu Barang
                                </button>

                                <button type="reset"
                                    id="resetForm"
                                    class="inline-flex justify-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Rekomendasi Alur Opname
                        </h3>
                        <div class="mt-3 text-sm text-gray-600 space-y-2">
                            <p>1. Filter barang sesuai rak/kategori pencarian.</p>
                            <p>2. Isi stok fisik pada daftar barang.</p>
                            <p>3. Centang hanya barang yang sudah benar-benar dihitung.</p>
                            <p>4. Simpan massal agar satu sesi opname memakai nomor sumber transaksi yang sama.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Opname Massal Barang Aktif
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Isi stok fisik untuk beberapa barang sekaligus pada halaman ini, lalu simpan dalam satu nomor opname.
                                </p>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('stock-opname.create') }}" class="mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Cari Barang
                                    </label>
                                    <input type="text"
                                        name="search"
                                        value="{{ $search }}"
                                        placeholder="Cari kode, nama, satuan, keterangan..."
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Kondisi Stok
                                    </label>
                                    <select name="filter_stok" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua</option>
                                        <option value="kosong" {{ $filterStok === 'kosong' ? 'selected' : '' }}>Stok Kosong</option>
                                        <option value="rendah" {{ $filterStok === 'rendah' ? 'selected' : '' }}>Stok Rendah</option>
                                        <option value="tersedia" {{ $filterStok === 'tersedia' ? 'selected' : '' }}>Stok Tersedia</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Tipe Harga
                                    </label>
                                    <select name="filter_tipe_harga" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua</option>
                                        <option value="normal" {{ $filterTipeHarga === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="isi_kemasan" {{ $filterTipeHarga === 'isi_kemasan' ? 'selected' : '' }}>Isi Kemasan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Status PPN
                                    </label>
                                    <select name="filter_ppn" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Semua</option>
                                        <option value="kena_ppn" {{ $filterPpn === 'kena_ppn' ? 'selected' : '' }}>Kena PPN</option>
                                        <option value="non_ppn" {{ $filterPpn === 'non_ppn' ? 'selected' : '' }}>Non PPN</option>
                                    </select>
                                </div>

                                <div class="md:col-span-3 flex items-end justify-end gap-2">
                                    <a href="{{ route('stock-opname.create') }}"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                        Reset
                                    </a>

                                    <button type="submit"
                                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition">
                                        Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('stock-opname.store') }}" id="batchOpnameForm">
                            @csrf
                            <input type="hidden" name="mode_opname" value="batch">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                <div>
                                    <label for="tanggal_batch" class="block text-sm font-medium text-gray-700 mb-1">
                                        Tanggal Opname Massal <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date"
                                        id="tanggal_batch"
                                        name="tanggal"
                                        value="{{ $tanggalDefault }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                </div>

                                <div>
                                    <label for="keterangan_batch" class="block text-sm font-medium text-gray-700 mb-1">
                                        Keterangan Massal
                                    </label>
                                    <input type="text"
                                        id="keterangan_batch"
                                        name="keterangan"
                                        value="{{ old('keterangan') }}"
                                        maxlength="500"
                                        placeholder="Contoh: Opname rak gudang A"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                <div class="text-sm text-gray-600">
                                    Pilih barang yang akan diproses. Baris yang diisi stok fisik akan otomatis dicentang.
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                        id="centangSemuaBerubah"
                                        class="px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded-md hover:bg-blue-200 transition">
                                        Centang yang Berubah
                                    </button>

                                    <button type="button"
                                        id="hapusCentangSemua"
                                        class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200 transition">
                                        Hapus Centang
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-200 text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border px-3 py-2 text-left whitespace-nowrap">No</th>
                                            <th class="border px-3 py-2 text-left whitespace-nowrap">Barang</th>
                                            <th class="border px-3 py-2 text-center whitespace-nowrap">Satuan</th>
                                            <th class="border px-3 py-2 text-left whitespace-nowrap">Perhitungan</th>
                                            <th class="border px-3 py-2 text-center whitespace-nowrap">PPN</th>
                                            <th class="border px-3 py-2 text-right whitespace-nowrap">Stok Sistem</th>
                                            <th class="border px-3 py-2 text-right whitespace-nowrap">Stok Fisik</th>
                                            <th class="border px-3 py-2 text-right whitespace-nowrap">Selisih</th>
                                            <th class="border px-3 py-2 text-center whitespace-nowrap">Proses</th>
                                            <th class="border px-3 py-2 text-center whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($barang as $item)
                                        @php
                                        $index = $loop->index;
                                        $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
                                        $satuan = $item->satuan ?? '-';
                                        $satuanHitung = $item->satuan_hitung_harga ?? $satuan;
                                        $isiPerSatuan = (float) ($item->isi_per_satuan ?? 1);
                                        $kenaPpn = (bool) ($item->kena_ppn ?? true);
                                        $stokSaatIni = (int) ($item->stok_saat_ini ?? 0);
                                        @endphp

                                        <tr class="hover:bg-gray-50 batch-row" data-stok="{{ $stokSaatIni }}">
                                            <td class="border px-3 py-2">
                                                {{ $loop->iteration + ($barang->currentPage() - 1) * $barang->perPage() }}
                                                <input type="hidden" name="items[{{ $index }}][id_barang]" value="{{ $item->id_barang }}">
                                            </td>

                                            <td class="border px-3 py-2 min-w-[220px]">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->kode_barang }}
                                                </div>
                                                <div>
                                                    {{ $item->nama_barang }}
                                                </div>
                                                @if ($item->keterangan)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $item->keterangan }}
                                                </div>
                                                @endif
                                            </td>

                                            <td class="border px-3 py-2 text-center">
                                                {{ strtoupper($satuan) }}
                                            </td>

                                            <td class="border px-3 py-2 min-w-[160px]">
                                                @if ($tipePerhitungan === 'isi_kemasan')
                                                <div class="font-medium">Isi Kemasan</div>
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

                                            <td class="border px-3 py-2 text-center">
                                                @if ($kenaPpn)
                                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">PPN</span>
                                                @else
                                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Non PPN</span>
                                                @endif
                                            </td>

                                            <td class="border px-3 py-2 text-right font-semibold">
                                                {{ number_format($stokSaatIni, 0, ',', '.') }}
                                            </td>

                                            <td class="border px-3 py-2 text-right">
                                                <input type="number"
                                                    name="items[{{ $index }}][stok_fisik]"
                                                    min="0"
                                                    step="1"
                                                    class="batch-stok-fisik w-28 border-gray-300 rounded-md shadow-sm text-right focus:border-blue-500 focus:ring-blue-500"
                                                    data-stok-sistem="{{ $stokSaatIni }}"
                                                    placeholder="0">
                                            </td>

                                            <td class="border px-3 py-2 text-right font-semibold">
                                                <span class="batch-selisih text-gray-500">-</span>
                                            </td>

                                            <td class="border px-3 py-2 text-center">
                                                <input type="hidden" name="items[{{ $index }}][diproses]" value="0">
                                                <input type="checkbox"
                                                    name="items[{{ $index }}][diproses]"
                                                    value="1"
                                                    class="batch-diproses rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            </td>

                                            <td class="border px-3 py-2 text-center whitespace-nowrap">
                                                <button type="button"
                                                    class="pilih-barang inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-md hover:bg-blue-200 transition"
                                                    data-id="{{ $item->id_barang }}"
                                                    data-stok="{{ $stokSaatIni }}">
                                                    Pilih
                                                </button>

                                                <button type="button"
                                                    class="samakan-stok inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-md hover:bg-gray-200 transition mt-1 md:mt-0"
                                                    data-stok="{{ $stokSaatIni }}">
                                                    Samakan
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="border px-3 py-4 text-center text-gray-500">
                                                Data barang aktif belum tersedia.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    {{ $barang->links() }}
                                </div>

                                <button type="submit"
                                    class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                    Simpan Opname Massal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const barangSelect = document.getElementById('id_barang');
            const stokSistemInput = document.getElementById('stok_sistem');
            const satuanBarangInput = document.getElementById('satuan_barang');
            const stokFisikInput = document.getElementById('stok_fisik');
            const selisihStokInput = document.getElementById('selisih_stok');
            const statusSelisihInput = document.getElementById('status_selisih');
            const resetFormButton = document.getElementById('resetForm');
            const pilihBarangButtons = document.querySelectorAll('.pilih-barang');
            const samakanStokButtons = document.querySelectorAll('.samakan-stok');
            const singleOpnameForm = document.getElementById('singleOpnameForm');
            const batchOpnameForm = document.getElementById('batchOpnameForm');
            const infoBarangSingle = document.getElementById('infoBarangSingle');
            const tipeHargaSingle = document.getElementById('tipeHargaSingle');
            const statusPpnSingle = document.getElementById('statusPpnSingle');
            const centangSemuaBerubah = document.getElementById('centangSemuaBerubah');
            const hapusCentangSemua = document.getElementById('hapusCentangSemua');

            function formatInteger(value) {
                const number = parseInt(value || 0, 10);
                return Number.isNaN(number) ? 0 : number;
            }

            function labelTipeHarga(option) {
                const tipe = option.dataset.tipeHarga || 'normal';
                const satuan = (option.dataset.satuan || '-').toUpperCase();
                const satuanHitung = (option.dataset.satuanHitung || satuan).toUpperCase();
                const isiPerSatuan = parseFloat(option.dataset.isiPerSatuan || 1);

                if (tipe === 'isi_kemasan') {
                    return `Isi Kemasan - 1 ${satuan} = ${isiPerSatuan} ${satuanHitung}`;
                }

                return `Normal - per ${satuan}`;
            }

            function updateInfoBarang() {
                const selectedOption = barangSelect.options[barangSelect.selectedIndex];

                if (!selectedOption || !selectedOption.value) {
                    stokSistemInput.value = '';
                    satuanBarangInput.value = '';
                    infoBarangSingle.classList.add('hidden');
                    hitungSelisih();
                    return;
                }

                stokSistemInput.value = selectedOption.dataset.stok ?? 0;
                satuanBarangInput.value = selectedOption.dataset.satuan ?? '-';
                tipeHargaSingle.textContent = labelTipeHarga(selectedOption);
                statusPpnSingle.textContent = selectedOption.dataset.kenaPpn === '0' ? 'Non PPN' : 'Kena PPN';
                infoBarangSingle.classList.remove('hidden');

                hitungSelisih();
            }

            function hitungSelisih() {
                const stokSistem = formatInteger(stokSistemInput.value);
                const stokFisik = stokFisikInput.value === '' ? null : formatInteger(stokFisikInput.value);

                if (stokFisik === null || Number.isNaN(stokFisik)) {
                    selisihStokInput.value = '';
                    statusSelisihInput.value = '';
                    return;
                }

                const selisih = stokFisik - stokSistem;
                selisihStokInput.value = selisih > 0 ? `+${selisih}` : selisih;

                if (selisih > 0) {
                    statusSelisihInput.value = 'Stok bertambah';
                } else if (selisih < 0) {
                    statusSelisihInput.value = 'Stok berkurang';
                } else {
                    statusSelisihInput.value = 'Stok sama';
                }
            }

            function updateBatchRow(input) {
                const row = input.closest('.batch-row');
                const stokSistem = formatInteger(input.dataset.stokSistem);
                const stokFisikValue = input.value;
                const selisihElement = row.querySelector('.batch-selisih');
                const checkbox = row.querySelector('.batch-diproses');

                selisihElement.classList.remove('text-green-700', 'text-red-700', 'text-gray-700', 'text-gray-500');

                if (stokFisikValue === '') {
                    selisihElement.textContent = '-';
                    selisihElement.classList.add('text-gray-500');
                    checkbox.checked = false;
                    return;
                }

                const stokFisik = formatInteger(stokFisikValue);
                const selisih = stokFisik - stokSistem;

                selisihElement.textContent = selisih > 0 ? `+${selisih}` : selisih;

                if (selisih > 0) {
                    selisihElement.classList.add('text-green-700');
                } else if (selisih < 0) {
                    selisihElement.classList.add('text-red-700');
                } else {
                    selisihElement.classList.add('text-gray-700');
                }

                checkbox.checked = true;
            }

            barangSelect.addEventListener('change', updateInfoBarang);
            stokFisikInput.addEventListener('input', hitungSelisih);

            document.querySelectorAll('.batch-stok-fisik').forEach(function(input) {
                input.addEventListener('input', function() {
                    updateBatchRow(this);
                });
            });

            pilihBarangButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const idBarang = this.dataset.id;
                    barangSelect.value = idBarang;
                    updateInfoBarang();

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    stokFisikInput.focus();
                });
            });

            samakanStokButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const row = this.closest('.batch-row');
                    const input = row.querySelector('.batch-stok-fisik');
                    input.value = this.dataset.stok || 0;
                    updateBatchRow(input);
                });
            });

            centangSemuaBerubah.addEventListener('click', function() {
                document.querySelectorAll('.batch-row').forEach(function(row) {
                    const input = row.querySelector('.batch-stok-fisik');
                    const checkbox = row.querySelector('.batch-diproses');
                    const stokSistem = formatInteger(input.dataset.stokSistem);

                    if (input.value !== '' && formatInteger(input.value) !== stokSistem) {
                        checkbox.checked = true;
                    }
                });
            });

            hapusCentangSemua.addEventListener('click', function() {
                document.querySelectorAll('.batch-diproses').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
            });

            resetFormButton.addEventListener('click', function() {
                setTimeout(function() {
                    stokSistemInput.value = '';
                    satuanBarangInput.value = '';
                    selisihStokInput.value = '';
                    statusSelisihInput.value = '';
                    infoBarangSingle.classList.add('hidden');
                }, 10);
            });

            singleOpnameForm.addEventListener('submit', function(event) {
                const barangText = barangSelect.options[barangSelect.selectedIndex]?.text || 'barang terpilih';
                const stokFisik = stokFisikInput.value;
                const konfirmasi = confirm(`Simpan stock opname untuk ${barangText} dengan stok fisik ${stokFisik}? Stok sistem akan langsung diperbarui.`);

                if (!konfirmasi) {
                    event.preventDefault();
                }
            });

            batchOpnameForm.addEventListener('submit', function(event) {
                const jumlahDipilih = document.querySelectorAll('.batch-diproses:checked').length;

                if (jumlahDipilih <= 0) {
                    event.preventDefault();
                    alert('Pilih minimal satu barang untuk diproses pada opname massal.');
                    return;
                }

                const konfirmasi = confirm(`Simpan stock opname massal untuk ${jumlahDipilih} barang? Stok sistem akan langsung diperbarui sesuai stok fisik.`);

                if (!konfirmasi) {
                    event.preventDefault();
                }
            });

            updateInfoBarang();
        });
    </script>
</x-app-layout>