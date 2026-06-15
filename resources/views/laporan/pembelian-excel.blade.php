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
    <title>Laporan Pembelian</title>

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
            mso-number-format: "#,##0.###";
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
            <td colspan="22" class="company-title">
                {{ $namaPerusahaan }}
            </td>
        </tr>

        <tr>
            <td colspan="22" class="company-info">
                {{ $alamatPerusahaan }}
            </td>
        </tr>

        <tr>
            <td colspan="22" class="company-info">
                Telp: {{ $teleponPerusahaan }}
            </td>
        </tr>

        <tr>
            <td colspan="22" class="title">
                LAPORAN PEMBELIAN
            </td>
        </tr>

        <tr>
            <td colspan="22" class="subtitle">
                Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
            </td>
        </tr>

        <tr>
            <td colspan="22" class="subtitle">
                Dicetak: {{ now()->format('d-m-Y H:i') }}
            </td>
        </tr>

        <tr>
            <td colspan="22"></td>
        </tr>

        <tr class="section-header">
            <td colspan="22">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Transaksi</td>
            <td class="text-center">{{ $totalTransaksi ?? 0 }}</td>

            <td class="bold">Sistem Berjalan</td>
            <td class="text-center">{{ $totalSistemBerjalan ?? 0 }}</td>

            <td class="bold">Historis</td>
            <td class="text-center">{{ $totalHistoris ?? 0 }}</td>

            <td class="bold">Mempengaruhi Stok</td>
            <td class="text-center">{{ $totalMemengaruhiStok ?? 0 }}</td>

            <td class="bold">Tidak Mempengaruhi Stok</td>
            <td class="text-center">{{ $totalTidakMemengaruhiStok ?? 0 }}</td>

            <td class="bold">Lengkap</td>
            <td class="text-center">{{ $totalLengkap ?? 0 }}</td>

            <td class="bold">Sebagian</td>
            <td class="text-center">{{ $totalSebagian ?? 0 }}</td>

            <td class="bold">Belum Dikirim</td>
            <td colspan="7" class="text-center">{{ $totalBelumDikirim ?? 0 }}</td>
        </tr>

        <tr>
            <td class="bold">Total Dipesan</td>
            <td colspan="3" class="text-center number-format">{{ $totalDipesan ?? 0 }}</td>

            <td class="bold">Total Diterima</td>
            <td colspan="3" class="text-center number-format">{{ $totalDiterima ?? 0 }}</td>

            <td class="bold">Total Sisa</td>
            <td colspan="3" class="text-center number-format">{{ $totalSisa ?? 0 }}</td>

            <td class="bold">Total Subtotal</td>
            <td colspan="3" class="text-right currency">{{ $totalSubtotal ?? 0 }}</td>

            <td class="bold">Total Pajak</td>
            <td class="text-right currency">{{ $totalPajak ?? 0 }}</td>

            <td class="bold">Total Akhir</td>
            <td colspan="3" class="text-right currency">{{ $totalAkhir ?? 0 }}</td>
        </tr>

        <tr>
            <td colspan="22"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Tanggal</td>
            <td>No Pembelian Sistem</td>
            <td>No Dokumen Asli</td>
            <td>No DO Supplier</td>
            <td>No Surat Jalan</td>
            <td>Supplier</td>
            <td>Telepon Supplier</td>
            <td>NPWP Supplier</td>
            <td>Alamat Supplier</td>
            <td>Status Penerimaan</td>
            <td>Tipe Invoice</td>
            <td>Pengaruh Stok</td>
            <td>Mode Pajak</td>
            <td>Jumlah Dipesan</td>
            <td>Jumlah Diterima</td>
            <td>Sisa Belum Dikirim</td>
            <td>Subtotal</td>
            <td>Pajak</td>
            <td>Total Akhir</td>
            <td>Admin</td>
            <td>Catatan</td>
        </tr>

        @foreach ($pembelian as $item)
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
        $pajakDitambahkan = $item->pajak_ditambahkan ?? true;

        $statusText = match ($statusPenerimaan) {
        'lengkap' => 'Lengkap',
        'sebagian' => 'Sebagian',
        default => 'Belum Dikirim',
        };

        $tipeInvoice = $isHistoris ? 'Historis / Lama' : 'Sistem Berjalan';
        $pengaruhStok = $affectStock ? 'Mempengaruhi stok' : 'Tidak mempengaruhi stok';

        $modePajak = $pajakDitambahkan
        ? 'Pajak ditambahkan ke total akhir'
        : 'Pajak hanya ditampilkan';

        $nomorTelepon = $item->supplier->nomor_telepon ?? '-';
        $npwp = $item->supplier->npwp ?? '-';
        $alamatSupplier = $item->supplier->alamat ?? '-';
        @endphp

        <tr class="{{ $isHistoris ? 'historis' : 'sistem' }}">
            <td class="text-center">
                {{ $loop->iteration }}
            </td>

            <td class="text-center">
                {{ $item->tanggal_pembelian ? $item->tanggal_pembelian->format('d-m-Y') : '-' }}
            </td>

            <td class="text-format">
                {{ $item->nomor_pembelian }}
            </td>

            <td class="text-format">
                {{ $item->nomor_dokumen_asli ?? '-' }}
            </td>

            <td class="text-format">
                {{ $item->nomor_delivery_order ?? '-' }}
            </td>

            <td class="text-format">
                {{ $item->nomor_surat_jalan ?? '-' }}
            </td>

            <td>
                {{ $item->supplier->nama_supplier ?? '-' }}
            </td>

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

            <td>
                {{ $alamatSupplier }}
            </td>

            <td class="text-center {{ $statusPenerimaan === 'lengkap' ? 'success' : ($statusPenerimaan === 'sebagian' ? 'warning' : 'danger') }}">
                {{ $statusText }}
            </td>

            <td class="text-center">
                {{ $tipeInvoice }}
            </td>

            <td class="text-center">
                {{ $pengaruhStok }}
            </td>

            <td>
                {{ $modePajak }}
            </td>

            <td class="text-center number-format">
                {{ $jumlahDipesan }}
            </td>

            <td class="text-center number-format">
                {{ $jumlahDiterima }}
            </td>

            <td class="text-center number-format">
                {{ $sisa }}
            </td>

            <td class="text-right currency">
                {{ $item->subtotal }}
            </td>

            <td class="text-right currency">
                {{ $item->nilai_pajak }}
            </td>

            <td class="text-right currency">
                {{ $item->total_akhir }}
            </td>

            <td>
                {{ $item->user->nama_user ?? '-' }}
            </td>

            <td>
                {{ $item->catatan ?? '-' }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="22"></td>
        </tr>

        <tr class="total-row">
            <td colspan="17" class="bold">TOTAL SUBTOTAL</td>
            <td colspan="5" class="text-right currency">{{ $totalSubtotal ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="17" class="bold">TOTAL PAJAK</td>
            <td colspan="5" class="text-right currency">{{ $totalPajak ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="17" class="bold">TOTAL AKHIR</td>
            <td colspan="5" class="text-right currency">{{ $totalAkhir ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="17" class="bold">TOTAL BARANG DITERIMA</td>
            <td colspan="5" class="text-center number-format">{{ $totalDiterima ?? 0 }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="17" class="bold">TOTAL SISA BELUM DIKIRIM</td>
            <td colspan="5" class="text-center number-format">{{ $totalSisa ?? 0 }}</td>
        </tr>
    </table>
</body>

</html>