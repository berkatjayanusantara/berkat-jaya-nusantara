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

        $normalisasiJenisPpn = function ($detail) {
        if (!empty($detail->jenis_ppn) && in_array($detail->jenis_ppn, ['non_ppn', 'ppn_normal', 'ppn_dpp_nilai_lain'], true)) {
        return $detail->jenis_ppn;
        }

        return ($detail->kena_ppn ?? true) ? 'ppn_dpp_nilai_lain' : 'non_ppn';
        };

        $labelJenisPpn = function ($jenisPpn) {
        return [
        'non_ppn' => 'Non PPN',
        'ppn_normal' => 'PPN Normal',
        'ppn_dpp_nilai_lain' => 'PPN Khusus',
        ][$jenisPpn] ?? 'PPN Khusus';
        };

        $isTanpaPpn = $modePpn === 'tanpa_ppn';

        if ($isTanpaPpn) {
        // Jika transaksi tidak memakai PPN, semua barang ditampilkan sebagai daftar barang biasa.
        // Jenis PPN di master/detail tidak dipakai untuk memecah invoice agar tidak muncul label PPN yang membingungkan.
        $detailUmum = $penjualan->detailPenjualan;
        $detailPpnKhusus = collect();
        $subtotalPpnKhusus = 0;
        $subtotalPpnNormal = 0;
        $subtotalNonPpn = (float) $penjualan->detailPenjualan->sum('subtotal');
        $subtotalKenaPpn = 0;
        } else {
        $detailPpnKhusus = $penjualan->detailPenjualan->filter(function ($detail) use ($normalisasiJenisPpn) {
        return $normalisasiJenisPpn($detail) === 'ppn_dpp_nilai_lain';
        });

        $detailUmum = $penjualan->detailPenjualan->reject(function ($detail) use ($normalisasiJenisPpn) {
        return $normalisasiJenisPpn($detail) === 'ppn_dpp_nilai_lain';
        });

        $subtotalPpnKhusus = $detailPpnKhusus->sum('subtotal');
        $subtotalPpnNormal = $detailUmum->filter(fn ($detail) => $normalisasiJenisPpn($detail) === 'ppn_normal')->sum('subtotal');
        $subtotalNonPpn = $detailUmum->filter(fn ($detail) => $normalisasiJenisPpn($detail) === 'non_ppn')->sum('subtotal');
        $subtotalKenaPpn = $subtotalPpnKhusus + $subtotalPpnNormal;

        if ((float) ($penjualan->subtotal_non_ppn ?? 0) > 0 || (float) ($penjualan->subtotal_kena_ppn ?? 0) > 0) {
        $subtotalKenaPpn = (float) ($penjualan->subtotal_kena_ppn ?? $subtotalKenaPpn);
        $subtotalNonPpn = (float) ($penjualan->subtotal_non_ppn ?? $subtotalNonPpn);
        }
        }

        $dppPpn = $isTanpaPpn ? 0 : (float) ($penjualan->dpp_ppn ?? $penjualan->detailPenjualan->sum('dpp_ppn'));
        $nilaiPajak = $isTanpaPpn ? 0 : (float) ($penjualan->nilai_pajak ?? $penjualan->detailPenjualan->sum('nilai_ppn'));

        $totalSebelumPenyesuaian = (float) ($penjualan->total_sebelum_penyesuaian ?? $penjualan->total_akhir);
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
        $nomorInvoiceTampil = !empty($penjualan->nomor_dokumen_asli)
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
                    border-bottom: 2px solid #000;
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
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }

                .invoice-logo {
                    width: 58px;
                    height: 58px;
                    object-fit: contain;
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
                    border: 1px solid #000;
                    border-radius: 999px;
                    padding: 4px 10px;
                    font-size: 10px;
                    font-weight: 800;
                    white-space: nowrap;
                }

                .copy-label-empty {
                    border-color: transparent;
                    color: transparent;
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
                    border-bottom: 1px solid #000;
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

                .items-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                    margin-bottom: 7px;
                }

                .items-table th,
                .items-table td {
                    border: 1px solid #000;
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
                    width: 330px;
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
                    border-top: 1.5px solid #000;
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
                    width: 230px;
                    max-width: 100%;
                    color: #b91c1c;
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
                    border-top: 1px dashed #b91c1c;
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

                    .invoice-copy-header {
                        padding: 2mm 34mm 3mm !important;
                        margin-bottom: 7px !important;
                        min-height: 32mm !important;
                        border-bottom: 1.5px solid #000000 !important;
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

                    .items-table {
                        margin-bottom: 6px !important;
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
                        width: 300px !important;
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

                        @php
                        $adaPpnPadaTransaksi = !$isTanpaPpn && (float) $nilaiPajak > 0;
                        $kelompokCetak = [];

                        if (!$adaPpnPadaTransaksi) {
                        // Jika transaksi benar-benar tidak memiliki PPN, invoice cukup 1 lembar saja.
                        // Semua barang ditampilkan dalam satu daftar agar tidak muncul halaman kosong/kelompok PPN yang tidak relevan.
                        $kelompokCetak[] = [
                        'judul' => 'Daftar Barang',
                        'jenis' => 'tanpa_ppn',
                        'detail' => $penjualan->detailPenjualan,
                        ];
                        } else {
                        // Jika ada PPN, tetap pisahkan sesuai permintaan client:
                        // 1) PPN Normal + Non PPN
                        // 2) PPN DPP Nilai Lain / Khusus
                        // Kelompok yang kosong tidak dicetak agar tidak menghasilkan lembar kosong.
                        if ($detailUmum->count() > 0) {
                        $kelompokCetak[] = [
                        'judul' => 'Daftar Barang',
                        'jenis' => 'umum',
                        'detail' => $detailUmum,
                        ];
                        }

                        if ($detailPpnKhusus->count() > 0) {
                        $kelompokCetak[] = [
                        'judul' => 'Daftar Barang',
                        'jenis' => 'khusus',
                        'detail' => $detailPpnKhusus,
                        ];
                        }

                        if (count($kelompokCetak) === 0) {
                        $kelompokCetak[] = [
                        'judul' => 'Daftar Barang',
                        'jenis' => 'umum',
                        'detail' => $penjualan->detailPenjualan,
                        ];
                        }
                        }
                        @endphp

                        @foreach ($kelompokCetak as $copyIndex => $kelompok)
                        @php
                        $copyDetails = $kelompok['detail'];
                        $copyJenis = $kelompok['jenis'];
                        $copySubtotal = (float) $copyDetails->sum('subtotal');
                        $copyDpp = $isTanpaPpn ? 0 : (float) $copyDetails->sum('dpp_ppn');
                        $copyPpn = $isTanpaPpn ? 0 : (float) $copyDetails->sum('nilai_ppn');
                        $copySubtotalPpnNormal = $copyDetails->filter(fn ($detail) => $normalisasiJenisPpn($detail) === 'ppn_normal')->sum('subtotal');
                        $copySubtotalNonPpn = $copyDetails->filter(fn ($detail) => $normalisasiJenisPpn($detail) === 'non_ppn')->sum('subtotal');
                        $copySubtotalPpnKhusus = $copyDetails->filter(fn ($detail) => $normalisasiJenisPpn($detail) === 'ppn_dpp_nilai_lain')->sum('subtotal');
                        $copyTampilkanPpnKhusus = !($copyJenis === 'khusus' && !$penjualan->butuh_faktur_pajak);
                        $copyPpnMasukTotal = $modePpn === 'exclude' && ($copyJenis !== 'khusus' || $copyTampilkanPpnKhusus);
                        $copyTotalDetail = $copyPpnMasukTotal ? $copySubtotal + $copyPpn : $copySubtotal;
                        $copyTerbilang = $bersihkanTerbilang($terbilang(round($copyTotalDetail))) . ' rupiah';
                        @endphp
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

                                <div class="copy-label copy-label-empty">&nbsp;</div>
                            </div>

                            <div class="invoice-title-row">
                                <div>
                                    <div class="invoice-title">INVOICE / NOTA PENJUALAN</div>
                                    <div class="invoice-number">No: {{ $nomorInvoiceTampil }}</div>
                                    @if (!empty($penjualan->nomor_invoice) && $penjualan->nomor_invoice !== $nomorInvoiceTampil)
                                    <div style="font-size: 10px; margin-top: 1px;">No Sistem: {{ $penjualan->nomor_invoice }}</div>
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
                                        <tr>
                                            <td>Catatan</td>
                                            <td>: {{ $penjualan->catatan ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="invoice-section-title">{{ $kelompok['judul'] }}</div>
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 24px;">No</th>
                                        <th>Barang</th>
                                        <th style="width: 70px;">Qty</th>
                                        <th style="width: 78px;">Harga</th>
                                        <th style="width: 88px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($copyDetails as $detail)
                                    @php
                                    $tipePerhitungan = $detail->tipe_perhitungan_harga ?? 'normal';
                                    $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
                                    $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                                    $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                                    $jenisPpnDetail = $normalisasiJenisPpn($detail);
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
                                        <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada barang pada kelompok detail.</td>
                                    </tr>
                                    @endforelse
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
                                    <div class="total-inline-row"><span>Subtotal</span><strong>Rp {{ number_format($copySubtotal, 0, ',', '.') }}</strong></div>

                                    @if ($modePpn === 'tanpa_ppn')
                                    <div class="total-inline-row"><span>Subtotal Non PPN</span><strong>Rp {{ number_format($copySubtotal, 0, ',', '.') }}</strong></div>
                                    <div class="total-inline-row"><span>PPN</span><strong>Rp 0</strong></div>
                                    @elseif ($copyJenis === 'khusus')
                                    <div class="total-inline-row"><span>Subtotal Barang DPP Nilai Lain</span><strong>Rp {{ number_format($copySubtotalPpnKhusus, 0, ',', '.') }}</strong></div>
                                    <div class="total-inline-row"><span>DPP Nilai Lain</span><strong>Rp {{ number_format($copyDpp, 0, ',', '.') }}</strong></div>
                                    @if ($copyTampilkanPpnKhusus)
                                    <div class="total-inline-row"><span>Nilai PPN</span><strong>Rp {{ number_format($copyPpn, 0, ',', '.') }}</strong></div>
                                    @endif
                                    @else
                                    @if ($copySubtotalPpnNormal > 0)
                                    <div class="total-inline-row"><span>Subtotal Barang PPN Normal</span><strong>Rp {{ number_format($copySubtotalPpnNormal, 0, ',', '.') }}</strong></div>
                                    @endif
                                    @if ($copySubtotalNonPpn > 0)
                                    <div class="total-inline-row"><span>Subtotal Barang Non PPN</span><strong>Rp {{ number_format($copySubtotalNonPpn, 0, ',', '.') }}</strong></div>
                                    @endif

                                    @if ($copyDpp > 0 || $copyPpn > 0)
                                    <div class="total-inline-row"><span>DPP</span><strong>Rp {{ number_format($copyDpp, 0, ',', '.') }}</strong></div>
                                    <div class="total-inline-row"><span>Nilai PPN</span><strong>Rp {{ number_format($copyPpn, 0, ',', '.') }}</strong></div>
                                    @else
                                    <div class="total-inline-row"><span>PPN</span><strong>Rp 0</strong></div>
                                    @endif
                                    @endif

                                    <div class="pajak-note">{{ $labelModePpn }}</div>
                                    <div class="total-inline-row"><span>Total</span><strong>Rp {{ number_format($copyTotalDetail, 0, ',', '.') }}</strong></div>

                                    @if ($adaPenyesuaianTotal)
                                    <div class="total-inline-row"><span>Total Sebelum Penyesuaian</span><strong>Rp {{ number_format($totalSebelumPenyesuaian, 0, ',', '.') }}</strong></div>
                                    <div class="total-inline-row"><span>Penyesuaian Total</span><strong>{{ $nilaiPenyesuaianTotal < 0 ? '-' : '+' }}Rp {{ number_format(abs($nilaiPenyesuaianTotal), 0, ',', '.') }}</strong></div>
                                    @if ($penjualan->keterangan_penyesuaian_total)
                                    <div class="pajak-note">{{ $penjualan->keterangan_penyesuaian_total }}</div>
                                    @endif
                                    @endif

                                    <div class="total-inline-row total-inline-total"><span>Total Akhir Transaksi</span><strong>Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</strong></div>
                                </div>
                            </div>

                            <div class="bottom-info-area">
                                <div class="terbilang-box">
                                    <span class="terbilang-label">Terbilang :</span>
                                    <span class="terbilang-text">{{ $copyTerbilang }}</span>
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