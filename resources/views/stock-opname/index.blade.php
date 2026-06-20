<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Stock Opname
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Penyesuaian stok fisik barang dengan stok yang tercatat di sistem.
                </p>
            </div>
        </div>
    </x-slot>

    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277';

    $formatAngka = function ($angka) {
    return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
    };

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

            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            @if (session('warning'))
            <div class="mb-4 bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded">
                {{ session('warning') }}
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
                <div class="font-semibold mb-1">Terdapat kesalahan:</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

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
                <form method="GET" action="{{ route('stock-opname.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
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

                        <div>
                            <label class="block mb-1 font-medium">Kondisi Stok</label>
                            <select name="kondisi_stok" class="w-full border-gray-300 rounded-md shadow-sm">
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
                            <label class="block mb-1 font-medium">Status PPN</label>
                            <select name="status_ppn" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="kena_ppn" {{ request('status_ppn') === 'kena_ppn' ? 'selected' : '' }}>
                                    Semua Kena PPN
                                </option>
                                <option value="ppn_normal" {{ request('status_ppn') === 'ppn_normal' ? 'selected' : '' }}>
                                    PPN Normal
                                </option>
                                <option value="ppn_dpp_nilai_lain" {{ request('status_ppn') === 'ppn_dpp_nilai_lain' ? 'selected' : '' }}>
                                    PPN DPP Nilai Lain
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
                        <a href="{{ route('stock-opname.index') }}"
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
                        Aktif: {{ $totalBarangAktif ?? 0 }} |
                        Nonaktif: {{ $totalBarangNonaktif ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Stok Sistem</p>
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
                    <p class="text-sm text-gray-500">Barang Tersedia</p>
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
                    <p class="text-sm text-gray-500">Tipe Harga Normal</p>
                    <p class="text-xl font-bold">
                        {{ $totalBarangNormal ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Isi kemasan: {{ $totalBarangIsiKemasan ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Barang Kena PPN</p>
                    <p class="text-xl font-bold text-green-700">
                        {{ $totalBarangKenaPpn ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Non PPN: {{ $totalBarangNonPpn ?? 0 }}
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">PPN Normal</p>
                    <p class="text-xl font-bold text-blue-700">
                        {{ $totalBarangPpnNormal ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang PPN standar.
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">PPN DPP Nilai Lain</p>
                    <p class="text-xl font-bold text-purple-700">
                        {{ $totalBarangPpnDppNilaiLain ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Barang PPN khusus.
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Form Stock Opname
                        </h3>
                        <p class="text-sm text-gray-500">
                            Centang barang yang ingin disesuaikan, lalu isi stok fisik sesuai hasil hitung gudang.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                            id="btnPilihSemua"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Pilih Semua
                        </button>

                        <button type="button"
                            id="btnBatalPilih"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal Pilih
                        </button>

                        <button type="button"
                            id="btnSamakanStok"
                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">
                            Samakan Stok Fisik
                        </button>
                    </div>
                </div>

                <form method="POST"
                    action="{{ route('stock-opname.store') }}"
                    onsubmit="return confirm('Yakin ingin menyimpan stock opname untuk barang yang dipilih? Stok sistem akan diperbarui dan riwayat stok akan dibuat.');">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border px-3 py-2 text-center">Pilih</th>
                                    <th class="border px-3 py-2 text-left">Kode</th>
                                    <th class="border px-3 py-2 text-left">Nama Barang</th>
                                    <th class="border px-3 py-2 text-center">Satuan</th>
                                    <th class="border px-3 py-2 text-left">Perhitungan Harga</th>
                                    <th class="border px-3 py-2 text-center">PPN</th>
                                    <th class="border px-3 py-2 text-center">Stok Sistem</th>
                                    <th class="border px-3 py-2 text-center">Stok Fisik</th>
                                    <th class="border px-3 py-2 text-center">Selisih</th>
                                    <th class="border px-3 py-2 text-left">Catatan</th>
                                    <th class="border px-3 py-2 text-center">Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($barang as $item)
                                @php
                                $stokSaatIni = (int) ($item->stok_saat_ini ?? 0);
                                $tipePerhitungan = $item->tipe_perhitungan_harga ?? 'normal';
                                $satuan = $item->satuan ?? '-';
                                $satuanHitung = $item->satuan_hitung_harga ?? $satuan;
                                $isiPerSatuan = $tipePerhitungan === 'isi_kemasan' ? (float) ($item->isi_per_satuan ?? 1) : 1;

                                $jenisPpn = $normalisasiJenisPpn($item);
                                $labelPpn = $labelJenisPpn($jenisPpn);
                                $classPpn = $classJenisPpn($jenisPpn);

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

                                    $oldStokFisik=old('stok_fisik.' . $item->id_barang, $stokSaatIni);
                                    $isSelected = collect(old('selected', []))->contains((string) $item->id_barang)
                                    || collect(old('selected', []))->contains($item->id_barang);
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td class="border px-3 py-2 text-center">
                                            <input type="checkbox"
                                                name="selected[]"
                                                value="{{ $item->id_barang }}"
                                                class="checkbox-barang rounded border-gray-300"
                                                data-id="{{ $item->id_barang }}"
                                                {{ $isSelected ? 'checked' : '' }}>
                                        </td>

                                        <td class="border px-3 py-2 font-semibold">
                                            {{ $item->kode_barang }}
                                        </td>

                                        <td class="border px-3 py-2 min-w-[190px]">
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
                                            <div class="font-medium text-purple-700">
                                                Isi Kemasan
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                1 {{ strtoupper($satuan) }} =
                                                {{ $formatAngka($isiPerSatuan) }}
                                                {{ strtoupper($satuanHitung) }}
                                            </div>
                                            @else
                                            <div class="font-medium">
                                                Normal
                                            </div>
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

                                        <td class="border px-3 py-2 text-center font-semibold">
                                            <span class="stok-sistem" data-id="{{ $item->id_barang }}">
                                                {{ $stokSaatIni }}
                                            </span>
                                        </td>

                                        <td class="border px-3 py-2 text-center min-w-[120px]">
                                            <input type="number"
                                                name="stok_fisik[{{ $item->id_barang }}]"
                                                value="{{ $oldStokFisik }}"
                                                min="0"
                                                step="1"
                                                class="stok-fisik w-24 border-gray-300 rounded-md shadow-sm text-center"
                                                data-id="{{ $item->id_barang }}"
                                                data-stok-sistem="{{ $stokSaatIni }}">
                                        </td>

                                        <td class="border px-3 py-2 text-center font-semibold min-w-[100px]">
                                            <span class="selisih-stok text-gray-700" data-id="{{ $item->id_barang }}">
                                                0
                                            </span>
                                        </td>

                                        <td class="border px-3 py-2 min-w-[220px]">
                                            <input type="text"
                                                name="keterangan[{{ $item->id_barang }}]"
                                                value="{{ old('keterangan.' . $item->id_barang) }}"
                                                placeholder="Opsional..."
                                                class="w-full border-gray-300 rounded-md shadow-sm">
                                        </td>

                                        <td class="border px-3 py-2 text-center">
                                            <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">
                                                {{ $statusStok }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="border px-3 py-6 text-center text-gray-500">
                                            Data barang belum tersedia untuk stock opname.
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4">
                        <div class="text-sm text-gray-500">
                            Hanya barang yang dicentang yang akan diproses. Barang dengan stok fisik sama tidak akan dibuatkan riwayat baru.
                        </div>

                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Simpan Stock Opname
                        </button>
                    </div>
                </form>

                <div class="mt-4">
                    {{ $barang->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
        function hitungSelisih(input) {
            const id = input.dataset.id;
            const stokSistem = parseInt(input.dataset.stokSistem || 0);
            const stokFisik = parseInt(input.value || 0);
            const selisih = stokFisik - stokSistem;

            const target = document.querySelector('.selisih-stok[data-id="' + id + '"]');

            if (!target) {
                return;
            }

            target.textContent = selisih > 0 ? '+' + selisih : selisih;

            target.classList.remove('text-gray-700', 'text-green-700', 'text-red-700');

            if (selisih > 0) {
                target.classList.add('text-green-700');
            } else if (selisih < 0) {
                target.classList.add('text-red-700');
            } else {
                target.classList.add('text-gray-700');
            }
        }

        document.querySelectorAll('.stok-fisik').forEach(function(input) {
            hitungSelisih(input);

            input.addEventListener('input', function() {
                hitungSelisih(input);

                const checkbox = document.querySelector('.checkbox-barang[data-id="' + input.dataset.id + '"]');

                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        });

        const btnPilihSemua = document.getElementById('btnPilihSemua');
        const btnBatalPilih = document.getElementById('btnBatalPilih');
        const btnSamakanStok = document.getElementById('btnSamakanStok');

        if (btnPilihSemua) {
            btnPilihSemua.addEventListener('click', function() {
                document.querySelectorAll('.checkbox-barang').forEach(function(checkbox) {
                    checkbox.checked = true;
                });
            });
        }

        if (btnBatalPilih) {
            btnBatalPilih.addEventListener('click', function() {
                document.querySelectorAll('.checkbox-barang').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
            });
        }

        if (btnSamakanStok) {
            btnSamakanStok.addEventListener('click', function() {
                document.querySelectorAll('.stok-fisik').forEach(function(input) {
                    input.value = input.dataset.stokSistem || 0;
                    hitungSelisih(input);
                });
            });
        }
    </script>
</x-app-layout>