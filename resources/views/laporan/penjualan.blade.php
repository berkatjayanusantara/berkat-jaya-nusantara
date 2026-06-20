<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Penjualan
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laporan penjualan sistem berjalan dan invoice historis, termasuk mode PPN, faktur pajak, penyesuaian total, detail barang normal/isi kemasan, dan piutang.
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

    $formatRupiah = function ($nilai) {
    return 'Rp ' . number_format((float) ($nilai ?? 0), 0, ',', '.');
    };

    $formatAngka = function ($nilai) {
    return rtrim(rtrim(number_format((float) ($nilai ?? 0), 3, ',', '.'), '0'), ',');
    };

    $normalisasiModePpn = function ($modePpn, $persentasePajak = 0, $pajakDitambahkan = false) {
    if (in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
    return $modePpn;
    }

    if ((float) ($persentasePajak ?? 0) <= 0) {
        return 'tanpa_ppn' ;
        }

        return (bool) $pajakDitambahkan ? 'exclude' : 'include' ;
        };

        $labelModePpn=function ($modePpn) {
        return [ 'tanpa_ppn'=> 'Tanpa PPN',
        'include' => 'PPN Include',
        'exclude' => 'PPN Exclude',
        ][$modePpn] ?? 'Tanpa PPN';
        };

        $labelStatusPembayaran = function ($status) {
        return [
        'lunas' => 'Lunas',
        'sebagian' => 'Sebagian',
        'belum_lunas' => 'Belum Lunas',
        ][$status] ?? 'Belum Lunas';
        };

        $classStatusPembayaran = function ($status) {
        return [
        'lunas' => 'bg-green-100 text-green-700',
        'sebagian' => 'bg-blue-100 text-blue-700',
        'belum_lunas' => 'bg-yellow-100 text-yellow-700',
        ][$status] ?? 'bg-yellow-100 text-yellow-700';
        };

        $labelPenyesuaian = function ($jenis, $nominal) use ($formatRupiah) {
        $nominal = (float) ($nominal ?? 0);
        if ($nominal <= 0 || !$jenis || $jenis==='tidak_ada' ) {
            return 'Tidak ada' ;
            }
            return ($jenis==='tambah' ? 'Tambah ' : 'Kurang ' ) . $formatRupiah($nominal);
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
                    <form method="GET" action="{{ route('laporan.penjualan') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
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
                                <select name="id_customer" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua Customer</option>
                                    @foreach ($customers as $customer)
                                    <option value="{{ $customer->id_customer }}" {{ request('id_customer') == $customer->id_customer ? 'selected' : '' }}>
                                        {{ $customer->nama_customer }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Metode Pembayaran</label>
                                <select name="metode_pembayaran" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="tunai" {{ request('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="kredit" {{ request('metode_pembayaran') === 'kredit' ? 'selected' : '' }}>Kredit / Piutang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Status Pembayaran</label>
                                <select name="status_pembayaran" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    <option value="sebagian" {{ request('status_pembayaran') === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                                    <option value="belum_lunas" {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Tipe Invoice</label>
                                <select name="tipe_invoice" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="sistem" {{ request('tipe_invoice') === 'sistem' ? 'selected' : '' }}>Sistem Berjalan</option>
                                    <option value="historis" {{ request('tipe_invoice') === 'historis' ? 'selected' : '' }}>Historis / Lama</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Pengaruh Stok</label>
                                <select name="pengaruh_stok" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="mempengaruhi" {{ request('pengaruh_stok') === 'mempengaruhi' ? 'selected' : '' }}>Mempengaruhi Stok</option>
                                    <option value="tidak_mempengaruhi" {{ request('pengaruh_stok') === 'tidak_mempengaruhi' ? 'selected' : '' }}>Tidak Mempengaruhi Stok</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Mode PPN</label>
                                <select name="mode_ppn" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="tanpa_ppn" {{ request('mode_ppn') === 'tanpa_ppn' ? 'selected' : '' }}>Tanpa PPN</option>
                                    <option value="include" {{ request('mode_ppn') === 'include' ? 'selected' : '' }}>PPN Include</option>
                                    <option value="exclude" {{ request('mode_ppn') === 'exclude' ? 'selected' : '' }}>PPN Exclude</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Faktur Pajak</label>
                                <select name="butuh_faktur_pajak" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="ya" {{ request('butuh_faktur_pajak') === 'ya' ? 'selected' : '' }}>Butuh Faktur Pajak</option>
                                    <option value="tidak" {{ request('butuh_faktur_pajak') === 'tidak' ? 'selected' : '' }}>Tidak Butuh Faktur</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Penyesuaian Total</label>
                                <select name="jenis_penyesuaian_total" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="tidak_ada" {{ request('jenis_penyesuaian_total') === 'tidak_ada' ? 'selected' : '' }}>Tanpa Penyesuaian</option>
                                    <option value="tambah" {{ request('jenis_penyesuaian_total') === 'tambah' ? 'selected' : '' }}>Penyesuaian Tambah</option>
                                    <option value="kurang" {{ request('jenis_penyesuaian_total') === 'kurang' ? 'selected' : '' }}>Penyesuaian Kurang</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Tipe Harga Barang</label>
                                <select name="tipe_harga" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="normal" {{ request('tipe_harga') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="isi_kemasan" {{ request('tipe_harga') === 'isi_kemasan' ? 'selected' : '' }}>Isi Kemasan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">PPN Detail Barang</label>
                                <select name="status_ppn_detail" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="kena_ppn" {{ request('status_ppn_detail') === 'kena_ppn' ? 'selected' : '' }}>Barang Kena PPN</option>
                                    <option value="non_ppn" {{ request('status_ppn_detail') === 'non_ppn' ? 'selected' : '' }}>Barang Non PPN</option>
                                </select>
                            </div>

                            <div class="md:col-span-3 lg:col-span-4">
                                <label class="block mb-1 font-medium">Cari</label>
                                <input type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Invoice/Dokumen Asli/Customer/NPWP/Nomor Faktur/Barang..."
                                    class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <a href="{{ route('laporan.penjualan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Reset
                            </a>

                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
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
                        <p class="text-2xl font-bold">{{ $formatRupiah($totalSubtotal ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Kena PPN: {{ $formatRupiah($totalSubtotalKenaPpn ?? 0) }}
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total PPN</p>
                        <p class="text-2xl font-bold">{{ $formatRupiah($totalPajak ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            DPP: {{ $formatRupiah($totalDppPpn ?? 0) }}
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total Akhir</p>
                        <p class="text-2xl font-bold text-green-700">{{ $formatRupiah($totalAkhir ?? 0) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Sebelum penyesuaian: {{ $formatRupiah($totalSebelumPenyesuaian ?? 0) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total Tunai</p>
                        <p class="text-xl font-bold">{{ $formatRupiah($totalTunai ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total Kredit</p>
                        <p class="text-xl font-bold">{{ $formatRupiah($totalKredit ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Total Dibayar Piutang</p>
                        <p class="text-xl font-bold text-blue-700">{{ $formatRupiah($totalDibayar ?? 0) }}</p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Sisa Piutang</p>
                        <p class="text-xl font-bold text-red-700">{{ $formatRupiah($totalSisaPiutang ?? 0) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Mode PPN</p>
                        <p class="text-sm font-semibold mt-1">
                            Tanpa: {{ $totalPpnTanpaPpn ?? 0 }} | Include: {{ $totalPpnInclude ?? 0 }} | Exclude: {{ $totalPpnExclude ?? 0 }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            PPN Include: {{ $formatRupiah($totalNilaiPajakInclude ?? 0) }} | Exclude: {{ $formatRupiah($totalNilaiPajakExclude ?? 0) }}
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Faktur Pajak</p>
                        <p class="text-sm font-semibold mt-1">
                            Butuh: {{ $totalButuhFakturPajak ?? 0 }} | Tidak: {{ $totalTidakButuhFakturPajak ?? 0 }}
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Penyesuaian Total</p>
                        <p class="text-sm font-semibold mt-1">
                            Tambah: {{ $formatRupiah($totalPenyesuaianTambah ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Kurang: {{ $formatRupiah($totalPenyesuaianKurang ?? 0) }} | Bersih: {{ $formatRupiah($totalPenyesuaianBersih ?? 0) }}
                        </p>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <p class="text-sm text-gray-500">Detail Barang</p>
                        <p class="text-sm font-semibold mt-1">
                            Baris: {{ $totalItemBarang ?? 0 }} | Qty: {{ $formatAngka($totalJumlahTerjual ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Normal: {{ $totalBarangNormal ?? 0 }} | Isi Kemasan: {{ $totalBarangIsiKemasan ?? 0 }}
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
                                    <th class="border px-3 py-2 text-left">Pembayaran</th>
                                    <th class="border px-3 py-2 text-left">Tipe</th>
                                    <th class="border px-3 py-2 text-left">PPN / Faktur</th>
                                    <th class="border px-3 py-2 text-left">Penyesuaian</th>
                                    <th class="border px-3 py-2 text-left">Detail Barang</th>
                                    <th class="border px-3 py-2 text-right">Subtotal</th>
                                    <th class="border px-3 py-2 text-right">PPN</th>
                                    <th class="border px-3 py-2 text-right">Total</th>
                                    <th class="border px-3 py-2 text-right">Sisa Piutang</th>
                                    <th class="border px-3 py-2 text-center">Detail</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($penjualan as $item)
                                @php
                                $isHistoris = (bool) ($item->is_historical ?? false);
                                $affectStock = (bool) ($item->affect_stock ?? true);
                                $modePpn = $normalisasiModePpn($item->mode_ppn ?? null, $item->persentase_pajak ?? 0, $item->pajak_ditambahkan ?? false);
                                $jenisPenyesuaian = $item->jenis_penyesuaian_total ?? 'tidak_ada';
                                $nominalPenyesuaian = (float) ($item->nominal_penyesuaian_total ?? 0);

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
                                    <td class="border px-3 py-2 align-top">
                                        {{ $penjualan->firstItem() + $loop->index }}
                                    </td>

                                    <td class="border px-3 py-2 align-top whitespace-nowrap">
                                        {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        @php
                                        $nomorInvoiceTampil = $isHistoris && !empty($item->nomor_dokumen_asli)
                                        ? $item->nomor_dokumen_asli
                                        : $item->nomor_invoice;
                                        @endphp

                                        <div class="font-semibold">{{ $nomorInvoiceTampil }}</div>

                                        @if (!$isHistoris && $item->nomor_dokumen_asli)
                                        <div class="text-xs text-gray-500 mt-1">Dok. Asli: {{ $item->nomor_dokumen_asli }}</div>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <div class="font-medium">{{ $item->customer->nama_customer ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->customer->nomor_telepon ?? '-' }}</div>
                                        @if ($item->customer?->npwp)
                                        <div class="text-xs text-gray-500">NPWP: {{ $item->customer->npwp }}</div>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <div>{{ ucfirst($item->metode_pembayaran ?? '-') }}</div>
                                        <span class="inline-block mt-1 px-2 py-1 text-xs rounded {{ $classStatusPembayaran($item->status_pembayaran) }}">
                                            {{ $labelStatusPembayaran($item->status_pembayaran) }}
                                        </span>
                                        @if ($item->tanggal_jatuh_tempo)
                                        <div class="text-xs text-gray-500 mt-1">JT: {{ $item->tanggal_jatuh_tempo->format('d-m-Y') }}</div>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        @if ($isHistoris)
                                        <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">Historis</span>
                                        @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">Sistem</span>
                                        @endif

                                        <div class="mt-2">
                                            @if ($affectStock)
                                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Mempengaruhi Stok</span>
                                            @else
                                            <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">Tidak Mempengaruhi Stok</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <div class="font-medium">{{ $labelModePpn($modePpn) }}</div>
                                        <div class="text-xs text-gray-500">DPP: {{ $formatRupiah($item->dpp_ppn ?? 0) }}</div>
                                        <div class="text-xs text-gray-500">Kena PPN: {{ $formatRupiah($item->subtotal_kena_ppn ?? 0) }}</div>
                                        <div class="text-xs text-gray-500">Non PPN: {{ $formatRupiah($item->subtotal_non_ppn ?? 0) }}</div>

                                        @if ($item->butuh_faktur_pajak)
                                        <div class="mt-1 text-xs text-blue-700 font-semibold">Butuh Faktur</div>
                                        @if ($item->nomor_faktur_pajak)
                                        <div class="text-xs text-gray-500">No: {{ $item->nomor_faktur_pajak }}</div>
                                        @endif
                                        @else
                                        <div class="mt-1 text-xs text-gray-500">Tidak butuh faktur</div>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 align-top">
                                        <div>{{ $labelPenyesuaian($jenisPenyesuaian, $nominalPenyesuaian) }}</div>
                                        @if ($nominalPenyesuaian > 0 && $item->keterangan_penyesuaian_total)
                                        <div class="text-xs text-gray-500 mt-1">{{ $item->keterangan_penyesuaian_total }}</div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">
                                            Sebelum: {{ $formatRupiah($item->total_sebelum_penyesuaian ?? $item->total_akhir ?? 0) }}
                                        </div>
                                    </td>

                                    <td class="border px-3 py-2 align-top min-w-80">
                                        @forelse ($item->detailPenjualan as $detail)
                                        @php
                                        $tipeHarga = $detail->tipe_perhitungan_harga ?? 'normal';
                                        $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '-');
                                        $satuanHitungHarga = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                                        $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                                        $kenaPpnDetail = (bool) ($detail->kena_ppn ?? false);
                                        $rumus = $tipeHarga === 'isi_kemasan'
                                        ? $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatAngka($isiPerSatuan) . ' ' . $satuanHitungHarga . ' x ' . $formatRupiah($detail->harga_jual)
                                        : $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatRupiah($detail->harga_jual);
                                        @endphp

                                        <div class="pb-2 mb-2 border-b border-gray-100 last:border-b-0 last:mb-0 last:pb-0">
                                            <div class="font-medium">
                                                {{ $detail->barang->kode_barang ?? '-' }} - {{ $detail->barang->nama_barang ?? '-' }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                {{ $rumus }} = {{ $formatRupiah($detail->subtotal ?? 0) }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Tipe: {{ $tipeHarga === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal' }} |
                                                {{ $kenaPpnDetail ? 'Kena PPN' : 'Non PPN' }} |
                                                DPP: {{ $formatRupiah($detail->dpp_ppn ?? 0) }} |
                                                PPN: {{ $formatRupiah($detail->nilai_ppn ?? 0) }}
                                            </div>
                                        </div>
                                        @empty
                                        <span class="text-gray-500">Detail barang tidak tersedia.</span>
                                        @endforelse
                                    </td>

                                    <td class="border px-3 py-2 align-top text-right whitespace-nowrap">
                                        {{ $formatRupiah($item->subtotal ?? 0) }}
                                    </td>

                                    <td class="border px-3 py-2 align-top text-right whitespace-nowrap">
                                        {{ $formatRupiah($item->nilai_pajak ?? 0) }}
                                    </td>

                                    <td class="border px-3 py-2 align-top text-right font-semibold whitespace-nowrap">
                                        {{ $formatRupiah($item->total_akhir ?? 0) }}
                                    </td>

                                    <td class="border px-3 py-2 align-top text-right whitespace-nowrap">
                                        @if ($item->piutang)
                                        <div>{{ $formatRupiah($item->piutang->sisa_piutang ?? 0) }}</div>
                                        <div class="text-xs text-gray-500">Dibayar: {{ $formatRupiah($item->piutang->total_dibayar ?? 0) }}</div>
                                        @else
                                        -
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2 align-top text-center">
                                        <a href="{{ $detailRoute }}" class="text-blue-600 hover:underline">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="14" class="border px-3 py-6 text-center text-gray-500">
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