<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Barang
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

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

                <form action="{{ route('barang.update', $barang->id_barang) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Kode Barang</label>
                        <input type="text"
                            value="{{ $barang->kode_barang }}"
                            class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                            readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Nama Barang</label>
                        <input type="text"
                            name="nama_barang"
                            value="{{ old('nama_barang', $barang->nama_barang) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Satuan</label>
                        <input type="text"
                            name="satuan"
                            value="{{ old('satuan', $barang->satuan) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Stok Saat Ini</label>
                        <input type="number"
                            name="stok_saat_ini"
                            value="{{ old('stok_saat_ini', $barang->stok_saat_ini) }}"
                            min="0"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Harga Beli Terakhir</label>
                        <input type="number"
                            name="harga_beli_terakhir"
                            value="{{ old('harga_beli_terakhir', $barang->harga_beli_terakhir) }}"
                            min="0"
                            step="0.01"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Harga Jual Default</label>
                        <input type="number"
                            name="harga_jual_default"
                            value="{{ old('harga_jual_default', $barang->harga_jual_default) }}"
                            min="0"
                            step="0.01"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Status</label>
                        <select name="status_aktif"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                            <option value="1" {{ old('status_aktif', $barang->status_aktif) == 1 ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="0" {{ old('status_aktif', $barang->status_aktif) == 0 ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Keterangan</label>
                        <textarea name="keterangan"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('keterangan', $barang->keterangan) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('barang.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Update
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>