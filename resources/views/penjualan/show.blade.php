<x-app-layout>
    @php
    $pajakDitambahkan = $penjualan->pajak_ditambahkan ?? true;
    $modePpn = $penjualan->mode_ppn
    ?? ((float) ($penjualan->persentase_pajak ?? 0) <= 0
        ? 'tanpa_ppn'
        : ($pajakDitambahkan ? 'exclude' : 'include' ));

        $labelModePpn=[ 'tanpa_ppn'=> 'Tidak Pakai PPN',
        'include' => 'Harga Sudah Termasuk PPN',
        'exclude' => 'Harga Belum Termasuk PPN',
        ][$modePpn] ?? 'Harga Sudah Termasuk PPN';

        $subtotalKenaPpn = (float) ($penjualan->subtotal_kena_ppn ?? 0);
        $subtotalNonPpn = (float) ($penjualan->subtotal_non_ppn ?? 0);

        if ($subtotalKenaPpn <= 0 && $subtotalNonPpn <=0) {
            foreach ($penjualan->detailPenjualan as $detail) {
            if ($detail->kena_ppn ?? true) {
            $subtotalKenaPpn += (float) $detail->subtotal;
            } else {
            $subtotalNonPpn += (float) $detail->subtotal;
            }
            }
            }

            $dppPpn = (float) ($penjualan->dpp_ppn ?? 0);
            if ($dppPpn <= 0 && $modePpn !=='tanpa_ppn' ) {
                $dppPpn=$modePpn==='include'
                ? ($subtotalKenaPpn * 100 / 111)
                : $subtotalKenaPpn;
                }

                $totalSebelumPenyesuaian=(float) ($penjualan->total_sebelum_penyesuaian ?? $penjualan->total_akhir);
                $jenisPenyesuaianTotal = $penjualan->jenis_penyesuaian_total ?? 'tidak_ada';
                $nominalPenyesuaianTotal = (float) ($penjualan->nominal_penyesuaian_total ?? 0);
                $adaPenyesuaianTotal = $jenisPenyesuaianTotal !== 'tidak_ada' && $nominalPenyesuaianTotal > 0;
                $nilaiPenyesuaianTotal = $jenisPenyesuaianTotal === 'kurang'
                ? -$nominalPenyesuaianTotal
                : $nominalPenyesuaianTotal;
                $labelPenyesuaianTotal = $jenisPenyesuaianTotal === 'tambah'
                ? 'Tambah Total Akhir'
                : ($jenisPenyesuaianTotal === 'kurang' ? 'Kurangi Total Akhir' : 'Tidak Ada');

                $backUrl = request('back_url', route('penjualan.index'));
                $namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
                $alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
                $teleponPerusahaan = '(021) 5664892, 5676277';

                $isInvoiceHistoris = (bool) ($penjualan->is_historical ?? false);
                $nomorInvoiceTampil = $isInvoiceHistoris && !empty($penjualan->nomor_dokumen_asli)
                ? $penjualan->nomor_dokumen_asli
                : $penjualan->nomor_invoice;

                $formatAngkaInvoice = function ($angka) {
                return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
                };

                $terbilang = function ($nilai) use (&$terbilang) {
                $nilai = abs((int) $nilai);
                $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

                if ($nilai < 12) return $huruf[$nilai];
                    if ($nilai < 20) return $terbilang($nilai - 10) . ' belas' ;
                    if ($nilai < 100) return $terbilang(floor($nilai / 10)) . ' puluh ' . $terbilang($nilai % 10);
                    if ($nilai < 200) return 'seratus ' . $terbilang($nilai - 100);
                    if ($nilai < 1000) return $terbilang(floor($nilai / 100)) . ' ratus ' . $terbilang($nilai % 100);
                    if ($nilai < 2000) return 'seribu ' . $terbilang($nilai - 1000);
                    if ($nilai < 1000000) return $terbilang(floor($nilai / 1000)) . ' ribu ' . $terbilang($nilai % 1000);
                    if ($nilai < 1000000000) return $terbilang(floor($nilai / 1000000)) . ' juta ' . $terbilang($nilai % 1000000);
                    if ($nilai < 1000000000000) return $terbilang(floor($nilai / 1000000000)) . ' miliar ' . $terbilang($nilai % 1000000000);
                    return $terbilang(floor($nilai / 1000000000000)) . ' triliun ' . $terbilang($nilai % 1000000000000);
                    };

                    $bersihkanTerbilang=function ($teks) {
                    $teks=trim(preg_replace('/\s+/', ' ' , $teks));
                    return $teks==='' ? 'nol' : $teks;
                    };

                    $terbilangTotal=$bersihkanTerbilang($terbilang(round($penjualan->total_akhir))) . ' rupiah';
                    $invoiceFileBase = 'Invoice-' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', $nomorInvoiceTampil ?? 'nota');
                    $invoiceFileBase = trim(preg_replace('/-+/', '-', $invoiceFileBase), '-');
                    @endphp

                    <style>
                        :root {
                            --invoice-primary: #000000;
                            --invoice-secondary: #000000;
                            --invoice-muted: #000000;
                            --invoice-line: #000000;
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
                            border-radius: 6px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
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
                            line-height: 1.1;
                            text-transform: uppercase;
                        }

                        .company-info {
                            font-size: 11.5px;
                            margin-top: 3px;
                            line-height: 1.3;
                        }

                        .copy-label {
                            position: absolute;
                            right: 0;
                            top: 50%;
                            transform: translateY(-50%);
                            border: 1px solid var(--invoice-primary);
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
                        }

                        .invoice-number {
                            font-size: 12px;
                            font-weight: 700;
                        }

                        .invoice-quick-info {
                            text-align: right;
                            font-size: 11px;
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
                            border-bottom: 1px solid var(--invoice-line);
                            padding-bottom: 2px;
                        }

                        .info-table {
                            width: 100%;
                            font-size: 11px;
                        }

                        .info-table td {
                            padding: 1px 0;
                            vertical-align: top;
                        }

                        .items-title-row {
                            margin-top: 2px;
                            margin-bottom: 3px;
                        }

                        .items-table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 11px;
                        }

                        .items-table th,
                        .items-table td {
                            border: 1px solid #000000;
                            padding: 5px 4px;
                            vertical-align: top;
                        }

                        .items-table th {
                            font-weight: 800;
                            text-align: center;
                        }

                        .item-name {
                            font-weight: 800;
                        }

                        .item-formula {
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
                            width: 300px;
                            max-width: 100%;
                            font-size: 11px;
                        }

                        .total-inline-row {
                            display: flex;
                            justify-content: space-between;
                            gap: 10px;
                            padding: 2px 0;
                        }

                        .total-inline-total {
                            border-top: 1.5px solid var(--invoice-primary);
                            margin-top: 3px;
                            padding-top: 4px;
                            font-size: 12px;
                            font-weight: 900;
                        }

                        .pajak-note {
                            font-size: 10px;
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
                            line-height: 1.35;
                            padding-top: 2px;
                        }

                        .terbilang-label {
                            font-weight: 800;
                        }

                        .terbilang-text {
                            font-style: italic;
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
                        }

                        .signature-box {
                            min-height: 72px;
                        }

                        .signature-name {
                            margin-top: 42px;
                            padding-top: 3px;
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
                                padding: 2mm 34mm 3mm !important;
                                margin-bottom: 7px !important;
                                min-height: 32mm !important;
                                border-bottom: 1.5px solid var(--invoice-primary) !important;
                            }

                            .logo-placeholder {
                                left: 0 !important;
                                width: 30mm !important;
                                height: 30mm !important;
                                border-radius: 5px !important;
                            }

                            .invoice-logo {
                                width: 29mm !important;
                                height: 29mm !important;
                            }

                            .company-name {
                                font-size: 22px !important;
                                letter-spacing: 0.7px !important;
                                line-height: 1.08 !important;
                            }

                            .company-info {
                                font-size: 12.5px !important;
                                line-height: 1.25 !important;
                                margin-top: 4px !important;
                            }

                            .copy-label {
                                right: 0 !important;
                                font-size: 11px !important;
                                padding: 3px 9px !important;
                                border-width: 1px !important;
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
                                gap: 12px !important;
                                margin-bottom: 7px !important;
                            }

                            .invoice-section-title {
                                font-size: 12.5px !important;
                                margin-bottom: 3px !important;
                                padding-bottom: 2px !important;
                            }

                            .items-table th,
                            .items-table td {
                                padding: 3px 4px !important;
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
                            }

                            .bottom-info-area {
                                margin-top: 5px !important;
                                gap: 14px !important;
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

                            /* BAGIAN STEMPEL: TETAP TAMPILAN LAMA, DIPINDAH KE AREA KOSONG KIRI SEJAJAR TOTAL */
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
                                opacity: 0.95 !important;
                            }

                            .stempel-company {
                                font-size: 15px !important;
                                line-height: 1.05 !important;
                            }

                            .stempel-bank {
                                font-size: 12.3px !important;
                                line-height: 1.08 !important;
                                margin-bottom: 3px !important;
                            }

                            .stempel-separator {
                                margin: 3px auto !important;
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
                                        <div class="logo-placeholder">
                                            <img src="{{ asset('assets/img/logo-bjn.png') }}" alt="Logo Berkat Jaya Nusantara" class="invoice-logo">
                                        </div>

                                        <div class="company-kop">
                                            <div class="company-name">{{ $namaPerusahaan }}</div>
                                            <div class="company-info">
                                                {{ $alamatPerusahaan }}<br>
                                                Telp: {{ $teleponPerusahaan }}
                                            </div>
                                        </div>

                                        <div class="copy-label">{{ $copyLabel }}</div>
                                    </div>

                                    <div class="invoice-title-row">
                                        <div>
                                            <div class="invoice-title">INVOICE / NOTA PENJUALAN</div>
                                            <div class="invoice-number">No: {{ $nomorInvoiceTampil }}</div>
                                            @if ($isInvoiceHistoris && !empty($penjualan->nomor_invoice))
                                            <div style="font-size: 10px; margin-top: 1px;">
                                                No Sistem: {{ $penjualan->nomor_invoice }}
                                            </div>
                                            @endif
                                        </div>

                                        <div class="invoice-quick-info">
                                            <div><strong>Tanggal:</strong> {{ $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}</div>
                                            <div><strong>Pembayaran:</strong> {{ ucfirst($penjualan->metode_pembayaran) }}</div>
                                            <div><strong>Status:</strong> {{ str_replace('_', ' ', ucfirst($penjualan->status_pembayaran)) }}</div>
                                        </div>
                                    </div>

                                    <div class="info-grid">
                                        <div>
                                            <div class="invoice-section-title">Informasi Customer</div>
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
                                            <div class="invoice-section-title">Informasi Transaksi</div>
                                            <table class="info-table">
                                                <tr>
                                                    <td style="width: 90px;">Jatuh Tempo</td>
                                                    <td>: {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Admin</td>
                                                    <td>: {{ $penjualan->user->nama_user ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Mode PPN</td>
                                                    <td>: {{ $labelModePpn }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Penyesuaian</td>
                                                    <td>: {{ $labelPenyesuaianTotal }}</td>
                                                </tr>
                                                @if ($adaPenyesuaianTotal && $penjualan->keterangan_penyesuaian_total)
                                                <tr>
                                                    <td>Ket. Penyesuaian</td>
                                                    <td>: {{ $penjualan->keterangan_penyesuaian_total }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td>Faktur Pajak</td>
                                                    <td>: {{ $penjualan->butuh_faktur_pajak ? 'Ya' : 'Tidak' }}</td>
                                                </tr>
                                                @if ($penjualan->butuh_faktur_pajak)
                                                <tr>
                                                    <td>No Faktur</td>
                                                    <td>: {{ $penjualan->nomor_faktur_pajak ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Tgl Faktur</td>
                                                    <td>: {{ $penjualan->tanggal_faktur_pajak ? $penjualan->tanggal_faktur_pajak->format('d-m-Y') : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Nama Faktur</td>
                                                    <td>: {{ $penjualan->nama_faktur_pajak ?? '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td>NPWP Faktur</td>
                                                    <td>: {{ $penjualan->npwp_faktur_pajak ?? '-' }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td>Catatan</td>
                                                    <td>: {{ $penjualan->catatan ?? '-' }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="items-title-row">
                                        <div class="invoice-section-title" style="width: 100%; margin-bottom: 0;">Daftar Barang</div>
                                    </div>

                                    <table class="items-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 24px;">No</th>
                                                <th>Barang</th>
                                                <th style="width: 70px;">Qty</th>
                                                <th style="width: 78px;">Harga</th>
                                                <th style="width: 62px;">PPN</th>
                                                <th style="width: 88px;">Subtotal</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($penjualan->detailPenjualan as $detail)
                                            @php
                                            $tipePerhitungan = $detail->tipe_perhitungan_harga ?? 'normal';
                                            $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
                                            $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                                            $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                                            $detailKenaPpn = (bool) ($detail->kena_ppn ?? true);
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="item-name">{{ $detail->barang->nama_barang ?? '-' }}</div>
                                                    @if ($tipePerhitungan === 'isi_kemasan')
                                                    <div class="item-formula">
                                                        {{ $detail->jumlah }} {{ $satuanTransaksi }}
                                                        x {{ $formatAngkaInvoice($isiPerSatuan) }} {{ $satuanHitung }}
                                                        x Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                                    </div>
                                                    @endif
                                                </td>
                                                <td class="text-right">{{ $detail->jumlah }} {{ $satuanTransaksi }}</td>
                                                <td class="text-right">
                                                    Rp {{ number_format($detail->harga_jual, 0, ',', '.') }}
                                                    <div class="item-formula">/ {{ $tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuanTransaksi }}</div>
                                                </td>
                                                <td class="text-center">
                                                    {{ $detailKenaPpn ? 'PPN' : 'Non PPN' }}
                                                    @if ($detailKenaPpn && ($detail->nilai_ppn ?? 0) > 0)
                                                    <div class="item-formula">
                                                        Rp {{ number_format($detail->nilai_ppn, 0, ',', '.') }}
                                                    </div>
                                                    @endif
                                                </td>
                                                <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
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
                                            <div class="total-inline-row">
                                                <span>Subtotal</span>
                                                <strong>Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</strong>
                                            </div>

                                            <div class="total-inline-row">
                                                <span>Subtotal Kena PPN</span>
                                                <strong>Rp {{ number_format($subtotalKenaPpn, 0, ',', '.') }}</strong>
                                            </div>

                                            <div class="total-inline-row">
                                                <span>Subtotal Non PPN</span>
                                                <strong>Rp {{ number_format($subtotalNonPpn, 0, ',', '.') }}</strong>
                                            </div>

                                            @if ($modePpn !== 'tanpa_ppn')
                                            <div class="total-inline-row">
                                                <span>DPP</span>
                                                <strong>Rp {{ number_format($dppPpn, 0, ',', '.') }}</strong>
                                            </div>

                                            <div class="total-inline-row">
                                                <span>PPN 11%</span>
                                                <strong>Rp {{ number_format($penjualan->nilai_pajak, 0, ',', '.') }}</strong>
                                            </div>
                                            @else
                                            <div class="total-inline-row">
                                                <span>PPN</span>
                                                <strong>Rp 0</strong>
                                            </div>
                                            @endif

                                            <div class="pajak-note">{{ $labelModePpn }}</div>

                                            <div class="total-inline-row">
                                                <span>Total Sebelum Penyesuaian</span>
                                                <strong>Rp {{ number_format($totalSebelumPenyesuaian, 0, ',', '.') }}</strong>
                                            </div>

                                            @if ($adaPenyesuaianTotal)
                                            <div class="total-inline-row">
                                                <span>Penyesuaian</span>
                                                <strong>
                                                    {{ $nilaiPenyesuaianTotal < 0 ? '-' : '+' }}Rp {{ number_format(abs($nilaiPenyesuaianTotal), 0, ',', '.') }}
                                                </strong>
                                            </div>
                                            @endif

                                            <div class="total-inline-row total-inline-total">
                                                <span>Total Akhir</span>
                                                <strong>Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</strong>
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

                                    <div class="signature-area">
                                        <div class="signature-box receiver-signature-box">
                                            <div>Penerima,</div>
                                            <div class="signature-name">{{ $penjualan->customer->nama_customer ?? 'Customer' }}</div>
                                        </div>

                                        <div class="signature-box company-signature-box">
                                            <div class="company-signature-label">Hormat Kami,</div>
                                            <div class="signature-name">{{ $namaPerusahaan }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                <div class="flex justify-end mt-6 no-print">
                                    <a href="{{ $backUrl }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
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