<x-app-layout>
    @php
    $pajakDitambahkan = $pembelian->pajak_ditambahkan ?? true;
    $statusPenerimaan = $pembelian->status_penerimaan ?? 'lengkap';
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Pembelian
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Informasi Pembelian</h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Nomor Pembelian</td>
                                <td class="py-1">: {{ $pembelian->nomor_pembelian }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Tanggal</td>
                                <td class="py-1">: {{ $pembelian->tanggal_pembelian->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Supplier</td>
                                <td class="py-1">: {{ $pembelian->supplier->nama_supplier ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Status Penerimaan</td>
                                <td class="py-1">
                                    :
                                    @if ($statusPenerimaan === 'lengkap')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Lengkap
                                    </span>
                                    @elseif ($statusPenerimaan === 'sebagian')
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                        Sebagian
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                        Belum Dikirim
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Dibuat Oleh</td>
                                <td class="py-1">: {{ $pembelian->user->nama_user ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Catatan</td>
                                <td class="py-1">: {{ $pembelian->catatan ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Ringkasan Total</h3>

                        <div class="bg-gray-50 border rounded-md p-4">
                            <div class="flex justify-between mb-2">
                                <span>Subtotal Barang Diterima</span>
                                <strong>
                                    Rp {{ number_format($pembelian->subtotal, 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>
                                    Pajak {{ number_format($pembelian->persentase_pajak, 2, ',', '.') }}%

                                    @if (!$pajakDitambahkan)
                                    <small class="text-gray-500">
                                        (ditampilkan saja)
                                    </small>
                                    @endif
                                </span>

                                <strong>
                                    Rp {{ number_format($pembelian->nilai_pajak, 0, ',', '.') }}
                                </strong>
                            </div>

                            @if (!$pajakDitambahkan)
                            <div class="mb-2 text-sm text-gray-500">
                                Pajak tidak ditambahkan ke total akhir.
                            </div>
                            @endif

                            <div class="flex justify-between border-t pt-2 text-lg">
                                <span>Total Akhir</span>
                                <strong>
                                    Rp {{ number_format($pembelian->total_akhir, 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="font-semibold text-lg mb-3">Daftar Barang Dibeli</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Kode Barang</th>
                                <th class="border px-3 py-2 text-left">Nama Barang</th>
                                <th class="border px-3 py-2 text-right">Dipesan</th>
                                <th class="border px-3 py-2 text-right">Diterima</th>
                                <th class="border px-3 py-2 text-right">Sisa</th>
                                <th class="border px-3 py-2 text-right">Harga Beli</th>
                                <th class="border px-3 py-2 text-right">Subtotal Diterima</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pembelian->detailPembelian as $detail)
                            @php
                            $jumlahDipesan = $detail->jumlah_dipesan ?? $detail->jumlah;
                            $jumlahDiterima = $detail->jumlah;
                            $sisaBelumDikirim = max($jumlahDipesan - $jumlahDiterima, 0);
                            @endphp

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
                                    {{ $jumlahDipesan }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    {{ $jumlahDiterima }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    @if ($sisaBelumDikirim > 0)
                                    <span class="text-yellow-700 font-semibold">
                                        {{ $sisaBelumDikirim }}
                                    </span>
                                    @else
                                    0
                                    @endif
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($statusPenerimaan === 'sebagian')
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md text-yellow-800">
                    Sebagian barang belum dikirim oleh supplier. Stok hanya bertambah sesuai jumlah barang yang sudah diterima.
                </div>
                @endif

                <div class="flex justify-end mt-6">
                    <a href="{{ route('pembelian.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>