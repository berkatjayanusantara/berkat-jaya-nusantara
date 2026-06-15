@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
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
            margin-bottom: 4px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
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
            font-size: 7.5px;
            color: #4b5563;
        }

        .summary-value {
            font-size: 10px;
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
            padding: 4px 3px;
            font-weight: bold;
            text-align: center;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 3px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .status-lunas {
            color: #047857;
            font-weight: bold;
        }

        .status-sebagian {
            color: #1d4ed8;
            font-weight: bold;
        }

        .status-belum {
            color: #92400e;
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

        .small-text {
            font-size: 7.5px;
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
        {{ $alamatPerusahaan }} | Telp: {{ $teleponPerusahaan }}
    </div>

    <div class="title">
        LAPORAN PENJUALAN
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
                <div class="summary-value">{{ $totalTransaksi }}</div>
                <div class="small-text">
                    Sistem: {{ $totalSistemBerjalan }} | Historis: {{ $totalHistoris }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Subtotal</div>
                <div class="summary-value">
                    Rp {{ number_format($totalSubtotal, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Pajak</div>
                <div class="summary-value">
                    Rp {{ number_format($totalPajak, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Akhir</div>
                <div class="summary-value">
                    Rp {{ number_format($totalAkhir, 0, ',', '.') }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Total Tunai</div>
                <div class="summary-value">
                    Rp {{ number_format($totalTunai, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Kredit</div>
                <div class="summary-value">
                    Rp {{ number_format($totalKredit, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Dibayar Piutang</div>
                <div class="summary-value">
                    Rp {{ number_format($totalDibayar, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Sisa Piutang</div>
                <div class="summary-value">
                    Rp {{ number_format($totalSisaPiutang, 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 12%;">Invoice</th>
                <th style="width: 14%;">Customer</th>
                <th style="width: 7%;">Metode</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 7%;">Tipe</th>
                <th style="width: 9%;">Subtotal</th>
                <th style="width: 8%;">Pajak</th>
                <th style="width: 9%;">Total</th>
                <th style="width: 8%;">Piutang</th>
                <th style="width: 8%;">Sisa</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($penjualan as $item)
            @php
            $isHistoris = (bool) ($item->is_historical ?? false);

            if ($item->status_pembayaran === 'lunas') {
            $statusPembayaran = 'Lunas';
            $statusClass = 'status-lunas';
            } elseif ($item->status_pembayaran === 'sebagian') {
            $statusPembayaran = 'Sebagian';
            $statusClass = 'status-sebagian';
            } else {
            $statusPembayaran = 'Belum Lunas';
            $statusClass = 'status-belum';
            }

            $tipeLabel = $isHistoris ? 'Historis' : 'Sistem';
            $tipeClass = $isHistoris ? 'badge-historis' : 'badge-sistem';
            @endphp

            <tr>
                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td class="text-center">
                    {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
                </td>

                <td>
                    <strong>{{ $item->nomor_invoice }}</strong>

                    @if ($item->nomor_dokumen_asli)
                    <br>
                    <span class="small-text">
                        Asli: {{ $item->nomor_dokumen_asli }}
                    </span>
                    @endif
                </td>

                <td>
                    {{ $item->customer->nama_customer ?? '-' }}
                    <br>
                    <span class="small-text">
                        {{ $item->customer->nomor_telepon ?? '-' }}
                    </span>
                </td>

                <td class="text-center">
                    {{ ucfirst($item->metode_pembayaran) }}
                </td>

                <td class="text-center">
                    <span class="{{ $statusClass }}">
                        {{ $statusPembayaran }}
                    </span>
                </td>

                <td class="text-center">
                    <span class="{{ $tipeClass }}">
                        {{ $tipeLabel }}
                    </span>
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->nilai_pajak, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->total_akhir, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->piutang->total_piutang ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->piutang->sisa_piutang ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">
                    Data laporan penjualan belum tersedia.
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