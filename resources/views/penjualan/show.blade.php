<x-app-layout>
    @php
    $pajakDitambahkan = $penjualan->pajak_ditambahkan ?? true;
    $backUrl = request('back_url', route('penjualan.index'));

    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277';

    $formatAngkaInvoice = function ($angka) {
    return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
    };

    $terbilang = function ($nilai) use (&$terbilang) {
    $nilai = abs((int) $nilai);
    $huruf = [
    '',
    'satu',
    'dua',
    'tiga',
    'empat',
    'lima',
    'enam',
    'tujuh',
    'delapan',
    'sembilan',
    'sepuluh',
    'sebelas'
    ];

    if ($nilai < 12) {
        return $huruf[$nilai];
        }

        if ($nilai < 20) {
        return $terbilang($nilai - 10) . ' belas' ;
        }

        if ($nilai < 100) {
        return $terbilang(floor($nilai / 10)) . ' puluh ' . $terbilang($nilai % 10);
        }

        if ($nilai < 200) {
        return 'seratus ' . $terbilang($nilai - 100);
        }

        if ($nilai < 1000) {
        return $terbilang(floor($nilai / 100)) . ' ratus ' . $terbilang($nilai % 100);
        }

        if ($nilai < 2000) {
        return 'seribu ' . $terbilang($nilai - 1000);
        }

        if ($nilai < 1000000) {
        return $terbilang(floor($nilai / 1000)) . ' ribu ' . $terbilang($nilai % 1000);
        }

        if ($nilai < 1000000000) {
        return $terbilang(floor($nilai / 1000000)) . ' juta ' . $terbilang($nilai % 1000000);
        }

        if ($nilai < 1000000000000) {
        return $terbilang(floor($nilai / 1000000000)) . ' miliar ' . $terbilang($nilai % 1000000000);
        }

        return $terbilang(floor($nilai / 1000000000000)) . ' triliun ' . $terbilang($nilai % 1000000000000);
        };

        $bersihkanTerbilang=function ($teks) {
        $teks=trim(preg_replace('/\s+/', ' ' , $teks));
        return $teks==='' ? 'nol' : $teks;
        };

        $terbilangTotal=$bersihkanTerbilang($terbilang(round($penjualan->total_akhir))) . ' rupiah';

        $invoiceFileBase = 'Invoice-' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', $penjualan->nomor_invoice ?? 'nota');
        $invoiceFileBase = trim(preg_replace('/-+/', '-', $invoiceFileBase), '-');
        @endphp

        <style>
            :root {
                --invoice-primary: #000000;
                --invoice-primary-soft: #ffffff;
                --invoice-secondary: #000000;
                --invoice-muted: #000000;
                --invoice-line: #000000;
                --invoice-light-line: #000000;
                --stamp-red: #b91c1c;
            }

            .invoice-copy {
                border: none;
                padding: 12px;
                background: #ffffff;
                margin-bottom: 12px;
            }

            .invoice-copy-header {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                border-bottom: 2px solid var(--invoice-primary);
                padding: 4px 90px 8px;
                margin-bottom: 8px;
                min-height: 76px;
            }

            .logo-placeholder {
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 62px;
                height: 62px;
                border: 1.5px dashed var(--invoice-primary);
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                background: #ffffff;
            }

            .company-kop {
                width: 100%;
                text-align: center;
            }

            .company-name {
                font-size: 20px;
                font-weight: 900;
                letter-spacing: 0.6px;
                color: var(--invoice-secondary);
                line-height: 1.1;
                text-transform: uppercase;
            }

            .company-info {
                font-size: 11.5px;
                color: var(--invoice-secondary);
                margin-top: 3px;
                line-height: 1.3;
            }

            .copy-label {
                position: absolute;
                right: 0;
                top: 50%;
                transform: translateY(-50%);
                border: 1px solid var(--invoice-primary);
                color: var(--invoice-primary);
                background: var(--invoice-primary-soft);
                border-radius: 999px;
                padding: 4px 10px;
                font-size: 10px;
                font-weight: 800;
                white-space: nowrap;
            }

            .invoice-title-row {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 8px;
            }

            .invoice-title {
                font-size: 15px;
                font-weight: 800;
                color: var(--invoice-secondary);
            }

            .invoice-number {
                font-size: 12px;
                font-weight: 700;
                color: var(--invoice-primary);
            }

            .invoice-quick-info {
                text-align: right;
                font-size: 11px;
                color: var(--invoice-secondary);
                line-height: 1.35;
            }

            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 14px;
                margin-bottom: 8px;
            }

            .invoice-section-title {
                font-size: 12px;
                font-weight: 800;
                margin-bottom: 4px;
                color: var(--invoice-primary);
                border-bottom: 1px solid var(--invoice-line);
                padding-bottom: 2px;
            }

            .info-table {
                width: 100%;
                font-size: 11px;
                color: var(--invoice-secondary);
            }

            .info-table td {
                padding: 1px 0;
                vertical-align: top;
            }

            .items-title-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-top: 2px;
                margin-bottom: 3px;
            }

            .items-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                color: var(--invoice-secondary);
            }

            .items-table thead th {
                background: var(--invoice-primary-soft);
                color: var(--invoice-secondary);
                border-top: 1.5px solid var(--invoice-primary);
                border-bottom: 1px solid var(--invoice-primary);
                border-left: 1px solid var(--invoice-line);
                border-right: 1px solid var(--invoice-line);
                padding: 5px 4px;
                font-weight: 800;
            }

            .items-table thead th:first-child {
                border-left: none;
            }

            .items-table thead th:last-child {
                border-right: none;
            }

            .items-table tbody td {
                border-bottom: 1px solid var(--invoice-line);
                border-left: 1px solid var(--invoice-light-line);
                border-right: 1px solid var(--invoice-light-line);
                padding: 5px 4px;
                vertical-align: top;
            }

            .items-table tbody td:first-child {
                border-left: none;
            }

            .items-table tbody td:last-child {
                border-right: none;
            }

            .items-table tbody tr:last-child td {
                border-bottom: 1.5px solid var(--invoice-primary);
            }

            .item-name {
                font-weight: 800;
                color: var(--invoice-secondary);
            }

            .item-formula {
                color: var(--invoice-muted);
                font-size: 10px;
                margin-top: 1px;
            }

            .total-inline-wrapper {
                display: flex;
                justify-content: flex-end;
                margin-top: 5px;
                margin-bottom: 6px;
            }

            .total-inline {
                width: 250px;
                max-width: 100%;
                font-size: 11px;
                color: var(--invoice-secondary);
            }

            .total-inline-row {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                padding: 2px 0;
            }

            .total-inline-row span:first-child {
                white-space: nowrap;
            }

            .total-inline-total {
                border-top: 1.5px solid var(--invoice-primary);
                margin-top: 3px;
                padding-top: 4px;
                font-size: 12px;
                font-weight: 900;
                color: var(--invoice-primary);
            }

            .pajak-note {
                font-size: 10px;
                color: var(--invoice-muted);
                text-align: right;
                margin-top: 1px;
            }

            .bottom-info-area {
                display: grid;
                grid-template-columns: 1.35fr 0.65fr;
                gap: 20px;
                margin-top: 6px;
            }

            .terbilang-box {
                font-size: 11px;
                color: var(--invoice-secondary);
                line-height: 1.35;
                padding-top: 2px;
            }

            .terbilang-label {
                font-weight: 800;
                color: var(--invoice-primary);
            }

            .terbilang-text {
                font-style: italic;
                color: var(--invoice-secondary);
            }

            .terbilang-stamp-area {
                position: relative;
                height: 0;
                margin: 0;
                padding: 0;
                overflow: visible;
            }

            .terbilang-stamp-area .stempel-manual {
                left: 32px;
                top: 10px;
                transform: none;
            }

            .signature-area {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-top: 8px;
                font-size: 11px;
                text-align: center;
                color: var(--invoice-secondary);
            }

            .signature-box {
                position: relative;
                min-height: 72px;
            }

            .signature-name {
                margin-top: 42px;
                padding-top: 3px;
                position: relative;
                z-index: 2;
                font-weight: 700;
            }

            .receiver-signature-box,
            .company-signature-box {
                position: relative;
                overflow: visible;
            }

            .company-signature-label {
                position: relative;
                z-index: 2;
                font-weight: 700;
            }

            .copy-divider {
                border-top: 1.5px dashed var(--invoice-muted);
                margin: 8px 0 12px;
                position: relative;
            }

            .copy-divider span {
                position: absolute;
                top: -9px;
                left: 50%;
                transform: translateX(-50%);
                background: #ffffff;
                padding: 0 8px;
                font-size: 10px;
                color: var(--invoice-muted);
            }

            .stempel-manual {
                position: absolute;
                left: 50%;
                top: 2px;
                width: 230px;
                max-width: 100%;
                padding: 9px 14px;
                border: none;
                color: var(--stamp-red);
                background: transparent;
                text-align: center;
                font-family: "Times New Roman", serif;
                transform: translateX(-50%) rotate(-7deg);
                box-sizing: border-box;
                opacity: 0.90;
                z-index: 8;
                pointer-events: none;
            }

            .stempel-manual::before {
                display: none;
                content: none;
            }

            .stempel-manual::after {
                display: none;
                content: none;
            }

            .stempel-content {
                position: relative;
                z-index: 2;
            }

            .stempel-company {
                font-size: 14px;
                font-weight: 800;
                line-height: 1.1;
            }

            .stempel-bank {
                font-size: 12px;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 5px;
            }

            .stempel-bank:last-child {
                margin-bottom: 0;
            }

            .stempel-separator {
                width: 70%;
                border-top: 1px dashed var(--stamp-red);
                margin: 5px auto;
            }

            @media print {
                @page {
                    size: A4 portrait;
                    margin: 6mm;
                }

                html,
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    color: #000000 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                nav,
                header,
                .no-print {
                    display: none !important;
                }

                .print-wrapper,
                .print-container,
                .invoice-box {
                    padding: 0 !important;
                    margin: 0 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-shadow: none !important;
                    border-radius: 0 !important;
                    background: #ffffff !important;
                }

                .invoice-copy {
                    min-height: 134mm !important;
                    max-height: none !important;
                    overflow: visible !important;
                    border: none !important;
                    padding: 4mm !important;
                    margin: 0 !important;
                    box-sizing: border-box !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .copy-divider {
                    margin: 2.5mm 0 !important;
                    border-top: 1px dashed #000000 !important;
                }

                .copy-divider span {
                    top: -6px !important;
                    font-size: 7px !important;
                    color: #000000 !important;
                }

                .invoice-copy-header {
                    position: relative !important;
                    justify-content: center !important;
                    padding: 1mm 21mm 2mm !important;
                    margin-bottom: 4px !important;
                    min-height: 16mm !important;
                    border-bottom: 1.2px solid var(--invoice-primary) !important;
                }

                .logo-placeholder {
                    left: 0 !important;
                    width: 14mm !important;
                    height: 14mm !important;
                    border-color: var(--invoice-primary) !important;
                    border-radius: 2px !important;
                    background: #ffffff !important;
                }

                .company-kop {
                    width: 100% !important;
                    text-align: center !important;
                }

                .company-name {
                    font-size: 12.5px !important;
                    line-height: 1.05 !important;
                    color: #000000 !important;
                    letter-spacing: 0.4px !important;
                }

                .company-info {
                    font-size: 7.8px !important;
                    color: #000000 !important;
                    line-height: 1.18 !important;
                }

                .copy-label {
                    right: 0 !important;
                    font-size: 7.5px !important;
                    padding: 2px 6px !important;
                    border-color: var(--invoice-primary) !important;
                    color: var(--invoice-primary) !important;
                    background: var(--invoice-primary-soft) !important;
                }

                .invoice-title-row {
                    margin-bottom: 3px !important;
                }

                .invoice-title {
                    font-size: 10px !important;
                }

                .invoice-number {
                    font-size: 8.5px !important;
                }

                .invoice-quick-info {
                    font-size: 8px !important;
                    line-height: 1.2 !important;
                }

                .info-grid {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    gap: 10px !important;
                    margin-bottom: 4px !important;
                }

                .invoice-section-title {
                    font-size: 8.5px !important;
                    margin-bottom: 2px !important;
                    padding-bottom: 1px !important;
                    color: var(--invoice-primary) !important;
                    border-bottom: 1px solid #000000 !important;
                }

                .info-table {
                    font-size: 7.8px !important;
                    line-height: 1.15 !important;
                }

                .info-table td {
                    padding: 0 !important;
                }

                .items-title-row {
                    margin-top: 1px !important;
                    margin-bottom: 1px !important;
                }

                .items-table {
                    font-size: 7.5px !important;
                    line-height: 1.15 !important;
                    border-collapse: collapse !important;
                }

                .items-table thead th {
                    padding: 1.5px 2px !important;
                    border-top: 1.2px solid var(--invoice-primary) !important;
                    border-bottom: 1px solid var(--invoice-primary) !important;
                    border-left: 1px solid #000000 !important;
                    border-right: 1px solid #000000 !important;
                    background: var(--invoice-primary-soft) !important;
                    color: #000000 !important;
                }

                .items-table thead th:first-child {
                    border-left: none !important;
                }

                .items-table thead th:last-child {
                    border-right: none !important;
                }

                .items-table tbody td {
                    padding: 1.5px 2px !important;
                    border-bottom: 1px solid #000000 !important;
                    border-left: 1px solid #000000 !important;
                    border-right: 1px solid #000000 !important;
                }

                .items-table tbody td:first-child {
                    border-left: none !important;
                }

                .items-table tbody td:last-child {
                    border-right: none !important;
                }

                .items-table tbody tr:last-child td {
                    border-bottom: 1.2px solid var(--invoice-primary) !important;
                }

                .item-formula {
                    font-size: 7px !important;
                    line-height: 1.1 !important;
                }

                .total-inline-wrapper {
                    margin-top: 2px !important;
                    margin-bottom: 3px !important;
                }

                .total-inline {
                    width: 185px !important;
                    font-size: 7.8px !important;
                    line-height: 1.15 !important;
                }

                .total-inline-row {
                    padding: 0.5px 0 !important;
                }

                .total-inline-total {
                    font-size: 8.8px !important;
                    padding-top: 2px !important;
                    margin-top: 1px !important;
                    border-top: 1.2px solid var(--invoice-primary) !important;
                    color: var(--invoice-primary) !important;
                }

                .pajak-note {
                    font-size: 7px !important;
                    margin-top: 0 !important;
                    color: #000000 !important;
                }

                .bottom-info-area {
                    grid-template-columns: 1.35fr 0.65fr !important;
                    margin-top: 3px !important;
                    gap: 12px !important;
                }

                .terbilang-box {
                    font-size: 7.8px !important;
                    line-height: 1.15 !important;
                }

                .terbilang-stamp-area {
                    position: relative !important;
                    height: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow: visible !important;
                }

                .terbilang-stamp-area .stempel-manual {
                    left: 0mm !important;
                    top: 2mm !important;
                    transform: none !important;
                }

                .signature-area {
                    margin-top: 3px !important;
                    font-size: 7.8px !important;
                    gap: 14px !important;
                }

                .signature-box {
                    min-height: 52px !important;
                }

                .signature-name {
                    margin-top: 29px !important;
                    padding-top: 2px !important;
                }

                .stempel-manual {
                    top: -1px !important;
                    width: 120px !important;
                    padding: 5px 8px !important;
                    border: none !important;
                    color: var(--stamp-red) !important;
                    background: transparent !important;
                    transform: translateX(-50%) !important;
                    opacity: 0.95 !important;
                    z-index: 8 !important;
                }

                .stempel-manual::before {
                    inset: 3px !important;
                    border: none !important;
                }

                .stempel-manual::after {
                    font-size: 16px !important;
                    letter-spacing: 1px !important;
                    color: rgba(185, 28, 28, 0.10) !important;
                }

                .stempel-company {
                    font-size: 9px !important;
                    line-height: 1.05 !important;
                }

                .stempel-bank {
                    font-size: 7.8px !important;
                    line-height: 1.08 !important;
                    margin-bottom: 2px !important;
                }

                .stempel-separator {
                    margin: 2px auto !important;
                    border-top: 1px dashed var(--stamp-red) !important;
                }
            }
        </style>

        <x-slot name="header">
            <div class="flex justify-between items-center no-print">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Invoice Penjualan
                </h2>

                <div class="flex gap-2">
                    <a href="{{ route('penjualan.exportExcel', $penjualan->id_penjualan) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Export Excel
                    </a>

                    <button onclick="cetakInvoiceA4()"
                        class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900">
                        Cetak / Download PDF A4
                    </button>
                </div>
            </div>
        </x-slot>

        <div class="py-6 print-wrapper">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 print-container">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 invoice-box">

                    @foreach (['CUSTOMER', 'ARSIP PERUSAHAAN'] as $copyIndex => $copyLabel)
                    <div class="invoice-copy">
                        <div class="invoice-copy-header">
                            <div class="logo-placeholder"></div>

                            <div class="company-kop">
                                <div class="company-name">
                                    {{ $namaPerusahaan }}
                                </div>
                                <div class="company-info">
                                    {{ $alamatPerusahaan }}<br>
                                    Telp: {{ $teleponPerusahaan }}
                                </div>
                            </div>

                            <div class="copy-label">
                                {{ $copyLabel }}
                            </div>
                        </div>

                        <div class="invoice-title-row">
                            <div>
                                <div class="invoice-title">
                                    INVOICE / NOTA PENJUALAN
                                </div>
                                <div class="invoice-number">
                                    No: {{ $penjualan->nomor_invoice }}
                                </div>
                            </div>

                            <div class="invoice-quick-info">
                                <div>
                                    <strong>Tanggal:</strong>
                                    {{ $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
                                </div>
                                <div>
                                    <strong>Pembayaran:</strong>
                                    {{ ucfirst($penjualan->metode_pembayaran) }}
                                </div>
                                <div>
                                    <strong>Status:</strong>
                                    {{ str_replace('_', ' ', ucfirst($penjualan->status_pembayaran)) }}
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div>
                                <div class="invoice-section-title">
                                    Informasi Customer
                                </div>

                                <table class="info-table">
                                    <tr>
                                        <td style="width: 70px;">Nama</td>
                                        <td>: {{ $penjualan->customer->nama_customer ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon</td>
                                        <td>: {{ $penjualan->customer->nomor_telepon ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>NPWP</td>
                                        <td>: {{ $penjualan->customer->npwp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>: {{ $penjualan->customer->alamat ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div>
                                <div class="invoice-section-title">
                                    Informasi Transaksi
                                </div>

                                <table class="info-table">
                                    <tr>
                                        <td style="width: 85px;">Jatuh Tempo</td>
                                        <td>: {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Admin</td>
                                        <td>: {{ $penjualan->user->nama_user ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Catatan</td>
                                        <td>: {{ $penjualan->catatan ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="items-title-row">
                            <div class="invoice-section-title" style="width: 100%; margin-bottom: 0;">
                                Daftar Barang
                            </div>
                        </div>

                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 24px;" class="text-center">No</th>
                                    <th>Barang</th>
                                    <th style="width: 70px;" class="text-right">Qty</th>
                                    <th style="width: 78px;" class="text-right">Harga</th>
                                    <th style="width: 88px;" class="text-right">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($penjualan->detailPenjualan as $detail)
                                @php
                                $tipePerhitungan = $detail->tipe_perhitungan_harga ?? 'normal';
                                $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
                                $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                                $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                                @endphp

                                <tr>
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <div class="item-name">
                                            {{ $detail->barang->nama_barang ?? '-' }}
                                        </div>

                                        @if ($tipePerhitungan === 'isi_kemasan')
                                        <div class="item-formula">
                                            {{ $detail->jumlah }} {{ $satuanTransaksi }}
                                            x {{ $formatAngkaInvoice($isiPerSatuan) }} {{ $satuanHitung }}
                                            x Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                        </div>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        {{ $detail->jumlah }} {{ $satuanTransaksi }}
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                        <div class="item-formula">
                                            / {{ $tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuanTransaksi }}
                                        </div>
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="total-inline-wrapper">
                            <div class="total-inline">
                                <div class="total-inline-row">
                                    <span>Subtotal</span>
                                    <strong>
                                        Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="total-inline-row">
                                    <span>Pajak {{ number_format($penjualan->persentase_pajak, 2, ',', '.') }}%</span>
                                    <strong>
                                        Rp {{ number_format($penjualan->nilai_pajak, 0, ',', '.') }}
                                    </strong>
                                </div>

                                @if (!$pajakDitambahkan)
                                <div class="pajak-note">
                                    Pajak ditampilkan saja
                                </div>
                                @endif

                                <div class="total-inline-row total-inline-total">
                                    <span>Total Akhir</span>
                                    <strong>
                                        Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="bottom-info-area">
                            <div class="terbilang-box">
                                <span class="terbilang-label">Terbilang :</span>
                                <span class="terbilang-text">{{ $terbilangTotal }}</span>
                            </div>

                            <div></div>
                        </div>

                        <div class="terbilang-stamp-area">
                            <div class="stempel-manual">
                                <div class="stempel-content">
                                    <div class="stempel-company">Berkat</div>
                                    <div class="stempel-bank">BCA : 5280902227</div>

                                    <div class="stempel-separator"></div>

                                    <div class="stempel-company">Berkat</div>
                                    <div class="stempel-bank">OCBC NISP : 565 8000 15150</div>
                                </div>
                            </div>
                        </div>

                        <div class="signature-area">
                            <div class="signature-box receiver-signature-box">
                                <div>Penerima,</div>

                                <div class="signature-name">
                                    {{ $penjualan->customer->nama_customer ?? 'Customer' }}
                                </div>
                            </div>

                            <div class="signature-box company-signature-box">
                                <div class="company-signature-label">Hormat Kami,</div>

                                <div class="signature-name">
                                    {{ $namaPerusahaan }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($copyIndex === 0)
                    <div class="copy-divider">
                        <span>potong / arsip</span>
                    </div>
                    @endif
                    @endforeach

                    <div class="flex justify-end mt-6 no-print">
                        <a href="{{ $backUrl }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Kembali
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <script>
            const invoicePrintTitle = "{{ $invoiceFileBase }}";
            let previousDocumentTitle = document.title;

            function cetakInvoiceA4() {
                previousDocumentTitle = document.title;
                document.title = invoicePrintTitle;
                window.print();

                setTimeout(function() {
                    document.title = previousDocumentTitle;
                }, 1500);
            }

            window.addEventListener('beforeprint', function() {
                previousDocumentTitle = document.title;
                document.title = invoicePrintTitle;
            });

            window.addEventListener('afterprint', function() {
                setTimeout(function() {
                    document.title = previousDocumentTitle;
                }, 500);
            });
        </script>
</x-app-layout>