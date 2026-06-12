<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Barang
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

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

                <form action="{{ route('barang.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Kode Barang</label>
                        <input type="text"
                            value="{{ $kodeBarang }}"
                            class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                            readonly>
                        <p class="text-sm text-gray-500 mt-1">
                            Kode barang dibuat otomatis oleh sistem.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">
                            Nama Barang <span class="text-red-600">*</span>
                        </label>
                        <input type="text"
                            name="nama_barang"
                            value="{{ old('nama_barang') }}"
                            placeholder="Contoh: Gula MLS 50kg"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block mb-1 font-medium">
                                Satuan Stok/Jual <span class="text-red-600">*</span>
                            </label>
                            <select name="satuan"
                                id="satuan"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach ($satuanOptions as $satuan)
                                <option value="{{ $satuan }}" {{ old('satuan') === $satuan ? 'selected' : '' }}>
                                    {{ strtoupper($satuan) }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">
                                Contoh: gula dijual per karung, maka pilih karung.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1 font-medium">
                                Stok Saat Ini <span class="text-red-600">*</span>
                            </label>
                            <input type="number"
                                name="stok_saat_ini"
                                value="{{ old('stok_saat_ini', 0) }}"
                                min="0"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block mb-1 font-medium">
                                Harga Beli Terakhir <span class="text-red-600">*</span>
                            </label>
                            <input type="number"
                                name="harga_beli_terakhir"
                                value="{{ old('harga_beli_terakhir', 0) }}"
                                min="0"
                                step="0.01"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1 font-medium">
                                Harga Jual Default <span class="text-red-600">*</span>
                            </label>
                            <input type="number"
                                name="harga_jual_default"
                                id="hargaJualDefault"
                                value="{{ old('harga_jual_default', 0) }}"
                                min="0"
                                step="0.01"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                            <p class="text-sm text-gray-500 mt-1" id="hargaJualHelp">
                                Untuk perhitungan normal, harga ini dihitung per satuan stok/jual.
                            </p>
                        </div>
                    </div>

                    <div class="mb-4 border rounded-md p-4 bg-gray-50">
                        <label class="block mb-2 font-medium">
                            Tipe Perhitungan Harga <span class="text-red-600">*</span>
                        </label>

                        <div class="space-y-2">
                            <label class="flex items-start gap-2">
                                <input type="radio"
                                    name="tipe_perhitungan_harga"
                                    value="normal"
                                    class="mt-1 tipe-perhitungan"
                                    {{ old('tipe_perhitungan_harga', 'normal') === 'normal' ? 'checked' : '' }}>
                                <span>
                                    <strong>Normal</strong>
                                    <br>
                                    <small class="text-gray-500">
                                        Subtotal = jumlah barang x harga jual.
                                    </small>
                                </span>
                            </label>

                            <label class="flex items-start gap-2">
                                <input type="radio"
                                    name="tipe_perhitungan_harga"
                                    value="isi_kemasan"
                                    class="mt-1 tipe-perhitungan"
                                    {{ old('tipe_perhitungan_harga') === 'isi_kemasan' ? 'checked' : '' }}>
                                <span>
                                    <strong>Berdasarkan isi kemasan</strong>
                                    <br>
                                    <small class="text-gray-500">
                                        Contoh: 1 karung x 50 kg x harga per kg.
                                    </small>
                                </span>
                            </label>
                        </div>

                        <div id="fieldIsiKemasan" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
                            <div>
                                <label class="block mb-1 font-medium">
                                    Satuan Hitung Harga <span class="text-red-600">*</span>
                                </label>
                                <select name="satuan_hitung_harga"
                                    id="satuanHitungHarga"
                                    class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Pilih Satuan Hitung --</option>
                                    @foreach ($satuanHitungOptions as $satuanHitung)
                                    <option value="{{ $satuanHitung }}" {{ old('satuan_hitung_harga') === $satuanHitung ? 'selected' : '' }}>
                                        {{ strtoupper($satuanHitung) }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">
                                    Contoh: kg, liter, meter, pcs.
                                </p>
                            </div>

                            <div>
                                <label class="block mb-1 font-medium">
                                    Isi per 1 Satuan Stok/Jual <span class="text-red-600">*</span>
                                </label>
                                <input type="number"
                                    name="isi_per_satuan"
                                    id="isiPerSatuan"
                                    value="{{ old('isi_per_satuan', 1) }}"
                                    min="0.001"
                                    step="0.001"
                                    class="w-full border-gray-300 rounded-md shadow-sm">
                                <p class="text-sm text-gray-500 mt-1" id="isiPerSatuanHelp">
                                    Contoh: 1 karung = 50 kg.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea name="keterangan"
                            rows="3"
                            placeholder="Contoh: Harga dihitung per kg, stok tetap per karung."
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('barang.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        function getTipePerhitungan() {
            const checked = document.querySelector('input[name="tipe_perhitungan_harga"]:checked');
            return checked ? checked.value : 'normal';
        }

        function updateFieldIsiKemasan() {
            const tipe = getTipePerhitungan();
            const fieldIsiKemasan = document.getElementById('fieldIsiKemasan');
            const satuan = document.getElementById('satuan').value || 'satuan';
            const satuanHitungHarga = document.getElementById('satuanHitungHarga').value || 'satuan hitung';

            const satuanHitungInput = document.getElementById('satuanHitungHarga');
            const isiPerSatuanInput = document.getElementById('isiPerSatuan');

            const hargaJualHelp = document.getElementById('hargaJualHelp');
            const isiPerSatuanHelp = document.getElementById('isiPerSatuanHelp');

            if (tipe === 'isi_kemasan') {
                fieldIsiKemasan.style.display = 'grid';
                satuanHitungInput.setAttribute('required', 'required');
                isiPerSatuanInput.setAttribute('required', 'required');

                hargaJualHelp.innerText = 'Untuk tipe ini, harga jual dihitung per ' + satuanHitungHarga + '.';
                isiPerSatuanHelp.innerText = 'Contoh: 1 ' + satuan + ' = 50 ' + satuanHitungHarga + '.';
            } else {
                fieldIsiKemasan.style.display = 'none';
                satuanHitungInput.removeAttribute('required');
                isiPerSatuanInput.removeAttribute('required');

                hargaJualHelp.innerText = 'Untuk perhitungan normal, harga ini dihitung per satuan stok/jual.';
                isiPerSatuanHelp.innerText = 'Contoh: 1 karung = 50 kg.';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateFieldIsiKemasan();

            document.querySelectorAll('.tipe-perhitungan').forEach(function(radio) {
                radio.addEventListener('change', updateFieldIsiKemasan);
            });

            document.getElementById('satuan').addEventListener('change', updateFieldIsiKemasan);
            document.getElementById('satuanHitungHarga').addEventListener('change', updateFieldIsiKemasan);
        });
    </script>
</x-app-layout>