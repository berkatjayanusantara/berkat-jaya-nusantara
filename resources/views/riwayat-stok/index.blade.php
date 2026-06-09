<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Stok
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" action="{{ route('riwayat-stok.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari barang, transaksi, atau keterangan..."
                        class="md:col-span-2 border-gray-300 rounded-md shadow-sm">

                    <select name="jenis_pergerakan"
                        class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Semua Jenis</option>
                        <option value="masuk" {{ $jenis === 'masuk' ? 'selected' : '' }}>
                            Masuk
                        </option>
                        <option value="keluar" {{ $jenis === 'keluar' ? 'selected' : '' }}>
                            Keluar
                        </option>
                        <option value="penyesuaian" {{ $jenis === 'penyesuaian' ? 'selected' : '' }}>
                            Penyesuaian
                        </option>
                    </select>

                    <select name="id_barang"
                        class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Semua Barang</option>
                        @foreach ($barang as $item)
                        <option value="{{ $item->id_barang }}" {{ (string) $idBarang === (string) $item->id_barang ? 'selected' : '' }}>
                            {{ $item->kode_barang }} - {{ $item->nama_barang }}
                        </option>
                        @endforeach
                    </select>

                    <input type="date"
                        name="tanggal_mulai"
                        value="{{ $tanggalMulai }}"
                        class="border-gray-300 rounded-md shadow-sm">

                    <input type="date"
                        name="tanggal_selesai"
                        value="{{ $tanggalSelesai }}"
                        class="border-gray-300 rounded-md shadow-sm">

                    <div class="md:col-span-6 flex gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                            Filter
                        </button>

                        <a href="{{ route('riwayat-stok.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Kode Barang</th>
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-center">Jenis</th>
                                <th class="border px-3 py-2 text-right">Jumlah</th>
                                <th class="border px-3 py-2 text-right">Stok Sebelum</th>
                                <th class="border px-3 py-2 text-right">Stok Sesudah</th>
                                <th class="border px-3 py-2 text-left">Sumber</th>
                                <th class="border px-3 py-2 text-left">Keterangan</th>
                                <th class="border px-3 py-2 text-left">Dibuat Oleh</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($riwayatStok as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($riwayatStok->currentPage() - 1) * $riwayatStok->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->barang->kode_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->barang->nama_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->jenis_pergerakan === 'masuk')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">
                                        Masuk
                                    </span>
                                    @elseif ($item->jenis_pergerakan === 'keluar')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">
                                        Keluar
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm">
                                        Penyesuaian
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $item->jumlah }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $item->stok_sebelum }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    {{ $item->stok_sesudah }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->sumber_transaksi ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->keterangan ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->user->nama_user ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="border px-3 py-4 text-center text-gray-500">
                                    Data riwayat stok belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $riwayatStok->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>