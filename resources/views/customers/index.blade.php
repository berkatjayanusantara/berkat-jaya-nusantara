<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Customer
            </h2>

            <a href="{{ route('customers.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Tambah Customer
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

                <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari kode, nama, nomor telepon, atau NPWP customer..."
                        class="w-full border-gray-300 rounded-md shadow-sm">

                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cari
                    </button>

                    <a href="{{ route('customers.index') }}"
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
                                <th class="border px-3 py-2 text-left">Nama Customer</th>
                                <th class="border px-3 py-2 text-left">Nomor Telepon</th>
                                <th class="border px-3 py-2 text-left">NPWP</th>
                                <th class="border px-3 py-2 text-left">Kategori</th>
                                <th class="border px-3 py-2 text-left">Alamat</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($customers as $customer)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->kode_customer }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->nama_customer }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->nomor_telepon ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->npwp ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->kategori_customer ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $customer->alamat ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($customer->status_aktif)
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
                                        <a href="{{ route('customers.edit', $customer->id_customer) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>

                                        @if ($customer->status_aktif)
                                        <form action="{{ route('customers.nonaktifkan', $customer->id_customer) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menonaktifkan customer ini?')">
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
                                <td colspan="9" class="border px-3 py-4 text-center text-gray-500">
                                    Data customer belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $customers->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>