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
                    <li>
                        {{ $error }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Form Stock Opname -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="mb-5">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Form Penyesuaian Stok
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Pilih barang, masukkan stok fisik, lalu sistem akan menyesuaikan stok otomatis.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('stock-opname.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tanggal Opname <span class="text-red-500">*</span>
                                </label>

                                <input type="date"
                                    id="tanggal"
                                    name="tanggal"
                                    value="{{ old('tanggal', date('Y-m-d')) }}"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required>

                                @error('tanggal')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
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

                                    @foreach ($barang as $item)
                                    <option value="{{ $item->id_barang }}"
                                        data-kode="{{ $item->kode_barang }}"
                                        data-nama="{{ $item->nama_barang }}"
                                        data-stok="{{ $item->stok_saat_ini }}"
                                        data-satuan="{{ $item->satuan }}"
                                        {{ (string) old('id_barang') === (string) $item->id_barang ? 'selected' : '' }}>
                                        {{ $item->kode_barang }} - {{ $item->nama_barang }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('id_barang')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
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
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
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
                                    placeholder="Contoh: Penyesuaian setelah pengecekan stok fisik di gudang."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>

                                @error('keterangan')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                <div class="text-sm text-yellow-800">
                                    <span class="font-semibold">Catatan:</span>
                                    Setelah disimpan, stok barang akan langsung berubah sesuai stok fisik dan sistem akan membuat riwayat stok dengan jenis
                                    <span class="font-semibold">Penyesuaian</span>.
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                <button type="submit"
                                    onclick="return confirm('Apakah data stock opname sudah benar? Stok barang akan langsung diperbarui.')"
                                    class="inline-flex justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                    Simpan Stock Opname
                                </button>

                                <button type="reset"
                                    id="resetForm"
                                    class="inline-flex justify-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300 transition">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Barang -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Daftar Barang Aktif
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Gunakan daftar ini untuk melihat stok sistem sebelum melakukan opname.
                                </p>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('stock-opname.create') }}" class="mb-4 flex flex-col sm:flex-row gap-2">
                            <input type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari kode atau nama barang..."
                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            <div class="flex gap-2">
                                <button type="submit"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition">
                                    Cari
                                </button>

                                <a href="{{ route('stock-opname.create') }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                    Reset
                                </a>
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-3 py-2 text-left whitespace-nowrap">
                                            No
                                        </th>
                                        <th class="border px-3 py-2 text-left whitespace-nowrap">
                                            Kode Barang
                                        </th>
                                        <th class="border px-3 py-2 text-left whitespace-nowrap">
                                            Nama Barang
                                        </th>
                                        <th class="border px-3 py-2 text-left whitespace-nowrap">
                                            Satuan
                                        </th>
                                        <th class="border px-3 py-2 text-right whitespace-nowrap">
                                            Stok Sistem
                                        </th>
                                        <th class="border px-3 py-2 text-center whitespace-nowrap">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($barang as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="border px-3 py-2">
                                            {{ $loop->iteration + ($barang->currentPage() - 1) * $barang->perPage() }}
                                        </td>

                                        <td class="border px-3 py-2 font-medium text-gray-800">
                                            {{ $item->kode_barang }}
                                        </td>

                                        <td class="border px-3 py-2">
                                            {{ $item->nama_barang }}
                                        </td>

                                        <td class="border px-3 py-2">
                                            {{ $item->satuan ?? '-' }}
                                        </td>

                                        <td class="border px-3 py-2 text-right font-semibold">
                                            {{ number_format($item->stok_saat_ini, 0, ',', '.') }}
                                        </td>

                                        <td class="border px-3 py-2 text-center">
                                            <button type="button"
                                                class="pilih-barang inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-md hover:bg-blue-200 transition"
                                                data-id="{{ $item->id_barang }}"
                                                data-kode="{{ $item->kode_barang }}"
                                                data-nama="{{ $item->nama_barang }}"
                                                data-stok="{{ $item->stok_saat_ini }}"
                                                data-satuan="{{ $item->satuan }}">
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="border px-3 py-4 text-center text-gray-500">
                                            Data barang aktif belum tersedia.
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

            function updateInfoBarang() {
                const selectedOption = barangSelect.options[barangSelect.selectedIndex];

                if (!selectedOption || !selectedOption.value) {
                    stokSistemInput.value = '';
                    satuanBarangInput.value = '';
                    hitungSelisih();
                    return;
                }

                stokSistemInput.value = selectedOption.dataset.stok ?? 0;
                satuanBarangInput.value = selectedOption.dataset.satuan ?? '-';

                hitungSelisih();
            }

            function hitungSelisih() {
                const stokSistem = parseInt(stokSistemInput.value || 0);
                const stokFisik = stokFisikInput.value === '' ? null : parseInt(stokFisikInput.value);

                if (stokFisik === null || Number.isNaN(stokFisik)) {
                    selisihStokInput.value = '';
                    statusSelisihInput.value = '';
                    return;
                }

                const selisih = stokFisik - stokSistem;

                selisihStokInput.value = selisih;

                if (selisih > 0) {
                    statusSelisihInput.value = 'Stok bertambah';
                } else if (selisih < 0) {
                    statusSelisihInput.value = 'Stok berkurang';
                } else {
                    statusSelisihInput.value = 'Stok sama';
                }
            }

            barangSelect.addEventListener('change', updateInfoBarang);
            stokFisikInput.addEventListener('input', hitungSelisih);

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

            resetFormButton.addEventListener('click', function() {
                setTimeout(function() {
                    stokSistemInput.value = '';
                    satuanBarangInput.value = '';
                    selisihStokInput.value = '';
                    statusSelisihInput.value = '';
                }, 10);
            });

            updateInfoBarang();
        });
    </script>
</x-app-layout>