<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Customer
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

                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Kode Customer</label>
                        <input type="text"
                            value="{{ $kodeCustomer }}"
                            class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100"
                            readonly>
                        <p class="text-sm text-gray-500 mt-1">
                            Kode customer dibuat otomatis oleh sistem.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Nama Customer</label>
                        <input type="text"
                            name="nama_customer"
                            value="{{ old('nama_customer') }}"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Nomor Telepon</label>
                        <input type="text"
                            name="nomor_telepon"
                            value="{{ old('nomor_telepon') }}"
                            placeholder="Contoh: 08123456789"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Kategori Customer</label>
                        <input type="text"
                            name="kategori_customer"
                            value="{{ old('kategori_customer') }}"
                            placeholder="Contoh: Customer Lama, Customer Baru, Grosir"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Alamat</label>
                        <textarea name="alamat"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('alamat') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Catatan</label>
                        <textarea name="catatan"
                            rows="3"
                            placeholder="Catatan tambahan tentang customer..."
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('customers.index') }}"
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
</x-app-layout>