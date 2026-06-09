<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Pembelian / Barang Masuk
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

                <form action="{{ route('pembelian.store') }}" method="POST" id="formPembelian">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block mb-1 font-medium">Nomor Pembelian</label>
                            <input type="text"
                                value="{{ $nomorPembelian }}"
                                class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                                readonly>
                            <p class="text-sm text-gray-500 mt-1">
                                Nomor dibuat otomatis.
                            </p>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Pembelian</label>
                            <input type="date"
                                name="tanggal_pembelian"
                                value="{{ old('tanggal_pembelian', date('Y-m-d')) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Supplier</label>
                            <select name="id_supplier"
                                id="supplierSelect"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Cari kode atau nama supplier..."
                                required>
                                <option value="">-- Cari / Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id_supplier }}"
                                    {{ old('id_supplier') == $supplier->id_supplier ? 'selected' : '' }}>
                                    {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    @if ($supplier->nomor_telepon)
                                    | {{ $supplier->nomor_telepon }}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h3 class="font-semibold text-lg mb-2">Daftar Barang Dibeli</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200" id="tableBarang">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-3 py-2 text-left">Barang</th>
                                        <th class="border px-3 py-2 text-right">Jumlah</th>
                                        <th class="border px-3 py-2 text-right">Harga Beli</th>
                                        <th class="border px-3 py-2 text-right">Subtotal</th>
                                        <th class="border px-3 py-2 text-center">Aksi</th>
                                    </tr>
                                </thead>

                                <tbody>
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
                                                    | Stok: {{ $item->stok_saat_ini }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="border px-3 py-2">
                                            <input type="number"
                                                name="jumlah[]"
                                                value="1"
                                                min="1"
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
                                                | Stok: {{ $item->stok_saat_ini }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="border px-3 py-2">
                                        <input type="number"
                                            name="jumlah[]"
                                            value="1"
                                            min="1"
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
                                class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md border">
                            <div class="mb-4">
                                <label class="block mb-1 font-medium">Persentase Pajak (%)</label>
                                <input type="number"
                                    name="persentase_pajak"
                                    id="persentasePajak"
                                    value="{{ old('persentase_pajak', 0) }}"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-right">
                                <p class="text-sm text-gray-500 mt-1">
                                    Isi 0 jika transaksi tidak menggunakan pajak.
                                </p>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal</span>
                                <strong id="totalSubtotal">Rp 0</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Nilai Pajak</span>
                                <strong id="totalPajak">Rp 0</strong>
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
                            onclick="return confirm('Simpan transaksi pembelian ini? Stok barang akan bertambah otomatis.')">
                            Simpan Pembelian
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
        }

        function initBarangSelect(selectElement) {
            if (selectElement.tomselect) {
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

        function initSupplierSelect() {
            const supplierSelect = document.getElementById('supplierSelect');

            if (!supplierSelect) {
                return;
            }

            if (supplierSelect.tomselect) {
                return;
            }

            new TomSelect(supplierSelect, {
                create: false,
                allowEmptyOption: true,
                maxOptions: 100,
                searchField: ['text'],
                placeholder: 'Cari kode atau nama supplier...'
            });
        }

        function hitungTotal() {
            let totalSubtotal = 0;

            document.querySelectorAll('#tableBarang tbody tr').forEach(function(row) {
                const jumlah = parseFloat(row.querySelector('.jumlah-input').value) || 0;
                const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
                const subtotal = jumlah * harga;

                row.querySelector('.subtotal-text').innerText = formatRupiah(subtotal);
                totalSubtotal += subtotal;
            });

            const persentasePajak = parseFloat(document.getElementById('persentasePajak').value) || 0;
            const nilaiPajak = totalSubtotal * (persentasePajak / 100);
            const totalAkhir = totalSubtotal + nilaiPajak;

            document.getElementById('totalSubtotal').innerText = formatRupiah(totalSubtotal);
            document.getElementById('totalPajak').innerText = formatRupiah(nilaiPajak);
            document.getElementById('totalAkhir').innerText = formatRupiah(totalAkhir);
        }

        document.addEventListener('input', function(e) {
            if (
                e.target.classList.contains('jumlah-input') ||
                e.target.classList.contains('harga-input') ||
                e.target.id === 'persentasePajak'
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
            const select = lastRow.querySelector('.barang-select');

            initBarangSelect(select);
            hitungTotal();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus')) {
                const tbody = document.querySelector('#tableBarang tbody');

                if (tbody.querySelectorAll('tr').length <= 1) {
                    alert('Minimal harus ada satu barang dalam transaksi pembelian.');
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

        document.addEventListener('DOMContentLoaded', function() {
            initSupplierSelect();
            initAllBarangSelect();
            hitungTotal();
        });
    </script>
</x-app-layout>