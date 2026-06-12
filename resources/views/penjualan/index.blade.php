<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Penjualan / Barang Keluar
            </h2>

            <a href="{{ route('penjualan.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Tambah Penjualan
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

                <form method="GET" action="{{ route('penjualan.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nomor invoice atau nama customer..."
                        class="w-full border-gray-300 rounded-md shadow-sm">

                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cari
                    </button>

                    <a href="{{ route('penjualan.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">No Invoice</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Customer</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">Pajak</th>
                                <th class="border px-3 py-2 text-right">Total Akhir</th>
                                <th class="border px-3 py-2 text-center">Metode</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($penjualan as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($penjualan->currentPage() - 1) * $penjualan->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->nomor_invoice }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->tanggal_penjualan->format('d-m-Y') }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->customer->nama_customer ?? '-' }}
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
                                    {{ ucfirst($item->metode_pembayaran) }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->status_pembayaran === 'lunas')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">
                                        Lunas
                                    </span>
                                    @elseif ($item->status_pembayaran === 'sebagian')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm">
                                        Sebagian
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">
                                        Belum Lunas
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('penjualan.show', $item->id_penjualan) }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Detail
                                        </a>

                                        <a href="{{ route('penjualan.edit', $item->id_penjualan) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="border px-3 py-4 text-center text-gray-500">
                                    Data penjualan belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $penjualan->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>