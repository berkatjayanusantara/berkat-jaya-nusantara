<x-app-layout>
    @php
    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $nomorDokumen = $pembelian->nomor_surat_jalan ?: 'SJ-SUP-' . $pembelian->nomor_pembelian;
    $statusPenerimaan = $pembelian->status_penerimaan ?? 'lengkap';
    $nomorPembelianTampil = !empty($pembelian->nomor_dokumen_asli) ? $pembelian->nomor_dokumen_asli : $pembelian->nomor_pembelian;
    $nomorSistemBerbeda = !empty($pembelian->nomor_dokumen_asli) && !empty($pembelian->nomor_pembelian) && $pembelian->nomor_dokumen_asli !== $pembelian->nomor_pembelian;
    @endphp

    <style>
        @media print {
            @page {
                size: A4;
                margin: 14mm;
            }

            nav,
            header,
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-area {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-table th,
        .doc-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            font-size: 13px;
        }

        .doc-table th {
            background: #f3f4f6;
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Surat Jalan Supplier
            </h2>

            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                    Cetak
                </button>

                <a href="{{ route('pembelian.show', $pembelian->id_pembelian) }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8 print-area">

                <div class="flex justify-between border-b-2 border-gray-800 pb-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">SURAT JALAN SUPPLIER</h1>
                        <p class="text-sm text-gray-600">
                            Dokumen pengiriman barang dari supplier ke {{ $namaPerusahaan }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-700">
                            No. Surat Jalan:
                            <strong>{{ $nomorDokumen }}</strong>
                        </p>
                        <p class="text-sm text-gray-700">
                            No. DO:
                            <strong>{{ $pembelian->nomor_delivery_order ?? '-' }}</strong>
                        </p>
                        <p class="text-sm text-gray-700">
                            No. Nota Pembelian:
                            <strong>{{ $nomorPembelianTampil }}</strong>
                        </p>
                        @if ($nomorSistemBerbeda)
                        <p class="text-xs text-gray-500">
                            No Sistem: {{ $pembelian->nomor_pembelian }}
                        </p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-semibold mb-2">Pengirim</h3>

                        <table class="w-full text-sm">
                            <tr>
                                <td class="py-1 w-32">Supplier</td>
                                <td class="py-1">: {{ $pembelian->supplier->nama_supplier ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Telepon</td>
                                <td class="py-1">: {{ $pembelian->supplier->nomor_telepon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Alamat</td>
                                <td class="py-1">: {{ $pembelian->supplier->alamat ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-2">Penerima</h3>

                        <table class="w-full text-sm">
                            <tr>
                                <td class="py-1 w-32">Perusahaan</td>
                                <td class="py-1">: {{ $namaPerusahaan }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Tanggal Terima</td>
                                <td class="py-1">: {{ $pembelian->tanggal_pembelian->format('d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="py-1">Status Terima</td>
                                <td class="py-1">
                                    :
                                    @if ($statusPenerimaan === 'lengkap')
                                    Lengkap
                                    @elseif ($statusPenerimaan === 'sebagian')
                                    Sebagian
                                    @else
                                    Belum Dikirim
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <p class="text-sm mb-4">
                    Barang berikut dikirim oleh supplier dan diterima berdasarkan jumlah yang masuk ke gudang:
                </p>

                <table class="doc-table mb-6">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th style="width: 120px;">Jumlah Diterima</th>
                            <th style="width: 140px;">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($pembelian->detailPembelian as $detail)
                        @php
                        $jumlahDipesan = $detail->jumlah_dipesan ?? $detail->jumlah;
                        $jumlahDiterima = $detail->jumlah;
                        $sisaBelumDikirim = max($jumlahDipesan - $jumlahDiterima, 0);
                        $satuan = $detail->barang->satuan ?? '';
                        @endphp

                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $detail->barang->kode_barang ?? '-' }}</td>
                            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                            <td class="text-right">
                                {{ $jumlahDiterima }} {{ strtoupper($satuan) }}
                            </td>
                            <td>
                                @if ($sisaBelumDikirim > 0)
                                Sebagian
                                @else
                                Lengkap
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mb-8 text-sm">
                    <p class="font-semibold mb-1">Catatan Penerimaan:</p>
                    <div class="border rounded p-3 min-h-[60px]">
                        {{ $pembelian->catatan ?? 'Barang diterima sesuai jumlah yang tercatat pada sistem.' }}
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 text-center text-sm mt-12">
                    <div>
                        <p>Supplier / Pengirim,</p>
                        <div class="h-20"></div>
                        <p class="font-semibold border-t pt-2">
                            {{ $pembelian->supplier->nama_supplier ?? 'Supplier' }}
                        </p>
                    </div>

                    <div>
                        <p>Penerima Barang,</p>
                        <div class="h-20"></div>
                        <p class="font-semibold border-t pt-2">
                            (........................)
                        </p>
                    </div>

                    <div>
                        <p>Admin,</p>
                        <div class="h-20"></div>
                        <p class="font-semibold border-t pt-2">
                            {{ $pembelian->user->nama_user ?? 'Admin' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>