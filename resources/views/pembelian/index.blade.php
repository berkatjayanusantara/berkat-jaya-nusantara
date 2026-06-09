<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pembelian / Barang Masuk
            </h2>

            <a href="{{ route('pembelian.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Tambah Pembelian
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

                <form method="GET" action="{{ route('pembelian.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nomor pembelian atau nama supplier..."
                        class="w-full border-gray-300 rounded-md shadow-sm">

                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cari
                    </button>

                    <a href="{{ route('pembelian.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Nomor Pembelian</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Supplier</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">Pajak</th>
                                <th class="border px-3 py-2 text-right">Total Akhir</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pembelian as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->nomor_pembelian }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->tanggal_pembelian->format('d-m-Y') }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->supplier->nama_supplier ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->nilai_pajak, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <a href="{{ route('pembelian.show', $item->id_pembelian) }}"
                                        class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="border px-3 py-4 text-center text-gray-500">
                                    Data pembelian belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pembelian->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>