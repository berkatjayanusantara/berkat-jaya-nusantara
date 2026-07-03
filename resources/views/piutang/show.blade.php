<x-app-layout>
    @php
    $backUrl = request('back_url', route('piutang.index'));

    $lewatJatuhTempo = $piutang->status_piutang !== 'lunas'
    && $piutang->tanggal_jatuh_tempo
    && $piutang->tanggal_jatuh_tempo->isPast();
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Piutang
            </h2>

            @if ($piutang->status_piutang !== 'lunas')
            <a href="{{ route('piutang.bayar', ['piutang' => $piutang->id_piutang, 'back_url' => $backUrl]) }}"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                + Bayar Piutang
            </a>
            @endif
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

            @if ($lewatJatuhTempo)
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                Piutang ini sudah melewati tanggal jatuh tempo.
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Informasi Piutang</h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Nomor Invoice</td>
                                <td class="py-1">: {{ $piutang->nomor_invoice }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Customer</td>
                                <td class="py-1">: {{ $piutang->customer->nama_customer ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Nomor Telepon</td>
                                <td class="py-1">: {{ $piutang->customer->nomor_telepon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Tanggal Jatuh Tempo</td>
                                <td class="py-1">
                                    : {{ $piutang->tanggal_jatuh_tempo ? $piutang->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Status</td>
                                <td class="py-1">
                                    :
                                    @if ($piutang->status_piutang === 'lunas')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Lunas
                                    </span>
                                    @elseif ($piutang->status_piutang === 'sebagian_dibayar')
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                        Sebagian Dibayar
                                    </span>
                                    @else
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                        Belum Lunas
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Keterangan</td>
                                <td class="py-1">
                                    :
                                    @if ($piutang->status_piutang === 'lunas')
                                    <span class="text-green-700 font-semibold">
                                        Selesai
                                    </span>
                                    @elseif ($lewatJatuhTempo)
                                    <span class="text-red-700 font-semibold">
                                        Lewat Jatuh Tempo
                                    </span>
                                    @else
                                    <span class="text-yellow-700 font-semibold">
                                        Berjalan
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Catatan</td>
                                <td class="py-1">: {{ $piutang->catatan ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3">Ringkasan Pembayaran</h3>

                        <div class="bg-gray-50 border rounded-md p-4">
                            <div class="flex justify-between mb-2">
                                <span>Total Piutang</span>
                                <strong>
                                    Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="flex justify-between mb-2">
                                <span>Total Dibayar</span>
                                <strong>
                                    Rp {{ number_format($piutang->total_dibayar, 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="flex justify-between border-t pt-2 text-lg">
                                <span>Sisa Piutang</span>
                                <strong class="{{ $piutang->sisa_piutang > 0 ? 'text-red-700' : 'text-green-700' }}">
                                    Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>

                        @if ($piutang->penjualan)
                        <div class="mt-4">
                            <a href="{{ route('penjualan.show', ['penjualan' => $piutang->penjualan->id_penjualan, 'back_url' => url()->full()]) }}"
                                class="text-blue-600 hover:underline">
                                Lihat Invoice Penjualan
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <h3 class="font-semibold text-lg mb-3">Riwayat Pembayaran</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal Pembayaran</th>
                                <th class="border px-3 py-2 text-right">Nominal</th>
                                <th class="border px-3 py-2 text-center">Metode</th>
                                <th class="border px-3 py-2 text-left">Catatan</th>
                                <th class="border px-3 py-2 text-left">Dibuat Oleh</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($piutang->pembayaranPiutang as $pembayaran)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $pembayaran->tanggal_pembayaran->format('d-m-Y') }}
                                </td>

                                <td class="border px-3 py-2 text-right">
                                    Rp {{ number_format($pembayaran->nominal_pembayaran, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    {{ ucfirst($pembayaran->metode_pembayaran) }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $pembayaran->catatan ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $pembayaran->user->nama_user ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-center">
                                    <a href="{{ route('piutang.editPembayaran', [$piutang->id_piutang, $pembayaran->id_pembayaran, 'back_url' => url()->current()]) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="border px-3 py-4 text-center text-gray-500">
                                    Belum ada pembayaran untuk piutang ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6">
                    <a href="{{ $backUrl }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>