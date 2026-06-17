<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Data Barang
            </h2>

            <a href="{{ route('barang.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                + Tambah Barang
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

                <form method="GET" action="{{ route('barang.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari kode, nama barang, atau satuan..."
                        class="w-full border-gray-300 rounded-md shadow-sm">

                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cari
                    </button>

                    <a href="{{ route('barang.index') }}"
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
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-left">Satuan</th>
                                <th class="border px-3 py-2 text-right">Stok</th>
                                <th class="border px-3 py-2 text-right">Harga Beli</th>
                                <th class="border px-3 py-2 text-right">Harga Jual</th>
                                <th class="border px-3 py-2 text-left">Perhitungan Harga</th>
                                <th class="border px-3 py-2 text-center">PPN</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($barang as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($barang->currentPage() - 1) * $barang->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->kode_barang }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->nama_barang }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ strtoupper($item->satuan) }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $item->stok_saat_ini }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->harga_beli_terakhir, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->harga_jual_default, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2">
                                    @if (($item->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan')
                                    <div>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-sm">
                                            Isi Kemasan
                                        </span>
                                        <p class="text-sm text-gray-600 mt-1">
                                            1 {{ $item->satuan }} =
                                            {{ rtrim(rtrim(number_format($item->isi_per_satuan, 3, ',', '.'), '0'), ',') }}
                                            {{ $item->satuan_hitung_harga }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Harga per {{ $item->satuan_hitung_harga }}
                                        </p>
                                    </div>
                                    @else
                                    <div>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                            Normal
                                        </span>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Harga per {{ $item->satuan }}
                                        </p>
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->kena_ppn ?? true)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-sm">
                                        Kena PPN
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                        Non PPN
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->status_aktif)
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
                                        <a href="{{ route('barang.edit', $item->id_barang) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>

                                        @if ($item->status_aktif)
                                        <form action="{{ route('barang.nonaktifkan', $item->id_barang) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menonaktifkan barang ini?')">
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
                                <td colspan="11" class="border px-3 py-4 text-center text-gray-500">
                                    Data barang belum tersedia.
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
</x-app-layout>