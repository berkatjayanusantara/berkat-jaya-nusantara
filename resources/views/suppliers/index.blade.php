<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Supplier
            </h2>

            <a href="{{ route('suppliers.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Tambah Supplier
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" action="{{ route('suppliers.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari kode, nama, atau nomor telepon supplier..."
                        class="w-full border-gray-300 rounded-md shadow-sm">

                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cari
                    </button>

                    <a href="{{ route('suppliers.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Kode</th>
                                <th class="border px-3 py-2 text-left">Nama Supplier</th>
                                <th class="border px-3 py-2 text-left">Nomor Telepon</th>
                                <th class="border px-3 py-2 text-left">Alamat</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $supplier->kode_supplier }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $supplier->nama_supplier }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $supplier->nomor_telepon ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $supplier->alamat ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($supplier->status_aktif)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">
                                        Aktif
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">
                                        Nonaktif
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('suppliers.edit', $supplier->id_supplier) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>

                                        @if ($supplier->status_aktif)
                                        <form action="{{ route('suppliers.nonaktifkan', $supplier->id_supplier) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menonaktifkan supplier ini?')">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="border px-3 py-4 text-center text-gray-500">
                                    Data supplier belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $suppliers->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>