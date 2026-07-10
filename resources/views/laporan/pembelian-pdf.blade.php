@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277 | WA: 085691801290';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.2px;
            color: #111827;
        }

        .company {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .company-info {
            text-align: center;
            font-size: 8px;
            color: #4b5563;
            margin-bottom: 2px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 7px;
            color: #4b5563;
        }

        .summary-value {
            font-size: 9px;
            font-weight: bold;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            border: 1px solid #9ca3af;
            background-color: #e5e7eb;
            padding: 4px 2px;
            font-weight: bold;
            text-align: center;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 3px 2px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-lengkap {
            color: #047857;
            font-weight: bold;
        }

        .status-sebagian {
            color: #92400e;
            font-weight: bold;
        }

        .status-belum {
            color: #b91c1c;
            font-weight: bold;
        }

        .badge-historis {
            color: #6d28d9;
            font-weight: bold;
        }

        .badge-sistem {
            color: #374151;
            font-weight: bold;
        }

        .badge-stok-ya {
            color: #1d4ed8;
            font-weight: bold;
        }

        .badge-stok-tidak {
            color: #c2410c;
            font-weight: bold;
        }

        .small-text {
            font-size: 6.8px;
            color: #4b5563;
        }

        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="company">
        {{ $namaPerusahaan }}
    </div>

    <div class="company-info">
        {{ $alamatPerusahaan }}
    </div>

    <div class="company-info">
        Telp: {{ $teleponPerusahaan }}
    </div>

    <div class="title">
        LAPORAN PEMBELIAN
    </div>

    <div class="subtitle">
        Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
        |
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ $totalTransaksi ?? 0 }}</div>
                <div class="small-text">
                    Sistem: {{ $totalSistemBerjalan ?? 0 }} | Historis: {{ $totalHistoris ?? 0 }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Subtotal</div>
                <div class="summary-value">
                    Rp {{ number_format($totalSubtotal ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Pajak Supplier</div>
                <div class="summary-value">
                    Rp {{ number_format($totalPajak ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Akhir</div>
                <div class="summary-value">
                    Rp {{ number_format($totalAkhir ?? 0, 0, ',', '.') }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Biaya Lain</div>
                <div class="summary-value">
                    Rp {{ number_format($totalBiayaLain ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Potongan / Diskon</div>
                <div class="summary-value">
                    Rp {{ number_format($totalPotonganDiskon ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Dipesan</div>
                <div class="summary-value">
                    {{ number_format($totalDipesan ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Diterima</div>
                <div class="summary-value">
                    {{ number_format($totalDiterima ?? 0, 0, ',', '.') }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Sisa Belum Dikirim</div>
                <div class="summary-value">
                    {{ number_format($totalSisa ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Pengaruh Stok</div>
                <div class="summary-value">
                    {{ $totalMemengaruhiStok ?? 0 }} Ya / {{ $totalTidakMemengaruhiStok ?? 0 }} Tidak
                </div>
            </td>

            <td>
                <div class="summary-label">Status Lengkap</div>
                <div class="summary-value">{{ $totalLengkap ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Status Sebagian / Belum</div>
                <div class="summary-value">{{ $totalSebagian ?? 0 }} / {{ $totalBelumDikirim ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 13%;">Dokumen</th>
                <th style="width: 14%;">Supplier</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 7%;">Tipe</th>
                <th style="width: 5%;">Stok</th>
                <th style="width: 6%;">Pesan</th>
                <th style="width: 6%;">Terima</th>
                <th style="width: 5%;">Sisa</th>
                <th style="width: 8%;">Subtotal</th>
                <th style="width: 7%;">Pajak</th>
                <th style="width: 8%;">Penyes.</th>
                <th style="width: 9%;">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($pembelian as $item)
            @php
            $jumlahDipesan = 0;
            $jumlahDiterima = 0;

            foreach ($item->detailPembelian as $detail) {
            $jumlahDipesan += $detail->jumlah_dipesan ?? $detail->jumlah;
            $jumlahDiterima += $detail->jumlah;
            }

            $sisa = max($jumlahDipesan - $jumlahDiterima, 0);
            $statusPenerimaan = $item->status_penerimaan ?? 'lengkap';
            $isHistoris = (bool) ($item->is_historical ?? false);
            $affectStock = (bool) ($item->affect_stock ?? true);
            $biayaLain = (float) ($item->biaya_lain ?? 0);
            $potonganDiskon = (float) ($item->potongan_diskon ?? 0);
            $adaPenyesuaian = $biayaLain > 0 || $potonganDiskon > 0;

            $nomorDokumenTampil = $isHistoris && !empty($item->nomor_dokumen_asli)
            ? $item->nomor_dokumen_asli
            : $item->nomor_pembelian;

            if ($statusPenerimaan === 'lengkap') {
            $statusText = 'Lengkap';
            $statusClass = 'status-lengkap';
            } elseif ($statusPenerimaan === 'sebagian') {
            $statusText = 'Sebagian';
            $statusClass = 'status-sebagian';
            } else {
            $statusText = 'Belum';
            $statusClass = 'status-belum';
            }

            $tipeLabel = $isHistoris ? 'Historis' : 'Sistem';
            $tipeClass = $isHistoris ? 'badge-historis' : 'badge-sistem';

            $stokLabel = $affectStock ? 'Ya' : 'Tidak';
            $stokClass = $affectStock ? 'badge-stok-ya' : 'badge-stok-tidak';
            @endphp

            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td class="text-center">
                    {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
                </td>

                <td>
                    <strong>{{ $nomorDokumenTampil }}</strong>

                    @if (!$isHistoris && $item->nomor_dokumen_asli)
                    <br>
                    <span class="small-text">
                        Asli: {{ $item->nomor_dokumen_asli }}
                    </span>
                    @endif

                    @if ($item->nomor_delivery_order)
                    <br>
                    <span class="small-text">
                        DO: {{ $item->nomor_delivery_order }}
                    </span>
                    @endif

                    @if ($item->nomor_surat_jalan)
                    <br>
                    <span class="small-text">
                        SJ: {{ $item->nomor_surat_jalan }}
                    </span>
                    @endif
                </td>

                <td>
                    {{ $item->supplier->nama_supplier ?? '-' }}
                    <br>
                    <span class="small-text">
                        Telp: {{ $item->supplier->nomor_telepon ?? '-' }}
                    </span>
                    <br>
                    <span class="small-text">
                        {{ $item->supplier->alamat ?? '-' }}
                    </span>
                </td>

                <td class="text-center">
                    <span class="{{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </td>

                <td class="text-center">
                    <span class="{{ $tipeClass }}">
                        {{ $tipeLabel }}
                    </span>
                </td>

                <td class="text-center">
                    <span class="{{ $stokClass }}">
                        {{ $stokLabel }}
                    </span>
                </td>

                <td class="text-center">
                    {{ number_format($jumlahDipesan, 0, ',', '.') }}
                </td>

                <td class="text-center">
                    {{ number_format($jumlahDiterima, 0, ',', '.') }}
                </td>

                <td class="text-center">
                    {{ number_format($sisa, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->nilai_pajak ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    @if ($adaPenyesuaian)
                    @if ($biayaLain > 0)
                    + Rp {{ number_format($biayaLain, 0, ',', '.') }}<br>
                    @endif
                    @if ($potonganDiskon > 0)
                    - Rp {{ number_format($potonganDiskon, 0, ',', '.') }}
                    @endif
                    @else
                    -
                    @endif
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->total_akhir ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="14" class="text-center">
                    Data laporan pembelian belum tersedia.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan ini dibuat otomatis oleh sistem Berkat Jaya Nusantara.
    </div>
</body>

</html>