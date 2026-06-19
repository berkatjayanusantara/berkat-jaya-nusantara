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
        table {
            border-collapse: collapse;
        }

        .company-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            background-color: #eff6ff;
        }

        .company-info {
            text-align: center;
            font-weight: bold;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            background-color: #dbeafe;
        }

        .subtitle {
            text-align: center;
            font-weight: bold;
        }

        .section-header {
            font-weight: bold;
            background-color: #dbeafe;
        }

        .header {
            font-weight: bold;
            background-color: #eeeeee;
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-format {
            mso-number-format: "\@";
        }

        .currency {
            mso-number-format: "#,##0";
        }

        .number-format {
            mso-number-format: "#,##0.##";
        }

        .total-row {
            font-weight: bold;
            background-color: #eff6ff;
        }

        .historis {
            background-color: #f3e8ff;
        }

        .sistem {
            background-color: #f9fafb;
        }

        .warning {
            background-color: #fef3c7;
        }

        .success {
            background-color: #dcfce7;
        }

        .danger {
            background-color: #fee2e2;
        }
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="19" class="company-title">{{ $namaPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="19" class="company-info">{{ $alamatPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="19" class="company-info">Telp: {{ $teleponPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="19" class="title">LAPORAN PIUTANG</td>
        </tr>

        <tr>
            <td colspan="19" class="subtitle">
                Periode Jatuh Tempo: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
            </td>
        </tr>

        <tr>
            <td colspan="19" class="subtitle">Dicetak: {{ now()->format('d-m-Y H:i') }}</td>
        </tr>

        <tr>
            <td colspan="19"></td>
        </tr>

        <tr class="section-header">
            <td colspan="19">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Data</td>
            <td class="text-center">{{ $totalData ?? 0 }}</td>

            <td class="bold">Sistem Berjalan</td>
            <td class="text-center">{{ $totalSistemBerjalan ?? 0 }}</td>

            <td class="bold">Historis</td>
            <td class="text-center">{{ $totalHistoris ?? 0 }}</td>

            <td class="bold">Belum Lunas</td>
            <td class="text-center">{{ $totalBelumLunas ?? 0 }}</td>

            <td class="bold">Sebagian Dibayar</td>
            <td class="text-center">{{ $totalSebagian ?? 0 }}</td>

            <td class="bold">Lunas</td>
            <td class="text-center">{{ $totalLunas ?? 0 }}</td>

            <td class="bold">Lewat Jatuh Tempo</td>
            <td class="text-center">{{ $totalLewatJatuhTempo ?? 0 }}</td>

            <td class="bold">Persentase Tertagih</td>
            <td colspan="4" class="text-center number-format">{{ number_format($persentaseTertagih, 2, ',', '.') }}%</td>
        </tr>

        <tr>
            <td class="bold">Total Piutang</td>
            <td colspan="5" class="text-right currency">{{ $totalPiutang ?? 0 }}</td>

            <td class="bold">Total Dibayar</td>
            <td colspan="5" class="text-right currency">{{ $totalDibayar ?? 0 }}</td>

            <td class="bold">Total Sisa</td>
            <td colspan="6" class="text-right currency">{{ $totalSisa ?? 0 }}</td>
        </tr>

        <tr>
            <td colspan="19"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>No Invoice Tampil</td>
            <td>No Invoice Sistem</td>
            <td>No Dokumen Asli</td>
            <td>Tanggal Invoice</td>
            <td>Customer</td>
            <td>Nomor Telepon</td>
            <td>NPWP</td>
            <td>Metode Pembayaran</td>
            <td>Status Pembayaran</td>
            <td>Tipe Invoice</td>
            <td>Jatuh Tempo</td>
            <td>Status Piutang</td>
            <td>Total Piutang</td>
            <td>Total Dibayar</td>
            <td>Sisa Piutang</td>
            <td>Keterangan</td>
            <td>Catatan Piutang</td>
            <td>Alamat Customer</td>
        </tr>

        @foreach ($piutang as $item)
        @php
        $isHistoris = (bool) ($item->penjualan->is_historical ?? false);
        $nomorInvoiceTampil = $isHistoris && !empty($item->penjualan?->nomor_dokumen_asli)
        ? $item->penjualan->nomor_dokumen_asli
        : $item->nomor_invoice;

        $lewatJatuhTempo = $item->status_piutang !== 'lunas'
        && $item->tanggal_jatuh_tempo
        && $item->tanggal_jatuh_tempo->isPast();

        if ($item->status_piutang === 'lunas') {
        $statusText = 'Lunas';
        $statusClass = 'success';
        } elseif ($item->status_piutang === 'sebagian_dibayar') {
        $statusText = 'Sebagian Dibayar';
        $statusClass = 'warning';
        } elseif ($item->status_piutang === 'jatuh_tempo') {
        $statusText = 'Jatuh Tempo';
        $statusClass = 'danger';
        } else {
        $statusText = 'Belum Lunas';
        $statusClass = 'warning';
        }

        if ($item->status_piutang === 'lunas') {
        $keterangan = 'Selesai';
        $keteranganClass = 'success';
        } elseif ($lewatJatuhTempo || $item->status_piutang === 'jatuh_tempo') {
        $keterangan = 'Lewat Jatuh Tempo';
        $keteranganClass = 'danger';
        } else {
        $keterangan = 'Berjalan';
        $keteranganClass = 'warning';
        }

        $tipeInvoice = $isHistoris ? 'Historis / Lama' : 'Sistem Berjalan';
        $nomorTelepon = $item->customer->nomor_telepon ?? '-';
        $npwp = $item->customer->npwp ?? '-';
        $alamatCustomer = $item->customer->alamat ?? '-';
        @endphp

        <tr class="{{ $isHistoris ? 'historis' : 'sistem' }}">
            <td class="text-center">{{ $loop->iteration }}</td>

            <td class="text-format">{{ $nomorInvoiceTampil }}</td>

            <td class="text-format">{{ $item->nomor_invoice }}</td>

            <td class="text-format">{{ $item->penjualan->nomor_dokumen_asli ?? '-' }}</td>

            <td class="text-center">
                {{ $item->penjualan?->tanggal_penjualan ? $item->penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
            </td>

            <td>{{ $item->customer->nama_customer ?? '-' }}</td>

            <td class="text-format">
                @if ($nomorTelepon !== '-')
                &#8203;{{ $nomorTelepon }}
                @else
                -
                @endif
            </td>

            <td class="text-format">
                @if ($npwp !== '-')
                &#8203;{{ $npwp }}
                @else
                -
                @endif
            </td>

            <td class="text-center">{{ ucfirst($item->penjualan->metode_pembayaran ?? '-') }}</td>

            <td class="text-center">
                {{ ucwords(str_replace('_', ' ', $item->penjualan->status_pembayaran ?? '-')) }}
            </td>

            <td class="text-center">{{ $tipeInvoice }}</td>

            <td class="text-center">
                {{ $item->tanggal_jatuh_tempo ? $item->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
            </td>

            <td class="text-center {{ $statusClass }}">{{ $statusText }}</td>

            <td class="text-right currency">{{ $item->total_piutang ?? 0 }}</td>

            <td class="text-right currency">{{ $item->total_dibayar ?? 0 }}</td>

            <td class="text-right currency">{{ $item->sisa_piutang ?? 0 }}</td>

            <td class="text-center {{ $keteranganClass }}">{{ $keterangan }}</td>

            <td>{{ $item->catatan ?? '-' }}</td>

            <td>{{ $alamatCustomer }}</td>
        </tr>
        @endforeach

        <tr>
            <td colspan="19"></td>
        </tr>

        <tr class="total-row">
            <td colspan="13" class="bold">TOTAL PIUTANG</td>
            <td colspan="6" class="text-right currency">{{ $totalPiutang ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="13" class="bold">TOTAL DIBAYAR</td>
            <td colspan="6" class="text-right currency">{{ $totalDibayar ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="13" class="bold">TOTAL SISA PIUTANG</td>
            <td colspan="6" class="text-right currency">{{ $totalSisa ?? 0 }}</td>
        </tr>
    </table>
</body>

</html>