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

            @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                {{ session('error') }}
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="GET" action="{{ route('pembelian.index') }}" class="mb-4 flex gap-2">
                    <input type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari nomor pembelian, nomor dokumen asli, DO, surat jalan, atau nama supplier..."
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
                                <th class="border px-3 py-2 text-center">Status Terima</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                                <th class="border px-3 py-2 text-right">Pajak</th>
                                <th class="border px-3 py-2 text-right">Total Akhir</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pembelian as $item)
                            @php
                            $statusPenerimaan = $item->status_penerimaan ?? 'lengkap';

                            $isHistoris = (bool) ($item->is_historical ?? false);

                            $nomorPembelianTampil = $item->nomor_dokumen_asli ?: $item->nomor_pembelian;
                            $nomorSistemBerbeda = !empty($item->nomor_dokumen_asli)
                            && !empty($item->nomor_pembelian)
                            && $item->nomor_dokumen_asli !== $item->nomor_pembelian;
                            @endphp

                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration + ($pembelian->currentPage() - 1) * $pembelian->perPage() }}
                                </td>

                                <td class="border px-3 py-2">
                                    <div class="font-semibold">
                                        {{ $nomorPembelianTampil }}
                                    </div>

                                    @if ($isHistoris)
                                    <div class="text-xs text-gray-500">
                                        Historis
                                    </div>

                                    @if ($nomorSistemBerbeda)
                                    <div class="text-xs text-gray-500">
                                        No Sistem: {{ $item->nomor_pembelian }}
                                    </div>
                                    @endif
                                    @else
                                    <div class="text-xs text-gray-500">
                                        Transaksi Sistem
                                    </div>
                                    @endif

                                    @if ($item->nomor_delivery_order)
                                    <div class="text-xs text-gray-500">
                                        DO: {{ $item->nomor_delivery_order }}
                                    </div>
                                    @endif

                                    @if ($item->nomor_surat_jalan)
                                    <div class="text-xs text-gray-500">
                                        SJ: {{ $item->nomor_surat_jalan }}
                                    </div>
                                    @endif
                                </td>

                                <td class="border px-3 py-2 whitespace-nowrap">
                                    {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $item->supplier->nama_supplier ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    @if ($statusPenerimaan === 'lengkap')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">
                                        Lengkap
                                    </span>
                                    @elseif ($statusPenerimaan === 'sebagian')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm">
                                        Sebagian
                                    </span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-sm">
                                        Belum Dikirim
                                    </span>
                                    @endif
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
                                    <div class="flex flex-wrap justify-center gap-2">
                                        @if ($isHistoris)
                                        @php
                                        $backUrlListPembelian = route('pembelian.index', ['search' => $search, 'page' => $pembelian->currentPage()]);
                                        $showUrlHistorisPembelian = route('invoice-historis.pembelian.show', ['pembelian' => $item->id_pembelian, 'back_url' => $backUrlListPembelian]);
                                        @endphp
                                        <a href="{{ $showUrlHistorisPembelian }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Detail
                                        </a>

                                        <a href="{{ route('invoice-historis.pembelian.edit', ['pembelian' => $item->id_pembelian, 'back_url' => $showUrlHistorisPembelian]) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                        @else
                                        @php
                                        $backUrlListPembelianReguler = route('pembelian.index', ['search' => $search, 'page' => $pembelian->currentPage()]);
                                        $showUrlRegulerPembelian = route('pembelian.show', ['pembelian' => $item->id_pembelian, 'back_url' => $backUrlListPembelianReguler]);
                                        @endphp
                                        <a href="{{ $showUrlRegulerPembelian }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Detail
                                        </a>

                                        <a href="{{ route('pembelian.edit', ['pembelian' => $item->id_pembelian, 'back_url' => $showUrlRegulerPembelian]) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                            Edit
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="border px-3 py-4 text-center text-gray-500">
                                    Data pembelian belum tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pembelian->appends(['search' => $search])->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>