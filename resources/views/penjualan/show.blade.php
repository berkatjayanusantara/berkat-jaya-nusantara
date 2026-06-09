<x-app-layout>
    @php
    /*
    Fallback untuk data lama:
    - Jika kolom pajak_ditambahkan belum ada / null, dianggap true.
    - true = pajak ditambahkan ke total akhir.
    - false = pajak hanya ditampilkan, tidak ditambahkan ke total akhir.
    */
    $pajakDitambahkan = $penjualan->pajak_ditambahkan ?? true;
    @endphp

    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }

            html,
            body {
                width: 80mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                font-size: 10px !important;
                color: #000000 !important;
            }

            nav,
            header,
            .no-print {
                display: none !important;
            }

            .print-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-container {
                width: 72mm !important;
                max-width: 72mm !important;
                margin: 0 auto !important;
                padding: 0 !important;
            }

            .invoice-box {
                width: 72mm !important;
                max-width: 72mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }

            .invoice-title {
                font-size: 16px !important;
                text-align: center !important;
                margin-bottom: 2px !important;
            }

            .invoice-number {
                text-align: center !important;
                font-size: 10px !important;
                margin-bottom: 8px !important;
            }

            .section-title {
                font-size: 11px !important;
                font-weight: bold !important;
                margin-top: 8px !important;
                margin-bottom: 4px !important;
                border-top: 1px dashed #000 !important;
                padding-top: 5px !important;
            }

            .print-grid {
                display: block !important;
            }

            .print-grid>div {
                margin-bottom: 6px !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9px !important;
            }

            th,
            td {
                padding: 2px 1px !important;
                border: none !important;
                vertical-align: top !important;
            }

            .table-barang th,
            .table-barang td {
                border-bottom: 1px dashed #999 !important;
            }

            .hide-print {
                display: none !important;
            }

            .text-print-right {
                text-align: right !important;
            }

            .summary-box,
            .catatan-box,
            .piutang-box {
                border: none !important;
                background: #ffffff !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }

            .summary-row {
                display: flex !important;
                justify-content: space-between !important;
                gap: 6px !important;
                margin-bottom: 3px !important;
                font-size: 10px !important;
            }

            .summary-total {
                border-top: 1px dashed #000 !important;
                padding-top: 5px !important;
                margin-top: 5px !important;
                font-size: 12px !important;
                font-weight: bold !important;
            }

            .pajak-note {
                font-size: 9px !important;
                color: #000000 !important;
                margin-bottom: 4px !important;
                font-style: italic !important;
            }

            .footer-print {
                display: block !important;
                margin-top: 10px !important;
                text-align: center !important;
                font-size: 9px !important;
                border-top: 1px dashed #000 !important;
                padding-top: 6px !important;
            }
        }

        .footer-print {
            display: none;
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Invoice Penjualan
            </h2>

            <button onclick="window.print()"
                class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                Cetak Invoice
            </button>
        </div>
    </x-slot>

    <div class="py-6 print-wrapper">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print-container">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 invoice-box">

                <div class="border-b pb-4 mb-6">
                    <h1 class="text-2xl font-bold invoice-title">INVOICE</h1>
                    <p class="text-gray-600 invoice-number">
                        {{ $penjualan->nomor_invoice }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 print-grid">
                    <div>
                        <h3 class="font-semibold text-lg mb-3 section-title">
                            Informasi Customer
                        </h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Nama</td>
                                <td class="py-1">
                                    : {{ $penjualan->customer->nama_customer ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Telepon</td>
                                <td class="py-1">
                                    : {{ $penjualan->customer->nomor_telepon ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Alamat</td>
                                <td class="py-1">
                                    : {{ $penjualan->customer->alamat ?? '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3 section-title">
                            Informasi Penjualan
                        </h3>

                        <table class="w-full">
                            <tr>
                                <td class="py-1 font-medium">Tanggal</td>
                                <td class="py-1">
                                    : {{ $penjualan->tanggal_penjualan->format('d-m-Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Pembayaran</td>
                                <td class="py-1">
                                    : {{ ucfirst($penjualan->metode_pembayaran) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Status</td>
                                <td class="py-1">
                                    : {{ str_replace('_', ' ', ucfirst($penjualan->status_pembayaran)) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Jatuh Tempo</td>
                                <td class="py-1">
                                    : {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium">Admin</td>
                                <td class="py-1">
                                    : {{ $penjualan->user->nama_user ?? '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h3 class="font-semibold text-lg mb-3 section-title">
                    Daftar Barang
                </h3>

                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full border border-gray-200 table-barang">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left hide-print">Kode</th>
                                <th class="border px-3 py-2 text-left">Barang</th>
                                <th class="border px-3 py-2 text-right">Qty</th>
                                <th class="border px-3 py-2 text-right">Harga</th>
                                <th class="border px-3 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($penjualan->detailPenjualan as $detail)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="border px-3 py-2 hide-print">
                                    {{ $detail->barang->kode_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $detail->barang->nama_barang ?? '-' }}
                                </td>

                                <td class="border px-3 py-2 text-right text-print-right">
                                    {{ $detail->jumlah }}
                                </td>

                                <td class="border px-3 py-2 text-right text-print-right">
                                    Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                </td>

                                <td class="border px-3 py-2 text-right text-print-right">
                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print-grid">
                    <div>
                        <h3 class="font-semibold text-lg mb-3 section-title">
                            Catatan
                        </h3>

                        <p class="border rounded-md p-4 bg-gray-50 catatan-box">
                            {{ $penjualan->catatan ?? '-' }}
                        </p>

                        @if ($penjualan->piutang)
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md piutang-box">
                            <h3 class="font-semibold text-yellow-800 mb-2 section-title">
                                Informasi Piutang
                            </h3>

                            <table class="w-full">
                                <tr>
                                    <td>Total Piutang</td>
                                    <td class="text-right text-print-right">
                                        Rp {{ number_format($penjualan->piutang->total_piutang, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Total Dibayar</td>
                                    <td class="text-right text-print-right">
                                        Rp {{ number_format($penjualan->piutang->total_dibayar, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sisa Piutang</td>
                                    <td class="text-right text-print-right">
                                        Rp {{ number_format($penjualan->piutang->sisa_piutang, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="text-right text-print-right">
                                        {{ str_replace('_', ' ', ucfirst($penjualan->piutang->status_piutang)) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg mb-3 section-title">
                            Ringkasan Total
                        </h3>

                        <div class="bg-gray-50 border rounded-md p-4 summary-box">
                            <div class="flex justify-between mb-2 summary-row">
                                <span>Subtotal</span>
                                <strong>
                                    Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="flex justify-between mb-2 summary-row">
                                <span>
                                    Pajak {{ number_format($penjualan->persentase_pajak, 2, ',', '.') }}%

                                    @if (!$pajakDitambahkan)
                                    <small class="text-gray-500">
                                        (ditampilkan saja)
                                    </small>
                                    @endif
                                </span>

                                <strong>
                                    Rp {{ number_format($penjualan->nilai_pajak, 0, ',', '.') }}
                                </strong>
                            </div>

                            @if (!$pajakDitambahkan)
                            <div class="mb-2 text-sm text-gray-500 pajak-note">
                                Pajak tidak ditambahkan ke total akhir.
                            </div>
                            @endif

                            <div class="flex justify-between border-t pt-2 text-lg summary-row summary-total">
                                <span>Total Akhir</span>
                                <strong>
                                    Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-print">
                    Terima kasih atas pembelian Anda.
                </div>

                <div class="flex justify-end mt-6 no-print">
                    <a href="{{ route('penjualan.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Kembali
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>