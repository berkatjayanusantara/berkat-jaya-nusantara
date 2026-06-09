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
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-medium">Supplier</label>

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
                                    {{ old('id_supplier') == $supplier->id_supplier ? 'selected' : '' }}>
                                    {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    @if ($supplier->nomor_telepon)
                                    | {{ $supplier->nomor_telepon }}
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
                                    Pajak tetap bisa ditampilkan, walaupun tidak ditambahkan ke total akhir.
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block mb-2 font-medium">Perhitungan Pajak</label>

                                <div class="space-y-2">
                                    <label class="flex items-start gap-2">
                                        <input type="radio"
                                            name="pajak_ditambahkan"
                                            value="1"
                                            class="mt-1"
                                            {{ old('pajak_ditambahkan', '1') == '1' ? 'checked' : '' }}>

                                        <span>
                                            <strong>Pajak ditambahkan ke total</strong>
                                            <br>
                                            <small class="text-gray-500">
                                                Untuk pembelian dari supplier yang memang dikenakan pajak.
                                            </small>
                                        </span>
                                    </label>

                                    <label class="flex items-start gap-2">
                                        <input type="radio"
                                            name="pajak_ditambahkan"
                                            value="0"
                                            class="mt-1"
                                            {{ old('pajak_ditambahkan') == '0' ? 'checked' : '' }}>

                                        <span>
                                            <strong>Pajak hanya ditampilkan</strong>
                                            <br>
                                            <small class="text-gray-500">
                                                Untuk pembelian yang pajaknya hanya ingin dicatat/ditampilkan, tetapi tidak menambah total.
                                            </small>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Subtotal Barang Diterima</span>
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
                            onclick="return confirm('Simpan transaksi pembelian ini? Stok barang akan bertambah sesuai jumlah diterima.')">
                            Simpan Pembelian
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div id="modalSupplier"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-semibold">
                    Tambah Supplier Baru
                </h3>

                <button type="button"
                    id="btnTutupModalSupplier"
                    class="text-gray-500 hover:text-gray-800 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <form id="formQuickSupplier" class="p-6">
                @csrf

                <div id="quickSupplierMessage"
                    class="hidden mb-4 p-4 rounded-md">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block mb-1 font-medium">
                            Nama Supplier <span class="text-red-600">*</span>
                        </label>
                        <input type="text"
                            name="nama_supplier"
                            id="quickNamaSupplier"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 font-medium">
                            Nomor Handphone <span class="text-red-600">*</span>
                        </label>
                        <input type="text"
                            name="nomor_telepon"
                            id="quickNomorTelepon"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                        <p class="text-sm text-gray-500 mt-1">
                            Jika nama atau nomor handphone sudah tersedia, supplier lama akan langsung dipilih.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 font-medium">Alamat</label>
                        <textarea name="alamat"
                            id="quickAlamatSupplier"
                            rows="2"
                            class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 font-medium">Catatan</label>
                        <textarea name="catatan"
                            id="quickCatatanSupplier"
                            rows="2"
                            class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button"
                        id="btnBatalSupplier"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Batal
                    </button>

                    <button type="submit"
                        id="btnSimpanQuickSupplier"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
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

            const persentasePajak = parseFloat(document.getElementById('persentasePajak').value) || 0;
            const nilaiPajak = totalSubtotal * (persentasePajak / 100);

            const pajakDitambahkanInput = document.querySelector('input[name="pajak_ditambahkan"]:checked');
            const pajakDitambahkan = pajakDitambahkanInput ? pajakDitambahkanInput.value === '1' : true;

            const totalAkhir = pajakDitambahkan ?
                totalSubtotal + nilaiPajak :
                totalSubtotal;

            document.getElementById('totalSubtotal').innerText = formatRupiah(totalSubtotal);
            document.getElementById('totalPajak').innerText = formatRupiah(nilaiPajak);
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

        function pilihSupplier(supplier) {
            const supplierSelect = document.getElementById('supplierSelect');
            const text = supplier.kode_supplier + ' - ' + supplier.nama_supplier +
                (supplier.nomor_telepon ? ' | ' + supplier.nomor_telepon : '');

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

                supplierSelect.tomselect.addItem(String(supplier.id_supplier), true);
                supplierSelect.tomselect.setValue(String(supplier.id_supplier), true);
                supplierSelect.tomselect.refreshOptions(false);
            } else {
                supplierSelect.value = supplier.id_supplier;
            }
        }

        async function simpanQuickSupplier(e) {
            e.preventDefault();

            const btn = document.getElementById('btnSimpanQuickSupplier');
            const form = document.getElementById('formQuickSupplier');
            const formData = new FormData(form);

            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

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
                }, 900);
            } catch (error) {
                tampilkanPesanSupplier('error', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Simpan Supplier';
            }
        }

        document.addEventListener('input', function(e) {
            if (
                e.target.classList.contains('jumlah-dipesan-input') ||
                e.target.classList.contains('jumlah-input') ||
                e.target.classList.contains('harga-input') ||
                e.target.id === 'persentasePajak' ||
                e.target.name === 'pajak_ditambahkan'
            ) {
                hitungTotal();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.name === 'pajak_ditambahkan') {
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

        document.getElementById('formPembelian').addEventListener('submit', function(e) {
            let valid = true;
            let pesan = '';

            document.querySelectorAll('#tableBarang tbody tr').forEach(function(row) {
                const jumlahDipesan = parseInt(row.querySelector('.jumlah-dipesan-input').value) || 0;
                const jumlahDiterima = parseInt(row.querySelector('.jumlah-input').value) || 0;

                if (jumlahDipesan < 1) {
                    valid = false;
                    pesan = 'Jumlah dipesan minimal 1.';
                }

                if (jumlahDiterima > jumlahDipesan) {
                    valid = false;
                    pesan = 'Jumlah diterima tidak boleh lebih besar dari jumlah dipesan.';
                }
            });

            if (!valid) {
                e.preventDefault();
                alert(pesan);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            initSupplierSelect();
            initAllBarangSelect();
            hitungTotal();

            document.getElementById('btnBukaModalSupplier').addEventListener('click', bukaModalSupplier);
            document.getElementById('btnTutupModalSupplier').addEventListener('click', tutupModalSupplier);
            document.getElementById('btnBatalSupplier').addEventListener('click', tutupModalSupplier);
            document.getElementById('formQuickSupplier').addEventListener('submit', simpanQuickSupplier);

            document.getElementById('modalSupplier').addEventListener('click', function(e) {
                if (e.target.id === 'modalSupplier') {
                    tutupModalSupplier();
                }
            });
        });
    </script>
</x-app-layout>