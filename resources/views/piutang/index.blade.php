<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Piutang Customer
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" action="{{ route('piutang.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari invoice atau customer..."
                        class="md:col-span-2 border-gray-300 rounded-md shadow-sm">

                    <select name="status"
                        class="border-gray-300 rounded-md shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="belum_lunas" {{ $status === 'belum_lunas' ? 'selected' : '' }}>
                            Belum Lunas
                        </option>
                        <option value="sebagian_dibayar" {{ $status === 'sebagian_dibayar' ? 'selected' : '' }}>
                            Sebagian Dibayar
                        </option>
                        <option value="lunas" {{ $status === 'lunas' ? 'selected' : '' }}>
                            Lunas
                        </option>
                        <option value="jatuh_tempo" {{ $status === 'jatuh_tempo' ? 'selected' : '' }}>
                            Jatuh Tempo
                        </option>
                    </select>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                            Filter
                        </button>

                        <a href="{{ route('piutang.index') }}"
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
                                <th class="border px-3 py-2 text-left">No Invoice</th>
                                <th class="border px-3 py-2 text-left">Customer</th>
                                <th class="border px-3 py-2 text-right">Total Piutang</th>
                                <th class="border px-3 py-2 text-right">Dibayar</th>
                                <th class="border px-3 py-2 text-right">Sisa</th>
                                <th class="border px-3 py-2 text-center">Jatuh Tempo</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($piutang as $item)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($piutang->currentPage() - 1) * $piutang->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->nomor_invoice }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->customer->nama_customer ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->total_piutang, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($item->total_dibayar, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right font-semibold">
                                    Rp {{ number_format($item->sisa_piutang, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ $item->tanggal_jatuh_tempo ? $item->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($item->status_piutang === 'lunas')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">
                                        Lunas
                                    </span>
                                    @elseif ($item->status_piutang === 'sebagian_dibayar')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm">
                                        Sebagian
                                    </span>
                                    @elseif ($item->status_piutang === 'jatuh_tempo')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">
                                        Jatuh Tempo
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                        Belum Lunas
                                    </span>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('piutang.show', $item->id_piutang) }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Detail
                                        </a>

                                        @if ($item->status_piutang !== 'lunas')
                                        <a href="{{ route('piutang.bayar', $item->id_piutang) }}"
                                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            Bayar
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="border px-3 py-4 text-center text-gray-500">
                                    Data piutang belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $piutang->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>