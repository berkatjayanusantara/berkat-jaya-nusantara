@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;

$persentaseTertagih = ($totalPiutang ?? 0) > 0
? (($totalDibayar ?? 0) / ($totalPiutang ?? 1)) * 100
: 0;
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
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
            font-size: 7.5px;
            color: #4b5563;
        }

        .summary-value {
            font-size: 9.5px;
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

        .status-lewat {
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

        .small-text {
            font-size: 7px;
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
    <div class="company">{{ $namaPerusahaan }}</div>
    <div class="company-info">{{ $alamatPerusahaan }}</div>
    <div class="company-info">Telp: {{ $teleponPerusahaan }}</div>

    <div class="title">LAPORAN PIUTANG</div>

    <div class="subtitle">
        Periode Jatuh Tempo: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
        |
        Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Data</div>
                <div class="summary-value">{{ $totalData ?? 0 }}</div>
                <div class="small-text">
                    Sistem: {{ $totalSistemBerjalan ?? 0 }} | Historis: {{ $totalHistoris ?? 0 }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Piutang</div>
                <div class="summary-value">
                    Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}
                </div>
            </td>

            <td>
                <div class="summary-label">Total Dibayar</div>
                <div class="summary-value">
                    Rp {{ number_format($totalDibayar ?? 0, 0, ',', '.') }}
                </div>
                <div class="small-text">
                    Tertagih: {{ number_format($persentaseTertagih, 2, ',', '.') }}%
                </div>
            </td>

            <td>
                <div class="summary-label">Sisa Piutang</div>
                <div class="summary-value">
                    Rp {{ number_format($totalSisa ?? 0, 0, ',', '.') }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Belum Lunas</div>
                <div class="summary-value">{{ $totalBelumLunas ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Sebagian Dibayar</div>
                <div class="summary-value">{{ $totalSebagian ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Lunas</div>
                <div class="summary-value">{{ $totalLunas ?? 0 }}</div>
            </td>

            <td>
                <div class="summary-label">Lewat Jatuh Tempo</div>
                <div class="summary-value">{{ $totalLewatJatuhTempo ?? 0 }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 13%;">Invoice</th>
                <th style="width: 15%;">Customer</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 8%;">Tempo</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 7%;">Tipe</th>
                <th style="width: 10%;">Piutang</th>
                <th style="width: 10%;">Dibayar</th>
                <th style="width: 10%;">Sisa</th>
                <th style="width: 8%;">Ket.</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($piutang as $item)
            @php
            $isHistoris = (bool) ($item->penjualan->is_historical ?? false);

            $lewatJatuhTempo = $item->status_piutang !== 'lunas'
            && $item->tanggal_jatuh_tempo
            && $item->tanggal_jatuh_tempo->isPast();

            if ($item->status_piutang === 'lunas') {
            $statusText = 'Lunas';
            $statusClass = 'status-lunas';
            } elseif ($item->status_piutang === 'sebagian_dibayar') {
            $statusText = 'Sebagian';
            $statusClass = 'status-sebagian';
            } elseif ($item->status_piutang === 'jatuh_tempo') {
            $statusText = 'Jatuh Tempo';
            $statusClass = 'status-lewat';
            } else {
            $statusText = 'Belum Lunas';
            $statusClass = 'status-belum';
            }

            if ($item->status_piutang === 'lunas') {
            $keterangan = 'Selesai';
            $keteranganClass = 'status-lunas';
            } elseif ($lewatJatuhTempo || $item->status_piutang === 'jatuh_tempo') {
            $keterangan = 'Lewat';
            $keteranganClass = 'status-lewat';
            } else {
            $keterangan = 'Berjalan';
            $keteranganClass = 'status-belum';
            }

            $tipeLabel = $isHistoris ? 'Historis' : 'Sistem';
            $tipeClass = $isHistoris ? 'badge-historis' : 'badge-sistem';
            @endphp

            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>

                <td>
                    <strong>{{ $item->nomor_invoice }}</strong>

                    @if ($item->penjualan?->nomor_dokumen_asli)
                    <br>
                    <span class="small-text">
                        Asli: {{ $item->penjualan->nomor_dokumen_asli }}
                    </span>
                    @endif
                </td>

                <td>
                    {{ $item->customer->nama_customer ?? '-' }}
                    <br>
                    <span class="small-text">
                        Telp: {{ $item->customer->nomor_telepon ?? '-' }}
                    </span>
                </td>

                <td class="text-center">
                    {{ $item->penjualan?->tanggal_penjualan ? $item->penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
                </td>

                <td class="text-center">
                    {{ $item->tanggal_jatuh_tempo ? $item->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                </td>

                <td class="text-center">
                    <span class="{{ $statusClass }}">{{ $statusText }}</span>
                </td>

                <td class="text-center">
                    <span class="{{ $tipeClass }}">{{ $tipeLabel }}</span>
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->total_piutang ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->total_dibayar ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($item->sisa_piutang ?? 0, 0, ',', '.') }}
                </td>

                <td class="text-center">
                    <span class="{{ $keteranganClass }}">{{ $keterangan }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">
                    Data laporan piutang belum tersedia.
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