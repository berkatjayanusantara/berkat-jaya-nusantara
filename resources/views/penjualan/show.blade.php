<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Invoice Penjualan
            </h2>

            <button onclick="window.print()"
                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                Cetak Invoice
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="border-b pb-4 mb-6">
                    <h1 class="text-2xl font-bold">INVOICE</h1>
                    <p class="text-gray-600">{{ $penjualan->nomor_invoice }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Informasi Customer</h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Nama Customer</td>
                                <td class="py-1">: {{ $penjualan->customer->nama_customer ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Nomor Telepon</td>
                                <td class="py-1">: {{ $penjualan->customer->nomor_telepon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Alamat</td>
                                <td class="py-1">: {{ $penjualan->customer->alamat ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Informasi Penjualan</h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Tanggal</td>
                                <td class="py-1">: {{ $penjualan->tanggal_penjualan->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Metode Pembayaran</td>
                                <td class="py-1">: {{ ucfirst($penjualan->metode_pembayaran) }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Status Pembayaran</td>
                                <td class="py-1">: {{ str_replace('_', ' ', ucfirst($penjualan->status_pembayaran)) }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Jatuh Tempo</td>
                                <td class="py-1">
                                    : {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Dibuat Oleh</td>
                                <td class="py-1">: {{ $penjualan->user->nama_user ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h3 class="font-semibold text-lg mb-3">Daftar Barang</h3>

                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Kode Barang</th>
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-right">Jumlah</th>
                                <th class="border px-3 py-2 text-right">Harga Jual</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($penjualan->detailPenjualan as $detail)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $detail->barang->kode_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $detail->barang->nama_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $detail->jumlah }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Catatan</h3>
                        <p class="border rounded-md p-4 bg-gray-50">
                            {{ $penjualan->catatan ?? '-' }}
                        </p>

                        @if ($penjualan->piutang)
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <h3 class="font-semibold text-yellow-800 mb-2">Informasi Piutang</h3>
                            <p>Total Piutang: Rp {{ number_format($penjualan->piutang->total_piutang, 0, ',', '.') }}</p>
                            <p>Total Dibayar: Rp {{ number_format($penjualan->piutang->total_dibayar, 0, ',', '.') }}</p>
                            <p>Sisa Piutang: Rp {{ number_format($penjualan->piutang->sisa_piutang, 0, ',', '.') }}</p>
                            <p>Status: {{ str_replace('_', ' ', ucfirst($penjualan->piutang->status_piutang)) }}</p>
                        </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Ringkasan Total</h3>

                        <div class="bg-gray-50 border rounded-md p-4">
                            <div class="flex justify-between mb-2">
                                <span>Subtotal</span>
                                <strong>Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Pajak {{ number_format($penjualan->persentase_pajak, 2, ',', '.') }}%</span>
                                <strong>Rp {{ number_format($penjualan->nilai_pajak, 0, ',', '.') }}</strong>
                            </div>

                            <div class="flex justify-between border-t pt-2 text-lg">
                                <span>Total Akhir</span>
                                <strong>Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <a href="{{ route('penjualan.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>