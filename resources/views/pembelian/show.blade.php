<x-app-layout>
    @php
    $pajakDitambahkan = $pembelian->pajak_ditambahkan ?? true;
    $statusPenerimaan = $pembelian->status_penerimaan ?? 'lengkap';
    $backUrl = request('back_url', route('pembelian.index'));
    $isPembelianHistoris = (bool) ($pembelian->is_historical ?? false);
    $nomorPembelianTampil = !empty($pembelian->nomor_dokumen_asli)
    ? $pembelian->nomor_dokumen_asli
    : $pembelian->nomor_pembelian;
    $nomorSistemBerbeda = !empty($pembelian->nomor_dokumen_asli)
    && !empty($pembelian->nomor_pembelian)
    && $pembelian->nomor_dokumen_asli !== $pembelian->nomor_pembelian;

    $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
    $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
    $teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';

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

        $terbilangTotal=$bersihkanTerbilang($terbilang(round($pembelian->total_akhir))) . ' rupiah';

        $invoiceFileBase = 'Nota-Pembelian-' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', $nomorPembelianTampil ?? 'nota');
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
                border: none;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                background: #ffffff;
                overflow: hidden;
            }

            .invoice-logo {
                width: 58px;
                height: 58px;
                object-fit: contain;
                display: block;
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

            .items-table th,
            .items-table td {
                border: 1px solid #000000;
                padding: 5px 4px;
                vertical-align: top;
            }

            .items-table th {
                background: var(--invoice-primary-soft);
                color: var(--invoice-secondary);
                font-weight: 800;
                text-align: center;
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

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .total-inline-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 20px;
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
                width: 230px;
                max-width: 100%;
                min-height: 64px;
                margin-top: 18px;
                margin-left: 28px;
                overflow: visible;
                flex-shrink: 0;
            }

            .stempel-manual {
                position: relative;
                left: 0;
                top: 0;
                width: 230px;
                max-width: 100%;
                color: var(--stamp-red);
                background: transparent;
                text-align: center;
                font-family: "Times New Roman", serif;
                opacity: 0.90;
                z-index: 8;
                pointer-events: none;
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
                margin-top: 64px;
                padding-top: 3px;
                position: relative;
                z-index: 2;
                font-weight: 700;
            }

            .supplier-signature-box,
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
                    min-height: 285mm !important;
                    overflow: visible !important;
                    border: none !important;
                    padding: 6mm !important;
                    margin: 0 !important;
                    box-sizing: border-box !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .invoice-copy+.invoice-copy {
                    page-break-before: always !important;
                    break-before: page !important;
                }

                .copy-divider {
                    display: none !important;
                }

                .invoice-copy-header {
                    position: relative !important;
                    justify-content: center !important;
                    padding: 2mm 34mm 3mm !important;
                    margin-bottom: 7px !important;
                    min-height: 32mm !important;
                    border-bottom: 1.5px solid var(--invoice-primary) !important;
                }

                .logo-placeholder {
                    left: 0 !important;
                    width: 30mm !important;
                    height: 30mm !important;
                    border: none !important;
                    border-radius: 5px !important;
                    background: #ffffff !important;
                    overflow: hidden !important;
                }

                .invoice-logo {
                    width: 29mm !important;
                    height: 29mm !important;
                    object-fit: contain !important;
                    display: block !important;
                }

                .company-kop {
                    width: 100% !important;
                    text-align: center !important;
                }

                .company-name {
                    font-size: 22px !important;
                    line-height: 1.08 !important;
                    letter-spacing: 0.7px !important;
                    color: #000000 !important;
                }

                .company-info {
                    font-size: 12.5px !important;
                    color: #000000 !important;
                    line-height: 1.25 !important;
                    margin-top: 4px !important;
                }

                .copy-label {
                    right: 0 !important;
                    font-size: 11px !important;
                    padding: 3px 9px !important;
                    border-width: 1px !important;
                    border-color: var(--invoice-primary) !important;
                    color: var(--invoice-primary) !important;
                    background: var(--invoice-primary-soft) !important;
                }

                .invoice-title-row {
                    margin-bottom: 6px !important;
                }

                .invoice-title {
                    font-size: 16px !important;
                    line-height: 1.2 !important;
                }

                .invoice-number,
                .invoice-quick-info,
                .info-table,
                .items-table,
                .total-inline,
                .terbilang-box,
                .signature-area {
                    font-size: 11.8px !important;
                    line-height: 1.28 !important;
                }

                .info-grid {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    gap: 12px !important;
                    margin-bottom: 7px !important;
                }

                .invoice-section-title {
                    font-size: 12.5px !important;
                    margin-bottom: 3px !important;
                    padding-bottom: 2px !important;
                    color: var(--invoice-primary) !important;
                    border-bottom: 1px solid #000000 !important;
                }

                .info-table td {
                    padding: 0.5px 0 !important;
                }

                .items-title-row {
                    margin-top: 2px !important;
                    margin-bottom: 2px !important;
                }

                .items-table {
                    border-collapse: collapse !important;
                }

                .items-table th,
                .items-table td {
                    border: 1px solid #000000 !important;
                    padding: 3px 4px !important;
                    vertical-align: top !important;
                }

                .items-table th {
                    background: var(--invoice-primary-soft) !important;
                    color: #000000 !important;
                    font-weight: 800 !important;
                    text-align: center !important;
                }

                .item-name {
                    font-size: 12.2px !important;
                }

                .item-formula,
                .pajak-note {
                    font-size: 10px !important;
                    line-height: 1.2 !important;
                }

                .total-inline-wrapper {
                    justify-content: space-between !important;
                    align-items: flex-start !important;
                    gap: 14px !important;
                    margin-top: 5px !important;
                    margin-bottom: 5px !important;
                }

                .total-inline {
                    width: 270px !important;
                }

                .total-inline-row {
                    padding: 1.5px 0 !important;
                }

                .total-inline-total {
                    font-size: 13px !important;
                    padding-top: 3px !important;
                    margin-top: 2px !important;
                    border-top: 1.2px solid var(--invoice-primary) !important;
                    color: var(--invoice-primary) !important;
                }

                .pajak-note {
                    color: #000000 !important;
                }

                .bottom-info-area {
                    grid-template-columns: 1.35fr 0.65fr !important;
                    margin-top: 5px !important;
                    gap: 14px !important;
                }

                .terbilang-stamp-area {
                    position: relative !important;
                    width: 205px !important;
                    max-width: 205px !important;
                    min-height: 76px !important;
                    margin-top: 16px !important;
                    margin-left: 8mm !important;
                    overflow: visible !important;
                    flex-shrink: 0 !important;
                }

                .stempel-manual {
                    position: relative !important;
                    left: 0 !important;
                    top: 0 !important;
                    width: 205px !important;
                    color: var(--stamp-red) !important;
                    background: transparent !important;
                    opacity: 0.95 !important;
                    z-index: 8 !important;
                }

                .stempel-company {
                    font-size: 14.5px !important;
                    line-height: 1.08 !important;
                }

                .stempel-bank {
                    font-size: 12px !important;
                    line-height: 1.1 !important;
                    margin-bottom: 2px !important;
                }

                .stempel-separator {
                    margin: 2px auto !important;
                    border-top: 1px dashed var(--stamp-red) !important;
                }

                .signature-area {
                    margin-top: 6px !important;
                    gap: 16px !important;
                }

                .signature-box {
                    min-height: 58px !important;
                }

                .signature-name {
                    margin-top: 34px !important;
                    padding-top: 2px !important;
                }
            }
        </style>

        <x-slot name="header">
            <div class="flex justify-between items-center no-print">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Invoice Pembelian
                </h2>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ $backUrl }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 no-print">
                        ← Kembali
                    </a>

                    <a href="{{ $isPembelianHistoris ? route('invoice-historis.pembelian.exportExcel', $pembelian->id_pembelian) : route('pembelian.exportExcel', $pembelian->id_pembelian) }}"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Export Excel
                    </a>

                    <a href="{{ route('pembelian.deliveryOrder', $pembelian->id_pembelian) }}"
                        target="_blank"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Cetak DO Supplier
                    </a>

                    <a href="{{ route('pembelian.suratJalan', $pembelian->id_pembelian) }}"
                        target="_blank"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Cetak Surat Jalan Supplier
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

                    <div class="invoice-copy">
                        <div class="invoice-copy-header">
                            <div class="logo-placeholder">
                                <img
                                    src="{{ asset('assets/img/logo-bjn.png') }}"
                                    alt="Logo Berkat Jaya Nusantara"
                                    class="invoice-logo">
                            </div>

                            <div class="company-kop">
                                <div class="company-name">
                                    {{ $namaPerusahaan }}
                                </div>
                                <div class="company-info">
                                    {{ $alamatPerusahaan }}<br>
                                    Telp: {{ $teleponPerusahaan }}
                                </div>
                            </div>

                        </div>

                        <div class="invoice-title-row">
                            <div>
                                <div class="invoice-title">
                                    INVOICE / NOTA PEMBELIAN
                                </div>
                                <div class="invoice-number">
                                    No: {{ $nomorPembelianTampil }}
                                </div>
                                @if ($nomorSistemBerbeda)
                                <div style="font-size: 10px; margin-top: 1px;">
                                    No Sistem: {{ $pembelian->nomor_pembelian }}
                                </div>
                                @endif
                            </div>

                            <div class="invoice-quick-info">
                                <div>
                                    <strong>Tanggal:</strong>
                                    {{ $pembelian->tanggal_pembelian ? $pembelian->tanggal_pembelian->format('d-m-Y') : '-' }}
                                </div>
                                <div>
                                    <strong>Status Terima:</strong>
                                    @if ($statusPenerimaan === 'lengkap')
                                    Lengkap
                                    @elseif ($statusPenerimaan === 'sebagian')
                                    Sebagian
                                    @else
                                    Belum Dikirim
                                    @endif
                                </div>
                                <div>
                                    <strong>Admin:</strong>
                                    {{ $pembelian->user->nama_user ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div>
                                <div class="invoice-section-title">
                                    Informasi Supplier
                                </div>

                                <table class="info-table">
                                    <tr>
                                        <td style="width: 70px;">Nama</td>
                                        <td>: {{ $pembelian->supplier->nama_supplier ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Telepon</td>
                                        <td>: {{ $pembelian->supplier->nomor_telepon ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>NPWP</td>
                                        <td>: {{ $pembelian->supplier->npwp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>: {{ $pembelian->supplier->alamat ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div>
                                <div class="invoice-section-title">
                                    Informasi Dokumen
                                </div>

                                <table class="info-table">
                                    <tr>
                                        <td style="width: 90px;">No. DO</td>
                                        <td>: {{ $pembelian->nomor_delivery_order ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>No. Surat Jalan</td>
                                        <td>: {{ $pembelian->nomor_surat_jalan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Catatan</td>
                                        <td>: {{ $pembelian->catatan ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="items-title-row">
                            <div class="invoice-section-title" style="width: 100%; margin-bottom: 0;">
                                Daftar Barang Dibeli
                            </div>
                        </div>

                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 24px;" class="text-center">No</th>
                                    <th>Barang</th>
                                    <th style="width: 58px;" class="text-right">Dipesan</th>
                                    <th style="width: 58px;" class="text-right">Diterima</th>
                                    <th style="width: 58px;" class="text-right">Sisa</th>
                                    <th style="width: 78px;" class="text-right">Harga</th>
                                    <th style="width: 88px;" class="text-right">Subtotal</th>
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
                                    <td class="text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        <div class="item-name">
                                            {{ $detail->barang->nama_barang ?? '-' }}
                                        </div>

                                        <div class="item-formula">
                                            Kode: {{ $detail->barang->kode_barang ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="text-right">
                                        {{ $formatAngkaInvoice($jumlahDipesan) }} {{ strtoupper($satuan) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatAngkaInvoice($jumlahDiterima) }} {{ strtoupper($satuan) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatAngkaInvoice($sisaBelumDikirim) }} {{ strtoupper($satuan) }}
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}
                                    </td>

                                    <td class="text-right">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="total-inline-wrapper">
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

                            <div class="total-inline">
                                @php
                                $biayaLainPembelian = (float) ($pembelian->biaya_lain ?? 0);
                                $potonganDiskonPembelian = (float) ($pembelian->potongan_diskon ?? 0);
                                $keteranganPenyesuaianPembelian = $pembelian->keterangan_penyesuaian_total ?? null;
                                @endphp

                                <div class="total-inline-row">
                                    <span>Subtotal Barang</span>
                                    <strong>Rp {{ number_format($pembelian->subtotal, 0, ',', '.') }}</strong>
                                </div>

                                <div class="total-inline-row">
                                    <span>PPN Supplier</span>
                                    <strong>Rp {{ number_format($pembelian->nilai_pajak, 0, ',', '.') }}</strong>
                                </div>

                                @if ($biayaLainPembelian > 0)
                                <div class="total-inline-row">
                                    <span>Biaya Lain / Ongkir</span>
                                    <strong>Rp {{ number_format($biayaLainPembelian, 0, ',', '.') }}</strong>
                                </div>
                                @endif

                                @if ($potonganDiskonPembelian > 0)
                                <div class="total-inline-row">
                                    <span>Potongan / Diskon</span>
                                    <strong>- Rp {{ number_format($potonganDiskonPembelian, 0, ',', '.') }}</strong>
                                </div>
                                @endif

                                <div class="total-inline-row total-inline-total">
                                    <span>Total Akhir</span>
                                    <strong>Rp {{ number_format($pembelian->total_akhir, 0, ',', '.') }}</strong>
                                </div>

                                @if ($keteranganPenyesuaianPembelian)
                                <div class="pajak-note">
                                    Catatan: {{ $keteranganPenyesuaianPembelian }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="bottom-info-area">
                            <div class="terbilang-box">
                                <span class="terbilang-label">Terbilang :</span>
                                <span class="terbilang-text">{{ $terbilangTotal }}</span>
                            </div>

                            <div></div>
                        </div>

                        <div class="signature-area">
                            <div class="signature-box supplier-signature-box">
                                <div>Supplier,</div>

                                <div class="signature-name">
                                    {{ $pembelian->supplier->nama_supplier ?? 'Supplier' }}
                                </div>
                            </div>

                            <div class="signature-box company-signature-box">
                                <div class="company-signature-label">Diterima Oleh,</div>

                                <div class="signature-name">
                                    {{ $namaPerusahaan }}
                                </div>
                            </div>
                        </div>
                    </div>

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
                document.title = invoicePrintTitle + "-" + Date.now();
                window.print();

                setTimeout(function() {
                    document.title = previousDocumentTitle;
                }, 1500);
            }

            window.addEventListener('beforeprint', function() {
                previousDocumentTitle = document.title;
                document.title = invoicePrintTitle + "-" + Date.now();
            });

            window.addEventListener('afterprint', function() {
                setTimeout(function() {
                    document.title = previousDocumentTitle;
                }, 500);
            });
        </script>
</x-app-layout>