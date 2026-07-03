<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    @php
    $oldIdBarang = old('id_barang');
    $oldJumlahDipesan = old('jumlah_dipesan');
    $oldJumlah = old('jumlah');
    $oldHargaBeli = old('harga_beli');

    $rows = [];

    if (is_array($oldIdBarang)) {
    foreach ($oldIdBarang as $index => $idBarang) {
    $rows[] = [
    'id_barang' => $idBarang,
    'jumlah_dipesan' => $oldJumlahDipesan[$index] ?? 1,
    'jumlah' => $oldJumlah[$index] ?? 1,
    'harga_beli' => $oldHargaBeli[$index] ?? 0,
    ];
    }
    } else {
    foreach ($pembelian->detailPembelian as $detail) {
    $rows[] = [
    'id_barang' => $detail->id_barang,
    'jumlah_dipesan' => $detail->jumlah_dipesan ?? $detail->jumlah,
    'jumlah' => $detail->jumlah,
    'harga_beli' => $detail->harga_beli,
    ];
    }
    }
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Pembelian / Barang Masuk
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <div class="font-semibold mb-1">
                        Data belum valid:
                    </div>

                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md text-sm">
                    <strong>Perhatian:</strong>
                    Saat pembelian diedit, sistem akan mengembalikan stok lama terlebih dahulu, lalu menghitung ulang stok berdasarkan data pembelian terbaru.
                    Jika stok lama sudah terpakai/dijual sehingga tidak cukup untuk dikembalikan, pembelian tidak bisa diedit sampai stoknya disesuaikan.
                </div>

                <form action="{{ route('pembelian.update', $pembelian->id_pembelian) }}" method="POST" id="formPembelian">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block mb-1 font-medium">
                                Nomor Invoice / Nota Pembelian <span class="text-red-600">*</span>
                            </label>

                            <input type="text"
                                name="nomor_pembelian"
                                value="{{ old('nomor_pembelian', $pembelian->nomor_dokumen_asli ?: $pembelian->nomor_pembelian) }}"
                                placeholder="Contoh: INV-SUP-001 / Nota Supplier 001"
                                class="w-full border-gray-300 rounded-md shadow-sm @error('nomor_pembelian') border-red-500 @enderror"
                                required>

                            @error('nomor_pembelian')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-sm text-gray-500 mt-1">
                                Nomor diisi manual sesuai nota/invoice supplier. Boleh sama dengan pembelian lain karena nomor sistem internal tetap menjadi pembeda.
                            </p>

                            @if (!empty($pembelian->nomor_pembelian) && !empty($pembelian->nomor_dokumen_asli) && $pembelian->nomor_pembelian !== $pembelian->nomor_dokumen_asli)
                            <p class="text-xs text-blue-600 mt-1">
                                No Sistem: {{ $pembelian->nomor_pembelian }}
                            </p>
                            @endif
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Nomor Delivery Order Supplier
                            </label>

                            <input type="text"
                                name="nomor_delivery_order"
                                value="{{ old('nomor_delivery_order', $pembelian->nomor_delivery_order) }}"
                                placeholder="Contoh: DO-SUP-20260612-0001"
                                class="w-full border-gray-300 rounded-md shadow-sm @error('nomor_delivery_order') border-red-500 @enderror">

                            @error('nomor_delivery_order')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-sm text-gray-500 mt-1">
                                Opsional. Isi sesuai dokumen DO dari supplier.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">
                                Nomor Surat Jalan Supplier
                            </label>

                            <input type="text"
                                name="nomor_surat_jalan"
                                value="{{ old('nomor_surat_jalan', $pembelian->nomor_surat_jalan) }}"
                                placeholder="Contoh: SJ-SUP-20260612-0001"
                                class="w-full border-gray-300 rounded-md shadow-sm @error('nomor_surat_jalan') border-red-500 @enderror">

                            @error('nomor_surat_jalan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <p class="text-sm text-gray-500 mt-1">
                                Opsional. Isi sesuai surat jalan dari supplier.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Pembelian</label>
                            <input type="date"
                                name="tanggal_pembelian"
                                value="{{ old('tanggal_pembelian', $pembelian->tanggal_pembelian->format('Y-m-d')) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-medium">
                                    Supplier <span class="text-red-600">*</span>
                                </label>

                                <button type="button"
                                    id="btnBukaModalSupplier"
                                    class="text-sm px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    + Tambah Supplier
                                </button>
                            </div>

                            <select name="id_supplier"
                                id="supplierSelect"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Cari kode atau nama supplier..."
                                required>
                                <option value="">-- Cari / Pilih Supplier --</option>

                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id_supplier }}"
                                    {{ old('id_supplier', $pembelian->id_supplier) == $supplier->id_supplier ? 'selected' : '' }}>
                                    {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}

                                    @if ($supplier->nomor_telepon)
                                    | {{ $supplier->nomor_telepon }}
                                    @endif

                                    @if ($supplier->npwp)
                                    | NPWP: {{ $supplier->npwp }}
                                    @endif
                                </option>
                                @endforeach
                            </select>

                            <p class="text-sm text-gray-500 mt-1">
                                Pilih supplier lama atau tambah supplier baru jika belum terdaftar.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold text-lg mb-2">Daftar Barang Dibeli</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200" id="tableBarang">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-3 py-2 text-left">Barang</th>
                                        <th class="border px-3 py-2 text-right">Jumlah Dipesan</th>
                                        <th class="border px-3 py-2 text-right">Jumlah Diterima</th>
                                        <th class="border px-3 py-2 text-right">Harga Beli</th>
                                        <th class="border px-3 py-2 text-right">Subtotal Diterima</th>
                                        <th class="border px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($rows as $row)
                                    <tr>
                                        <td class="border px-3 py-2 min-w-[360px]">
                                            <select name="id_barang[]"
                                                class="w-full barang-select"
                                                placeholder="Cari kode atau nama barang..."
                                                required>
                                                <option value="">-- Cari / Pilih Barang --</option>

                                                @foreach ($barang as $item)
                                                <option value="{{ $item->id_barang }}"
                                                    {{ (string) $row['id_barang'] === (string) $item->id_barang ? 'selected' : '' }}>
                                                    {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                                    | Stok Saat Ini: {{ $item->stok_saat_ini }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="jumlah_dipesan[]"
                                                value="{{ $row['jumlah_dipesan'] }}"
                                                min="1"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-dipesan-input"
                                                required>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="jumlah[]"
                                                value="{{ $row['jumlah'] }}"
                                                min="0"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-input"
                                                required>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="harga_beli[]"
                                                value="{{ $row['harga_beli'] }}"
                                                min="0"
                                                step="0.01"
                                                class="w-full border-gray-300 rounded-md shadow-sm text-right harga-input"
                                                required>
                                        </td>

                                        <td class="border px-3 py-2 text-right">
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
                                <tr>
                                    <td class="border px-3 py-2 min-w-[360px]">
                                        <select name="id_barang[]"
                                            class="w-full barang-select"
                                            placeholder="Cari kode atau nama barang..."
                                            required>
                                            <option value="">-- Cari / Pilih Barang --</option>

                                            @foreach ($barang as $item)
                                            <option value="{{ $item->id_barang }}">
                                                {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                                | Stok Saat Ini: {{ $item->stok_saat_ini }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="jumlah_dipesan[]"
                                            value="1"
                                            min="1"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-dipesan-input"
                                            required>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="jumlah[]"
                                            value="1"
                                            min="0"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right jumlah-input"
                                            required>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="harga_beli[]"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-right harga-input"
                                            required>
                                    </td>

                                    <td class="border px-3 py-2 text-right">
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
                            <label class="block mb-1 font-medium">Catatan</label>
                            <textarea name="catatan"
                                rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan', $pembelian->catatan) }}</textarea>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md border">
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800">
                                <strong>PPN Pembelian Manual:</strong>
                                isi nominal PPN sesuai yang tertera pada invoice/faktur supplier.
                                Sistem tidak menghitung otomatis dari persen agar total mengikuti dokumen supplier.
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">PPN dari Supplier (Rp)</label>
                                <input type="number"
                                    name="nilai_pajak"
                                    id="nilaiPajak"
                                    value="{{ old('nilai_pajak', $pembelian->nilai_pajak ?? 0) }}"
                                    min="0"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-right">
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Biaya Lain / Ongkir (Rp)</label>
                                <input type="number"
                                    name="biaya_lain"
                                    id="biayaLain"
                                    value="{{ old('biaya_lain', $pembelian->biaya_lain ?? 0) }}"
                                    min="0"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-right">
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Potongan / Diskon (Rp)</label>
                                <input type="number"
                                    name="potongan_diskon"
                                    id="potonganDiskon"
                                    value="{{ old('potongan_diskon', $pembelian->potongan_diskon ?? 0) }}"
                                    min="0"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-right">
                            </div>

                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Catatan Penyesuaian Total</label>
                                <textarea name="keterangan_penyesuaian_total"
                                    id="keteranganPenyesuaianTotal"
                                    rows="2"
                                    class="w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Contoh: PPN sesuai faktur supplier, ongkir, diskon supplier">{{ old('keterangan_penyesuaian_total', $pembelian->keterangan_penyesuaian_total) }}</textarea>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal Barang Diterima</span>
                                <strong id="totalSubtotal">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>PPN dari Supplier</span>
                                <strong id="totalPajak">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Biaya Lain / Ongkir</span>
                                <strong id="totalBiayaLain">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Potongan / Diskon</span>
                                <strong id="totalPotongan">Rp 0</strong>
                            </div>

                            <div class="flex justify-between border-t pt-2 text-lg">
                                <span>Total Akhir</span>
                                <strong id="totalAkhir">Rp 0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <a href="{{ route('pembelian.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            onclick="return confirm('Update transaksi pembelian ini? Stok barang akan disesuaikan ulang.')">
                            Update Pembelian
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div id="modalSupplier"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-start justify-center z-50 overflow-y-auto px-4 py-6 sm:py-10">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between border-b px-6 py-4 flex-shrink-0 bg-white rounded-t-xl">
                <h3 class="text-lg font-semibold">
                    Tambah Supplier Baru
                </h3>

                <button type="button"
                    id="btnTutupModalSupplier"
                    class="text-gray-500 hover:text-gray-800 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <form id="formQuickSupplier" class="flex flex-col min-h-0">
                @csrf

                <div class="p-6 overflow-y-auto min-h-0">
                    <div id="quickSupplierMessage"
                        class="hidden mb-4 p-4 rounded-md whitespace-pre-line break-words text-sm leading-relaxed">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">
                                Nama Perusahaan Supplier <span class="text-red-600">*</span>
                            </label>
                            <input type="text"
                                name="nama_supplier"
                                id="quickNamaSupplier"
                                placeholder="Contoh: PT Berkat Jaya Nusantara"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>

                            <p class="text-sm text-gray-500 mt-1">
                                Wajib diisi. Nama perusahaan tidak boleh sama dengan supplier lain.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Nomor Telepon</label>
                            <input type="text"
                                name="nomor_telepon"
                                id="quickNomorTelepon"
                                placeholder="Contoh: 08123456789"
                                class="w-full border-gray-300 rounded-md shadow-sm">

                            <p class="text-sm text-gray-500 mt-1">
                                Opsional. Jika nomor telepon sudah tersedia, supplier lama akan langsung dipilih.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">NPWP Perusahaan</label>
                            <input type="text"
                                name="npwp"
                                id="quickNpwpSupplier"
                                placeholder="Contoh: 01.234.567.8-999.000"
                                class="w-full border-gray-300 rounded-md shadow-sm">

                            <p class="text-sm text-gray-500 mt-1">
                                Opsional. Jika NPWP sudah tersedia, supplier lama akan langsung dipilih.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Alamat</label>
                            <textarea name="alamat"
                                id="quickAlamatSupplier"
                                rows="3"
                                placeholder="Alamat lengkap perusahaan supplier..."
                                class="w-full border-gray-300 rounded-md shadow-sm"></textarea>

                            <p class="text-sm text-gray-500 mt-1">
                                Opsional. Jika alamat sudah tersedia, supplier lama akan langsung dipilih.
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-1 font-medium">Catatan</label>
                            <textarea name="catatan"
                                id="quickCatatanSupplier"
                                rows="3"
                                placeholder="Catatan tambahan tentang supplier..."
                                class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t bg-white flex-shrink-0 rounded-b-xl">
                    <button type="button"
                        id="btnBatalSupplier"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Batal
                    </button>

                    <button type="submit"
                        id="btnSimpanQuickSupplier"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed">
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(angka) || 0));
        }

        function initSupplierSelect() {
            const supplierSelect = document.getElementById('supplierSelect');

            if (!supplierSelect || supplierSelect.tomselect) {
                return;
            }

            new TomSelect(supplierSelect, {
                create: false,
                allowEmptyOption: true,
                maxOptions: 100,
                searchField: ['text'],
                placeholder: 'Cari kode, nama, nomor telepon, atau NPWP supplier...'
            });
        }

        function initBarangSelect(selectElement) {
            if (!selectElement || selectElement.tomselect) {
                return;
            }

            new TomSelect(selectElement, {
                create: false,
                allowEmptyOption: true,
                maxOptions: 100,
                searchField: ['text'],
                placeholder: 'Cari kode atau nama barang...'
            });
        }

        function initAllBarangSelect() {
            document.querySelectorAll('.barang-select').forEach(function(select) {
                initBarangSelect(select);
            });
        }

        function hitungTotal() {
            let totalSubtotal = 0;

            document.querySelectorAll('#tableBarang tbody tr').forEach(function(row) {
                const jumlahDipesanInput = row.querySelector('.jumlah-dipesan-input');
                const jumlahDiterimaInput = row.querySelector('.jumlah-input');
                const hargaInput = row.querySelector('.harga-input');

                const jumlahDipesan = parseFloat(jumlahDipesanInput.value) || 0;
                const jumlahDiterima = parseFloat(jumlahDiterimaInput.value) || 0;
                const harga = parseFloat(hargaInput.value) || 0;

                if (jumlahDiterima > jumlahDipesan) {
                    jumlahDiterimaInput.value = jumlahDipesan;
                }

                const jumlahFinal = parseFloat(jumlahDiterimaInput.value) || 0;
                const subtotal = jumlahFinal * harga;

                row.querySelector('.subtotal-text').innerText = formatRupiah(subtotal);
                totalSubtotal += subtotal;
            });

            const nilaiPajak = parseFloat(document.getElementById('nilaiPajak').value) || 0;
            const biayaLain = parseFloat(document.getElementById('biayaLain').value) || 0;
            const potonganDiskon = parseFloat(document.getElementById('potonganDiskon').value) || 0;

            const totalSebelumPotongan = totalSubtotal + nilaiPajak + biayaLain;
            const totalAkhir = Math.max(totalSebelumPotongan - potonganDiskon, 0);

            document.getElementById('totalSubtotal').innerText = formatRupiah(totalSubtotal);
            document.getElementById('totalPajak').innerText = formatRupiah(nilaiPajak);
            document.getElementById('totalBiayaLain').innerText = formatRupiah(biayaLain);
            document.getElementById('totalPotongan').innerText = formatRupiah(potonganDiskon);
            document.getElementById('totalAkhir').innerText = formatRupiah(totalAkhir);
        }

        function bukaModalSupplier() {
            const modal = document.getElementById('modalSupplier');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('quickSupplierMessage').classList.add('hidden');
            document.getElementById('quickNamaSupplier').focus();
        }

        function tutupModalSupplier() {
            const modal = document.getElementById('modalSupplier');

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            document.getElementById('formQuickSupplier').reset();
            document.getElementById('quickSupplierMessage').classList.add('hidden');
        }

        function tampilkanPesanSupplier(type, message) {
            const box = document.getElementById('quickSupplierMessage');

            box.classList.remove(
                'hidden',
                'bg-red-100',
                'text-red-700',
                'bg-green-100',
                'text-green-700',
                'bg-yellow-100',
                'text-yellow-700'
            );

            if (type === 'error') {
                box.classList.add('bg-red-100', 'text-red-700');
            } else if (type === 'exists') {
                box.classList.add('bg-yellow-100', 'text-yellow-700');
            } else {
                box.classList.add('bg-green-100', 'text-green-700');
            }

            box.innerText = message;
        }

        function buatTextSupplier(supplier) {
            let text = supplier.kode_supplier + ' - ' + supplier.nama_supplier;

            if (supplier.nomor_telepon) {
                text += ' | ' + supplier.nomor_telepon;
            }

            if (supplier.npwp) {
                text += ' | NPWP: ' + supplier.npwp;
            }

            return text;
        }

        function pilihSupplier(supplier) {
            const supplierSelect = document.getElementById('supplierSelect');
            const text = buatTextSupplier(supplier);

            let option = supplierSelect.querySelector('option[value="' + supplier.id_supplier + '"]');

            if (!option) {
                option = new Option(text, supplier.id_supplier, true, true);
                supplierSelect.add(option);
            } else {
                option.text = text;
            }

            if (supplierSelect.tomselect) {
                supplierSelect.tomselect.addOption({
                    value: String(supplier.id_supplier),
                    text: text
                });

                supplierSelect.tomselect.setValue(String(supplier.id_supplier), true);
                supplierSelect.tomselect.refreshOptions(false);
            } else {
                supplierSelect.value = supplier.id_supplier;
            }
        }

        async function simpanQuickSupplier(event) {
            event.preventDefault();

            const form = document.getElementById('formQuickSupplier');
            const button = document.getElementById('btnSimpanQuickSupplier');
            const formData = new FormData(form);

            button.disabled = true;
            button.innerText = 'Menyimpan...';

            try {
                const response = await fetch("{{ route('suppliers.quickStore') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    let pesan = 'Supplier gagal disimpan.';

                    if (data.errors) {
                        pesan = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        pesan = data.message;
                    }

                    tampilkanPesanSupplier('error', pesan);
                    return;
                }

                pilihSupplier(data.supplier);

                if (data.status === 'exists') {
                    tampilkanPesanSupplier('exists', data.message || 'Supplier sudah tersedia dan langsung dipilih.');
                } else {
                    tampilkanPesanSupplier('success', data.message || 'Supplier baru berhasil ditambahkan dan langsung dipilih.');
                }

                setTimeout(function() {
                    tutupModalSupplier();
                }, 800);
            } catch (error) {
                tampilkanPesanSupplier('error', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                button.disabled = false;
                button.innerText = 'Simpan Supplier';
            }
        }

        document.addEventListener('input', function(e) {
            if (
                e.target.classList.contains('jumlah-dipesan-input') ||
                e.target.classList.contains('jumlah-input') ||
                e.target.classList.contains('harga-input') ||
                e.target.id === 'nilaiPajak' ||
                e.target.id === 'biayaLain' ||
                e.target.id === 'potonganDiskon'
            ) {
                hitungTotal();
            }
        });

        document.getElementById('btnTambahBarang').addEventListener('click', function() {
            const tbody = document.querySelector('#tableBarang tbody');
            const template = document.getElementById('templateBarangRow');
            const newRow = template.content.cloneNode(true);

            tbody.appendChild(newRow);

            const rows = tbody.querySelectorAll('tr');
            const lastRow = rows[rows.length - 1];

            initBarangSelect(lastRow.querySelector('.barang-select'));
            hitungTotal();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus')) {
                const tbody = document.querySelector('#tableBarang tbody');

                if (tbody.querySelectorAll('tr').length <= 1) {
                    alert('Minimal harus ada satu barang.');
                    return;
                }

                const row = e.target.closest('tr');
                const select = row.querySelector('.barang-select');

                if (select && select.tomselect) {
                    select.tomselect.destroy();
                }

                row.remove();
                hitungTotal();
            }
        });

        document.getElementById('btnBukaModalSupplier').addEventListener('click', bukaModalSupplier);
        document.getElementById('btnTutupModalSupplier').addEventListener('click', tutupModalSupplier);
        document.getElementById('btnBatalSupplier').addEventListener('click', tutupModalSupplier);
        document.getElementById('formQuickSupplier').addEventListener('submit', simpanQuickSupplier);

        document.addEventListener('DOMContentLoaded', function() {
            initSupplierSelect();
            initAllBarangSelect();
            hitungTotal();
        });
    </script>
</x-app-layout>