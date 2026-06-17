<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Invoice Penjualan Lama
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md">
                <strong>Catatan:</strong> Invoice penjualan lama adalah data historis. Perubahan data ini <strong>tidak akan mengurangi atau menambah stok barang</strong>. Jika invoice kredit sudah memiliki pembayaran piutang, sistem akan menjaga agar data piutang tetap konsisten.
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if ($penjualan->piutang && $penjualan->piutang->total_dibayar > 0)
                <div class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-md">
                    Penjualan ini sudah memiliki pembayaran piutang. Penjualan kredit yang sudah dibayar sebagian tidak bisa diubah menjadi tunai sebelum pembayaran piutang diatur.
                </div>
                @endif

                <form action="{{ route('invoice-historis.penjualan.update', $penjualan->id_penjualan) }}" method="POST" id="formPenjualanHistoris">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block mb-1 font-medium">Nomor Sistem</label>
                            <input type="text"
                                value="{{ $penjualan->nomor_invoice }}"
                                class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                                readonly>
                            <p class="text-sm text-gray-500 mt-1">
                                Nomor sistem historis tidak diubah.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Nomor Dokumen Asli <span class="text-red-600">*</span>
                            </label>
                            <input type="text"
                                name="nomor_dokumen_asli"
                                value="{{ old('nomor_dokumen_asli', $penjualan->nomor_dokumen_asli) }}"
                                placeholder="Contoh: INV lama / nota lama"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                            <p class="text-sm text-gray-500 mt-1">
                                Nomor ini yang tampil sebagai nomor invoice historis.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Penjualan Lama</label>
                            <input type="date"
                                name="tanggal_penjualan"
                                value="{{ old('tanggal_penjualan', optional($penjualan->tanggal_penjualan)->format('Y-m-d')) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-medium">Customer <span class="text-red-600">*</span></label>

                                <button type="button"
                                    id="btnBukaModalCustomer"
                                    class="text-sm px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    + Tambah Customer
                                </button>
                            </div>

                            <select name="id_customer"
                                id="customerSelect"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Cari kode atau nama customer..."
                                required>
                                <option value="">-- Cari / Pilih Customer --</option>
                                @foreach ($customers as $customer)
                                <option value="{{ $customer->id_customer }}"
                                    data-nama="{{ $customer->nama_customer }}"
                                    data-npwp="{{ $customer->npwp }}"
                                    data-alamat="{{ $customer->alamat }}"
                                    {{ old('id_customer', $penjualan->id_customer) == $customer->id_customer ? 'selected' : '' }}>
                                    {{ $customer->kode_customer }} - {{ $customer->nama_customer }}
                                    @if ($customer->nomor_telepon)
                                    | {{ $customer->nomor_telepon }}
                                    @endif
                                    @if ($customer->npwp)
                                    | NPWP: {{ $customer->npwp }}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold text-lg mb-2">Daftar Barang pada Invoice Lama</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200" id="tableBarang">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-3 py-2 text-left">Barang</th>
                                        <th class="border px-3 py-2 text-right">Jumlah</th>
                                        <th class="border px-3 py-2 text-right">Harga Jual</th>
                                        <th class="border px-3 py-2 text-right">Subtotal</th>
                                        <th class="border px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($penjualan->detailPenjualan as $detail)
                                    <tr data-old-barang-id="{{ $detail->id_barang }}" data-old-jumlah="{{ $detail->jumlah }}">
                                        <td class="border px-3 py-2 min-w-[460px]">
                                            <select name="id_barang[]"
                                                class="w-full barang-select"
                                                placeholder="Cari kode atau nama barang..."
                                                required>
                                                <option value="">-- Cari / Pilih Barang --</option>
                                                @foreach ($barang as $item)
                                                <option value="{{ $item->id_barang }}"
                                                    data-harga="{{ $item->harga_jual_default }}"
                                                    data-stok="{{ $item->stok_saat_ini }}"
                                                    data-satuan="{{ $item->satuan }}"
                                                    data-tipe-perhitungan="{{ $item->tipe_perhitungan_harga ?? 'normal' }}"
                                                    data-satuan-hitung="{{ $item->satuan_hitung_harga }}"
                                                    data-isi-per-satuan="{{ $item->isi_per_satuan ?? 1 }}"
                                                    data-kena-ppn="{{ ($item->kena_ppn ?? true) ? 1 : 0 }}"
                                                    {{ old('id_barang.' . $loop->parent->index, $detail->id_barang) == $item->id_barang ? 'selected' : '' }}>
                                                    {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                                    | Stok: {{ $item->stok_saat_ini }} {{ strtoupper($item->satuan) }}
                                                    | Harga: Rp {{ number_format($item->harga_jual_default, 0, ',', '.') }}
                                                    | {{ ($item->kena_ppn ?? true) ? 'PPN' : 'Non PPN' }}
                                                    @if (($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan')
                                                    / {{ strtoupper($item->satuan_hitung_harga) }}
                                                    | 1 {{ strtoupper($item->satuan) }} = {{ rtrim(rtrim(number_format($item->isi_per_satuan, 3, ',', '.'), '0'), ',') }} {{ strtoupper($item->satuan_hitung_harga) }}
                                                    @else
                                                    / {{ strtoupper($item->satuan) }}
                                                    @endif
                                                </option>
                                                @endforeach
                                            </select>

                                            <p class="text-sm text-gray-500 mt-1 stok-info">Stok tersedia: -</p>
                                            <p class="text-sm text-blue-600 mt-1 perhitungan-info">Perhitungan: -</p>
                                            <p class="text-sm text-purple-600 mt-1 ppn-info">PPN: -</p>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="jumlah[]"
                                                value="{{ old('jumlah.' . $loop->index, $detail->jumlah) }}"
                                                min="1"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-input"
                                                required>
                                            <p class="text-xs text-gray-500 mt-1 satuan-jumlah-info text-right">-</p>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="harga_jual[]"
                                                value="{{ old('harga_jual.' . $loop->index, $detail->harga_jual) }}"
                                                min="0"
                                                step="0.01"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-right harga-input"
                                                required>
                                            <p class="text-xs text-gray-500 mt-1 harga-info text-right">-</p>
                                        </td>

                                        <td class="border px-3 py-2 text-right min-w-[160px]">
                                            <span class="subtotal-text">Rp 0</span>
                                        </td>

                                        <td class="border px-3 py-2 text-center">
                                            <button type="button"
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 btn-hapus">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <template id="templateBarangRow">
                                <tr data-old-barang-id="" data-old-jumlah="0">
                                    <td class="border px-3 py-2 min-w-[460px]">
                                        <select name="id_barang[]"
                                            class="w-full barang-select"
                                            placeholder="Cari kode atau nama barang..."
                                            required>
                                            <option value="">-- Cari / Pilih Barang --</option>
                                            @foreach ($barang as $item)
                                            <option value="{{ $item->id_barang }}"
                                                data-harga="{{ $item->harga_jual_default }}"
                                                data-stok="{{ $item->stok_saat_ini }}"
                                                data-satuan="{{ $item->satuan }}"
                                                data-tipe-perhitungan="{{ $item->tipe_perhitungan_harga ?? 'normal' }}"
                                                data-satuan-hitung="{{ $item->satuan_hitung_harga }}"
                                                data-isi-per-satuan="{{ $item->isi_per_satuan ?? 1 }}"
                                                data-kena-ppn="{{ ($item->kena_ppn ?? true) ? 1 : 0 }}">
                                                {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                                | Stok: {{ $item->stok_saat_ini }} {{ strtoupper($item->satuan) }}
                                                | Harga: Rp {{ number_format($item->harga_jual_default, 0, ',', '.') }}
                                                | {{ ($item->kena_ppn ?? true) ? 'PPN' : 'Non PPN' }}
                                                @if (($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan')
                                                / {{ strtoupper($item->satuan_hitung_harga) }}
                                                | 1 {{ strtoupper($item->satuan) }} = {{ rtrim(rtrim(number_format($item->isi_per_satuan, 3, ',', '.'), '0'), ',') }} {{ strtoupper($item->satuan_hitung_harga) }}
                                                @else
                                                / {{ strtoupper($item->satuan) }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>

                                        <p class="text-sm text-gray-500 mt-1 stok-info">Stok tersedia: -</p>
                                        <p class="text-sm text-blue-600 mt-1 perhitungan-info">Perhitungan: -</p>
                                        <p class="text-sm text-purple-600 mt-1 ppn-info">PPN: -</p>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="jumlah[]"
                                            value="1"
                                            min="1"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-input"
                                            required>
                                        <p class="text-xs text-gray-500 mt-1 satuan-jumlah-info text-right">-</p>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="harga_jual[]"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right harga-input"
                                            required>
                                        <p class="text-xs text-gray-500 mt-1 harga-info text-right">-</p>
                                    </td>

                                    <td class="border px-3 py-2 text-right min-w-[160px]">
                                        <span class="subtotal-text">Rp 0</span>
                                    </td>

                                    <td class="border px-3 py-2 text-center">
                                        <button type="button"
                                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 btn-hapus">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </div>

                        <button type="button"
                            id="btnTambahBarang"
                            class="mt-3 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            + Tambah Baris Barang
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Metode Pembayaran</label>
                                <select name="metode_pembayaran"
                                    id="metodePembayaran"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="tunai" {{ old('metode_pembayaran', $penjualan->metode_pembayaran) === 'tunai' ? 'selected' : '' }}>
                                        Tunai
                                    </option>
                                    <option value="kredit" {{ old('metode_pembayaran', $penjualan->metode_pembayaran) === 'kredit' ? 'selected' : '' }}>
                                        Kredit / Piutang
                                    </option>
                                </select>
                            </div>

                            <div class="mb-4" id="fieldJatuhTempo" style="display: none;">
                                <label class="block mb-1 font-medium">Tanggal Jatuh Tempo</label>
                                <input type="date"
                                    name="tanggal_jatuh_tempo"
                                    value="{{ old('tanggal_jatuh_tempo', optional($penjualan->tanggal_jatuh_tempo)->format('Y-m-d')) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500 mt-1">
                                    Wajib diisi jika pembayaran kredit.
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">Catatan</label>
                                <textarea name="catatan"
                                    rows="4"
                                    class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan', $penjualan->catatan) }}</textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md border">
                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Mode PPN</label>
                                @php
                                $modePpnLama = old('mode_ppn', $penjualan->mode_ppn ?? 'include');
                                @endphp
                                <select name="mode_ppn"
                                    id="modePpn"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="tanpa_ppn" {{ $modePpnLama === 'tanpa_ppn' ? 'selected' : '' }}>
                                        Tidak Pakai PPN
                                    </option>
                                    <option value="include" {{ $modePpnLama === 'include' ? 'selected' : '' }}>
                                        Harga Sudah Termasuk PPN
                                    </option>
                                    <option value="exclude" {{ $modePpnLama === 'exclude' ? 'selected' : '' }}>
                                        Harga Belum Termasuk PPN
                                    </option>
                                </select>

                                <p class="text-sm text-gray-500 mt-1">
                                    Tarif PPN sistem: 11%. PPN hanya dihitung dari barang yang statusnya kena PPN.
                                </p>
                            </div>

                            <div class="mb-4 p-3 bg-white rounded border">
                                <input type="hidden" name="butuh_faktur_pajak" value="0">

                                <label class="flex items-start gap-2">
                                    <input type="checkbox"
                                        name="butuh_faktur_pajak"
                                        id="butuhFakturPajak"
                                        value="1"
                                        class="mt-1"
                                        {{ old('butuh_faktur_pajak', $penjualan->butuh_faktur_pajak) ? 'checked' : '' }}>

                                    <span>
                                        <strong>Customer membutuhkan faktur pajak</strong>
                                        <br>
                                        <small class="text-gray-500">
                                            Centang jika customer meminta data faktur pajak. Data ini hanya disimpan dan ditampilkan pada nota.
                                        </small>
                                    </span>
                                </label>

                                <div id="detailFakturPajak" class="mt-4 space-y-3 hidden">
                                    <div>
                                        <label class="block mb-1 font-medium">Nomor Faktur Pajak</label>
                                        <input type="text"
                                            name="nomor_faktur_pajak"
                                            id="nomorFakturPajak"
                                            value="{{ old('nomor_faktur_pajak', $penjualan->nomor_faktur_pajak) }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Opsional, isi jika sudah ada nomor faktur">
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">Tanggal Faktur Pajak</label>
                                        <input type="date"
                                            name="tanggal_faktur_pajak"
                                            id="tanggalFakturPajak"
                                            value="{{ old('tanggal_faktur_pajak', optional($penjualan->tanggal_faktur_pajak)->format('Y-m-d')) }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">
                                            Nama Faktur Pajak <span class="text-red-600">*</span>
                                        </label>
                                        <input type="text"
                                            name="nama_faktur_pajak"
                                            id="namaFakturPajak"
                                            value="{{ old('nama_faktur_pajak', $penjualan->nama_faktur_pajak) }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Nama sesuai kebutuhan faktur pajak">
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">
                                            NPWP Faktur Pajak <span class="text-red-600">*</span>
                                        </label>
                                        <input type="text"
                                            name="npwp_faktur_pajak"
                                            id="npwpFakturPajak"
                                            value="{{ old('npwp_faktur_pajak', $penjualan->npwp_faktur_pajak) }}"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Contoh: 01.234.567.8-999.000">
                                    </div>

                                    <div>
                                        <label class="block mb-1 font-medium">Alamat Faktur Pajak</label>
                                        <textarea name="alamat_faktur_pajak"
                                            id="alamatFakturPajak"
                                            rows="3"
                                            class="w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Alamat sesuai kebutuhan faktur pajak">{{ old('alamat_faktur_pajak', $penjualan->alamat_faktur_pajak) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal Penjualan</span>
                                <strong id="totalSubtotal">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal Barang Kena PPN</span>
                                <strong id="totalKenaPpn">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal Barang Non PPN</span>
                                <strong id="totalNonPpn">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>DPP</span>
                                <strong id="totalDpp">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>PPN 11%</span>
                                <strong id="totalPajak">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2 border-t pt-2">
                                <span>Total Sebelum Penyesuaian</span>
                                <strong id="totalSebelumPenyesuaian">Rp 0</strong>
                            </div>

                            <div class="mt-4 mb-4 p-3 bg-white rounded border">
                                <label class="block mb-2 font-medium">Penyesuaian Total Akhir Setelah PPN</label>

                                @php
                                $jenisPenyesuaian = old('jenis_penyesuaian_total', $penjualan->jenis_penyesuaian_total ?? 'tidak_ada');
                                @endphp

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium">Jenis Penyesuaian</label>
                                        <select name="jenis_penyesuaian_total"
                                            id="jenisPenyesuaianTotal"
                                            class="w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="tidak_ada" {{ $jenisPenyesuaian === 'tidak_ada' ? 'selected' : '' }}>
                                                Tidak Ada
                                            </option>
                                            <option value="tambah" {{ $jenisPenyesuaian === 'tambah' ? 'selected' : '' }}>
                                                Tambah Total Akhir
                                            </option>
                                            <option value="kurang" {{ $jenisPenyesuaian === 'kurang' ? 'selected' : '' }}>
                                                Kurangi Total Akhir
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-sm font-medium">Nominal Penyesuaian</label>
                                        <input type="number"
                                            name="nominal_penyesuaian_total"
                                            id="nominalPenyesuaianTotal"
                                            value="{{ old('nominal_penyesuaian_total', $penjualan->nominal_penyesuaian_total ?? 0) }}"
                                            min="0"
                                            step="0.01"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="block mb-1 text-sm font-medium">Keterangan Penyesuaian</label>
                                    <textarea name="keterangan_penyesuaian_total"
                                        id="keteranganPenyesuaianTotal"
                                        rows="2"
                                        class="w-full border-gray-300 rounded-md shadow-sm"
                                        placeholder="Contoh: pengurangan karena isi botol dalam 1 dus kurang, pembulatan harga, tambahan biaya lain">{{ old('keterangan_penyesuaian_total', $penjualan->keterangan_penyesuaian_total) }}</textarea>
                                </div>

                                <p class="text-sm text-gray-500 mt-2">
                                    Opsional. Penyesuaian ini dihitung setelah PPN, jadi tidak mengubah DPP dan nilai PPN.
                                </p>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Penyesuaian Total</span>
                                <strong id="totalPenyesuaian">Rp 0</strong>
                            </div>

                            <div class="flex justify-between border-t pt-2 text-lg">
                                <span>Total Akhir</span>
                                <strong id="totalAkhir">Rp 0</strong>
                            </div>

                            <p id="keteranganModePpn" class="text-sm text-gray-500 mt-2 text-right"></p>
                            <p id="keteranganPenyesuaian" class="text-sm text-gray-500 mt-1 text-right"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <a href="{{ route('invoice-historis.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            onclick="return confirm('Update invoice penjualan lama? Data ini tidak akan memengaruhi stok.')">
                            Update Invoice Lama
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalCustomer"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-start justify-center z-50 overflow-y-auto px-4 py-6 sm:py-10">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b px-6 py-4 flex-shrink-0 bg-white rounded-t-xl">
                <h3 class="text-lg font-semibold">Tambah Customer Baru</h3>
                <button type="button" id="btnTutupModalCustomer" class="text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>

            <form id="formQuickCustomer" class="flex flex-col min-h-0">
                @csrf

                <div class="p-6 overflow-y-auto min-h-0">
                    <div id="quickCustomerMessage" class="hidden mb-4 p-4 rounded-md whitespace-pre-line break-words text-sm leading-relaxed"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Nama Customer <span class="text-red-600">*</span></label>
                            <input type="text" name="nama_customer" id="quickNamaCustomer" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <p class="text-sm text-gray-500 mt-1">
                                Wajib diisi. Data lainnya boleh dikosongkan, tetapi jika diisi tidak boleh sama dengan customer lain.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Nomor Handphone <span class="text-gray-500 text-sm">(Opsional)</span></label>
                            <input type="text" name="nomor_telepon" id="quickNomorTelepon" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Contoh: 08123456789">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">NPWP <span class="text-gray-500 text-sm">(Opsional)</span></label>
                            <input type="text" name="npwp" id="quickNpwpCustomer" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Contoh: 01.234.567.8-999.000">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Kategori Customer <span class="text-gray-500 text-sm">(Opsional)</span></label>
                            <input type="text" name="kategori_customer" id="quickKategoriCustomer" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Contoh: Customer Baru, Customer Lama, Grosir">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Alamat <span class="text-gray-500 text-sm">(Opsional)</span></label>
                            <textarea name="alamat" id="quickAlamatCustomer" rows="3" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Alamat customer"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Catatan <span class="text-gray-500 text-sm">(Opsional)</span></label>
                            <textarea name="catatan" id="quickCatatanCustomer" rows="3" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Catatan tambahan tentang customer"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t bg-white flex-shrink-0 rounded-b-xl">
                    <button type="button" id="btnBatalCustomer" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" id="btnSimpanQuickCustomer" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">Simpan Customer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function formatRupiah(angka) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(angka) || 0));
            }

            function formatAngkaDesimal(angka) {
                const number = parseFloat(angka) || 0;
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 3
                }).format(number);
            }

            function loadTomSelect(callback) {
                if (window.TomSelect) {
                    callback();
                    return;
                }

                let existingScript = document.querySelector('script[data-tomselect-fallback="1"]');
                if (existingScript) {
                    existingScript.addEventListener('load', callback);
                    return;
                }

                const cssLoaded = document.querySelector('link[href*="tom-select.css"]');
                if (!cssLoaded) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css';
                    document.head.appendChild(link);
                }

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js';
                script.dataset.tomselectFallback = '1';
                script.onload = callback;
                script.onerror = function() {
                    console.warn('Tom Select gagal dimuat. Select tetap bisa dipakai secara standar, tetapi fitur pencarian tidak aktif.');
                };
                document.head.appendChild(script);
            }

            function initCustomerSelect() {
                const customerSelect = document.getElementById('customerSelect');
                if (!customerSelect || customerSelect.tomselect || !window.TomSelect) return;

                new TomSelect(customerSelect, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 100,
                    searchField: ['text'],
                    placeholder: 'Cari kode atau nama customer...',
                    onChange: function() {
                        isiFakturDariCustomer(true);
                    }
                });
            }

            function initBarangSelect(selectElement) {
                if (!selectElement || selectElement.tomselect || !window.TomSelect) return;

                new TomSelect(selectElement, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 100,
                    searchField: ['text'],
                    placeholder: 'Cari kode atau nama barang...',
                    onChange: function() {
                        const row = selectElement.closest('tr');
                        updateBarangInfo(row);
                        hitungTotal();
                    }
                });
            }

            function initAllBarangSelect() {
                document.querySelectorAll('.barang-select').forEach(function(select) {
                    initBarangSelect(select);
                });
            }

            function getSelectedOption(selectElement) {
                if (!selectElement || !selectElement.value) return null;
                return selectElement.querySelector('option[value="' + selectElement.value + '"]');
            }

            function getDetailBarangDariRow(row) {
                if (!row) return null;

                const select = row.querySelector('.barang-select');
                const selectedOption = getSelectedOption(select);
                if (!selectedOption) return null;

                return {
                    harga: parseFloat(selectedOption.getAttribute('data-harga')) || 0,
                    stok: parseInt(selectedOption.getAttribute('data-stok')) || 0,
                    satuan: selectedOption.getAttribute('data-satuan') || '',
                    tipePerhitungan: selectedOption.getAttribute('data-tipe-perhitungan') || 'normal',
                    satuanHitung: selectedOption.getAttribute('data-satuan-hitung') || '',
                    isiPerSatuan: parseFloat(selectedOption.getAttribute('data-isi-per-satuan')) || 1,
                    kenaPpn: selectedOption.getAttribute('data-kena-ppn') === '1',
                };
            }

            function updateBarangInfo(row) {
                if (!row) return;

                const detail = getDetailBarangDariRow(row);
                const hargaInput = row.querySelector('.harga-input');
                const stokInfo = row.querySelector('.stok-info');
                const perhitunganInfo = row.querySelector('.perhitungan-info');
                const satuanJumlahInfo = row.querySelector('.satuan-jumlah-info');
                const hargaInfo = row.querySelector('.harga-info');
                const ppnInfo = row.querySelector('.ppn-info');

                if (!detail) {
                    if (hargaInput) hargaInput.value = 0;
                    if (stokInfo) stokInfo.innerText = 'Stok saat ini: -';
                    if (perhitunganInfo) perhitunganInfo.innerText = 'Perhitungan: -';
                    if (satuanJumlahInfo) satuanJumlahInfo.innerText = '-';
                    if (hargaInfo) hargaInfo.innerText = '-';
                    if (ppnInfo) ppnInfo.innerText = 'PPN: -';
                    return;
                }

                if (hargaInput && (!hargaInput.value || parseFloat(hargaInput.value) === 0)) {
                    hargaInput.value = detail.harga || 0;
                }

                if (stokInfo) {
                    stokInfo.innerText = 'Stok saat ini: ' + detail.stok + ' ' + detail.satuan + ' (tidak berubah karena historis)';
                }

                if (satuanJumlahInfo) satuanJumlahInfo.innerText = detail.satuan ? detail.satuan : '-';

                if (detail.tipePerhitungan === 'isi_kemasan') {
                    if (perhitunganInfo) {
                        perhitunganInfo.innerText = 'Perhitungan: jumlah ' + detail.satuan + ' x ' + formatAngkaDesimal(detail.isiPerSatuan) + ' ' + detail.satuanHitung + ' x harga per ' + detail.satuanHitung;
                    }
                    if (hargaInfo) hargaInfo.innerText = 'Harga per ' + detail.satuanHitung;
                } else {
                    if (perhitunganInfo) {
                        perhitunganInfo.innerText = 'Perhitungan: jumlah ' + detail.satuan + ' x harga per ' + detail.satuan;
                    }
                    if (hargaInfo) hargaInfo.innerText = 'Harga per ' + detail.satuan;
                }

                if (ppnInfo) {
                    ppnInfo.innerText = detail.kenaPpn ?
                        'PPN: Barang ini kena PPN' :
                        'PPN: Barang ini tidak kena PPN';
                }
            }

            function hitungSubtotalRow(row) {
                const detail = getDetailBarangDariRow(row);
                const jumlahInput = row.querySelector('.jumlah-input');
                const hargaInput = row.querySelector('.harga-input');
                const jumlah = parseFloat(jumlahInput ? jumlahInput.value : 0) || 0;
                const harga = parseFloat(hargaInput ? hargaInput.value : 0) || 0;

                if (!detail) return jumlah * harga;
                if (detail.tipePerhitungan === 'isi_kemasan') return jumlah * detail.isiPerSatuan * harga;
                return jumlah * harga;
            }

            function hitungPpn(totalSubtotal, subtotalKenaPpn) {
                const tarifPpn = 11;
                const modePpnElement = document.getElementById('modePpn');
                const modePpn = modePpnElement ? modePpnElement.value : 'include';

                if (modePpn === 'tanpa_ppn') {
                    return {
                        dpp: 0,
                        ppn: 0,
                        totalAkhir: totalSubtotal,
                        keterangan: 'Transaksi historis tidak menggunakan PPN. Semua nilai PPN menjadi Rp0.'
                    };
                }

                if (modePpn === 'exclude') {
                    const ppn = subtotalKenaPpn * (tarifPpn / 100);
                    return {
                        dpp: subtotalKenaPpn,
                        ppn: ppn,
                        totalAkhir: totalSubtotal + ppn,
                        keterangan: 'Harga belum termasuk PPN. PPN 11% hanya dihitung dari barang yang kena PPN.'
                    };
                }

                const dpp = subtotalKenaPpn * 100 / 111;
                const ppn = subtotalKenaPpn - dpp;
                return {
                    dpp: dpp,
                    ppn: ppn,
                    totalAkhir: totalSubtotal,
                    keterangan: 'Harga sudah termasuk PPN. PPN hanya dipisahkan dari barang yang kena PPN.'
                };
            }

            function hitungPenyesuaianTotal(totalSebelumPenyesuaian) {
                const jenisElement = document.getElementById('jenisPenyesuaianTotal');
                const nominalElement = document.getElementById('nominalPenyesuaianTotal');

                const jenis = jenisElement ? jenisElement.value : 'tidak_ada';
                const nominal = nominalElement ? (parseFloat(nominalElement.value) || 0) : 0;

                if (jenis === 'tambah' && nominal > 0) {
                    return {
                        nilaiPenyesuaian: nominal,
                        totalAkhir: totalSebelumPenyesuaian + nominal,
                        keterangan: 'Total akhir ditambah setelah perhitungan PPN.'
                    };
                }

                if (jenis === 'kurang' && nominal > 0) {
                    return {
                        nilaiPenyesuaian: -nominal,
                        totalAkhir: Math.max(totalSebelumPenyesuaian - nominal, 0),
                        keterangan: nominal > totalSebelumPenyesuaian ?
                            'Peringatan: nominal pengurangan lebih besar dari total sebelum penyesuaian.' : 'Total akhir dikurangi setelah perhitungan PPN.'
                    };
                }

                return {
                    nilaiPenyesuaian: 0,
                    totalAkhir: totalSebelumPenyesuaian,
                    keterangan: 'Tidak ada penyesuaian total akhir.'
                };
            }

            function updateFieldPenyesuaianTotal() {
                const jenisElement = document.getElementById('jenisPenyesuaianTotal');
                const nominalElement = document.getElementById('nominalPenyesuaianTotal');
                const keteranganElement = document.getElementById('keteranganPenyesuaianTotal');

                if (!jenisElement || !nominalElement || !keteranganElement) return;

                const aktif = jenisElement.value !== 'tidak_ada';

                nominalElement.disabled = !aktif;
                keteranganElement.disabled = !aktif;

                if (!aktif) {
                    nominalElement.value = 0;
                    keteranganElement.value = '';
                }
            }

            function hitungTotal() {
                let totalSubtotal = 0;
                let subtotalKenaPpn = 0;
                let subtotalNonPpn = 0;

                document.querySelectorAll('#tableBarang tbody tr').forEach(function(row) {
                    const detail = getDetailBarangDariRow(row);
                    const subtotal = hitungSubtotalRow(row);
                    const subtotalText = row.querySelector('.subtotal-text');
                    if (subtotalText) subtotalText.innerText = formatRupiah(subtotal);

                    totalSubtotal += subtotal;

                    if (detail && detail.kenaPpn) subtotalKenaPpn += subtotal;
                    else subtotalNonPpn += subtotal;
                });

                const hasilPpn = hitungPpn(totalSubtotal, subtotalKenaPpn);
                const hasilPenyesuaian = hitungPenyesuaianTotal(hasilPpn.totalAkhir);

                const mapping = {
                    totalSubtotal: totalSubtotal,
                    totalKenaPpn: subtotalKenaPpn,
                    totalNonPpn: subtotalNonPpn,
                    totalDpp: hasilPpn.dpp,
                    totalPajak: hasilPpn.ppn,
                    totalSebelumPenyesuaian: hasilPpn.totalAkhir,
                    totalPenyesuaian: hasilPenyesuaian.nilaiPenyesuaian,
                    totalAkhir: hasilPenyesuaian.totalAkhir,
                };

                Object.keys(mapping).forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) el.innerText = formatRupiah(mapping[id]);
                });

                const keteranganModePpn = document.getElementById('keteranganModePpn');
                const keteranganPenyesuaian = document.getElementById('keteranganPenyesuaian');
                if (keteranganModePpn) keteranganModePpn.innerText = hasilPpn.keterangan;
                if (keteranganPenyesuaian) keteranganPenyesuaian.innerText = hasilPenyesuaian.keterangan;
            }

            function updateMetodePembayaran() {
                const metodeElement = document.getElementById('metodePembayaran');
                const fieldJatuhTempo = document.getElementById('fieldJatuhTempo');
                if (!metodeElement || !fieldJatuhTempo) return;

                const inputJatuhTempo = fieldJatuhTempo.querySelector('input');

                if (metodeElement.value === 'kredit') {
                    fieldJatuhTempo.style.display = 'block';
                    if (inputJatuhTempo) inputJatuhTempo.setAttribute('required', 'required');
                } else {
                    fieldJatuhTempo.style.display = 'none';
                    if (inputJatuhTempo) inputJatuhTempo.removeAttribute('required');
                }
            }

            function getCustomerTerpilih() {
                const select = document.getElementById('customerSelect');
                if (!select || !select.value) return null;

                const option = select.querySelector('option[value="' + select.value + '"]');
                if (!option) return null;

                return {
                    nama: option.getAttribute('data-nama') || '',
                    npwp: option.getAttribute('data-npwp') || '',
                    alamat: option.getAttribute('data-alamat') || ''
                };
            }

            function isiFakturDariCustomer(force = false) {
                const customer = getCustomerTerpilih();
                if (!customer) return;

                const namaInput = document.getElementById('namaFakturPajak');
                const npwpInput = document.getElementById('npwpFakturPajak');
                const alamatInput = document.getElementById('alamatFakturPajak');

                if (namaInput && (force || !namaInput.value)) namaInput.value = customer.nama;
                if (npwpInput && (force || !npwpInput.value)) npwpInput.value = customer.npwp;
                if (alamatInput && (force || !alamatInput.value)) alamatInput.value = customer.alamat;
            }

            function updateDetailFakturPajak() {
                const checkbox = document.getElementById('butuhFakturPajak');
                const detail = document.getElementById('detailFakturPajak');
                if (!checkbox || !detail) return;

                const wajibInputs = [
                    document.getElementById('namaFakturPajak'),
                    document.getElementById('npwpFakturPajak'),
                ];

                if (checkbox.checked) {
                    detail.classList.remove('hidden');
                    wajibInputs.forEach(function(input) {
                        if (input) input.setAttribute('required', 'required');
                    });
                    isiFakturDariCustomer(false);
                } else {
                    detail.classList.add('hidden');
                    wajibInputs.forEach(function(input) {
                        if (input) input.removeAttribute('required');
                    });
                }
            }

            function bukaModalCustomer() {
                const modal = document.getElementById('modalCustomer');
                const messageBox = document.getElementById('quickCustomerMessage');
                if (!modal) {
                    alert('Modal tambah customer belum ditemukan. Pastikan file view sudah diganti penuh.');
                    return;
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');

                if (messageBox) {
                    messageBox.classList.add('hidden');
                    messageBox.innerText = '';
                }

                setTimeout(function() {
                    const input = document.getElementById('quickNamaCustomer');
                    if (input) input.focus();
                }, 100);
            }

            function tutupModalCustomer() {
                const modal = document.getElementById('modalCustomer');
                const form = document.getElementById('formQuickCustomer');
                const messageBox = document.getElementById('quickCustomerMessage');
                const btn = document.getElementById('btnSimpanQuickCustomer');

                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                document.body.classList.remove('overflow-hidden');
                if (form) form.reset();
                if (messageBox) {
                    messageBox.classList.add('hidden');
                    messageBox.innerText = '';
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerText = 'Simpan Customer';
                }
            }

            function tampilkanPesanCustomer(type, message) {
                const box = document.getElementById('quickCustomerMessage');
                if (!box) return;

                box.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700', 'bg-yellow-100', 'text-yellow-700');

                if (type === 'error') box.classList.add('bg-red-100', 'text-red-700');
                else if (type === 'exists') box.classList.add('bg-yellow-100', 'text-yellow-700');
                else box.classList.add('bg-green-100', 'text-green-700');

                box.innerText = message;
            }

            function pilihCustomer(customer) {
                const customerSelect = document.getElementById('customerSelect');
                if (!customerSelect) return;

                const text = customer.kode_customer + ' - ' + customer.nama_customer +
                    (customer.nomor_telepon ? ' | ' + customer.nomor_telepon : '') +
                    (customer.npwp ? ' | NPWP: ' + customer.npwp : '');

                let option = customerSelect.querySelector('option[value="' + customer.id_customer + '"]');

                if (!option) {
                    option = new Option(text, customer.id_customer, true, true);
                    customerSelect.add(option);
                } else {
                    option.text = text;
                }

                option.setAttribute('data-nama', customer.nama_customer || '');
                option.setAttribute('data-npwp', customer.npwp || '');
                option.setAttribute('data-alamat', customer.alamat || '');

                if (customerSelect.tomselect) {
                    customerSelect.tomselect.addOption({
                        value: String(customer.id_customer),
                        text: text
                    });
                    customerSelect.tomselect.setValue(String(customer.id_customer), true);
                    customerSelect.tomselect.refreshOptions(false);
                } else {
                    customerSelect.value = customer.id_customer;
                }

                isiFakturDariCustomer(true);
            }

            async function simpanQuickCustomer(e) {
                e.preventDefault();

                const btn = document.getElementById('btnSimpanQuickCustomer');
                const form = document.getElementById('formQuickCustomer');
                const token = document.querySelector('input[name="_token"]');

                if (!form || !token) {
                    tampilkanPesanCustomer('error', 'Form customer atau token CSRF tidak ditemukan. Refresh halaman lalu coba lagi.');
                    return;
                }

                const formData = new FormData(form);

                if (btn) {
                    btn.disabled = true;
                    btn.innerText = 'Menyimpan...';
                }

                try {
                    const response = await fetch("{{ route('customers.quickStore') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token.value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch (jsonError) {
                        data = {
                            message: 'Response server tidak berbentuk JSON.'
                        };
                    }

                    if (!response.ok) {
                        let pesan = 'Customer gagal disimpan.';
                        if (data.errors) pesan = Object.values(data.errors).flat().join('\n');
                        else if (data.message) pesan = data.message;
                        tampilkanPesanCustomer('error', pesan);
                        return;
                    }

                    pilihCustomer(data.customer);

                    if (data.status === 'exists') tampilkanPesanCustomer('exists', data.message || 'Customer sudah tersedia dan langsung dipilih.');
                    else tampilkanPesanCustomer('success', data.message || 'Customer baru berhasil ditambahkan dan langsung dipilih.');

                    setTimeout(function() {
                        tutupModalCustomer();
                    }, 900);
                } catch (error) {
                    tampilkanPesanCustomer('error', 'Terjadi kesalahan saat menyimpan customer. Cek koneksi atau route customers.quickStore.');
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerText = 'Simpan Customer';
                    }
                }
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('jumlah-input') ||
                    e.target.classList.contains('harga-input') ||
                    e.target.id === 'nominalPenyesuaianTotal') {
                    hitungTotal();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.id === 'metodePembayaran') updateMetodePembayaran();
                if (e.target.name === 'mode_ppn') hitungTotal();
                if (e.target.id === 'jenisPenyesuaianTotal') {
                    updateFieldPenyesuaianTotal();
                    hitungTotal();
                }
                if (e.target.id === 'butuhFakturPajak') updateDetailFakturPajak();
                if (e.target.id === 'customerSelect') isiFakturDariCustomer(true);

                if (e.target.classList.contains('barang-select')) {
                    const row = e.target.closest('tr');
                    updateBarangInfo(row);
                    hitungTotal();
                }
            });

            document.addEventListener('click', function(e) {
                const btnTambahBarang = e.target.closest('#btnTambahBarang');
                if (btnTambahBarang) {
                    e.preventDefault();
                    const tbody = document.querySelector('#tableBarang tbody');
                    const template = document.getElementById('templateBarangRow');
                    if (!tbody || !template) return;

                    const newRow = template.content.cloneNode(true);
                    tbody.appendChild(newRow);

                    const rows = tbody.querySelectorAll('tr');
                    const lastRow = rows[rows.length - 1];
                    initBarangSelect(lastRow.querySelector('.barang-select'));
                    updateBarangInfo(lastRow);
                    hitungTotal();
                    return;
                }

                const btnHapus = e.target.closest('.btn-hapus');
                if (btnHapus) {
                    e.preventDefault();
                    const tbody = document.querySelector('#tableBarang tbody');
                    if (!tbody) return;

                    if (tbody.querySelectorAll('tr').length <= 1) {
                        alert('Minimal harus ada satu barang dalam invoice historis.');
                        return;
                    }

                    const row = btnHapus.closest('tr');
                    const select = row.querySelector('.barang-select');
                    if (select && select.tomselect) select.tomselect.destroy();
                    row.remove();
                    hitungTotal();
                    return;
                }

                const btnBukaModal = e.target.closest('#btnBukaModalCustomer');
                if (btnBukaModal) {
                    e.preventDefault();
                    bukaModalCustomer();
                    return;
                }

                const btnTutupModal = e.target.closest('#btnTutupModalCustomer, #btnBatalCustomer');
                if (btnTutupModal) {
                    e.preventDefault();
                    tutupModalCustomer();
                    return;
                }

                const modalCustomer = document.getElementById('modalCustomer');
                if (modalCustomer && e.target === modalCustomer) {
                    tutupModalCustomer();
                }
            });

            const formQuickCustomer = document.getElementById('formQuickCustomer');
            if (formQuickCustomer) {
                formQuickCustomer.addEventListener('submit', simpanQuickCustomer);
            }

            document.addEventListener('keydown', function(e) {
                const modalCustomer = document.getElementById('modalCustomer');
                if (e.key === 'Escape' && modalCustomer && !modalCustomer.classList.contains('hidden')) {
                    tutupModalCustomer();
                }
            });

            loadTomSelect(function() {
                initCustomerSelect();
                initAllBarangSelect();
            });

            updateMetodePembayaran();
            updateDetailFakturPajak();
            updateFieldPenyesuaianTotal();
            hitungTotal();

            document.querySelectorAll('#tableBarang tbody tr').forEach(function(row) {
                updateBarangInfo(row);
            });
        });
    </script>
</x-app-layout>