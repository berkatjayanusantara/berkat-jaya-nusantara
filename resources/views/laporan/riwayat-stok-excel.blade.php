@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;

$nettoPerubahan = ($totalMasuk ?? 0) - ($totalKeluar ?? 0) + ($totalSelisihPlus ?? 0) - ($totalSelisihMinus ?? 0);
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Stok</title>

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

        .number-format {
            mso-number-format: "#,##0";
        }

        .total-row {
            font-weight: bold;
            background-color: #eff6ff;
        }

        .success {
            background-color: #dcfce7;
        }

        .warning {
            background-color: #fef3c7;
        }

        .danger {
            background-color: #fee2e2;
        }

        .info {
            background-color: #dbeafe;
        }
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="16" class="company-title">{{ $namaPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="16" class="company-info">{{ $alamatPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="16" class="company-info">Telp: {{ $teleponPerusahaan }}</td>
        </tr>

        <tr>
            <td colspan="16" class="title">LAPORAN RIWAYAT STOK</td>
        </tr>

        <tr>
            <td colspan="16" class="subtitle">Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}</td>
        </tr>

        <tr>
            <td colspan="16" class="subtitle">Dicetak: {{ now()->format('d-m-Y H:i') }}</td>
        </tr>

        <tr>
            <td colspan="16"></td>
        </tr>

        <tr class="section-header">
            <td colspan="16">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Data</td>
            <td class="text-center number-format">{{ $totalData ?? 0 }}</td>

            <td class="bold">Total Barang Masuk</td>
            <td class="text-center number-format">{{ $totalMasuk ?? 0 }}</td>

            <td class="bold">Total Barang Keluar</td>
            <td class="text-center number-format">{{ $totalKeluar ?? 0 }}</td>

            <td class="bold">Jumlah Penyesuaian</td>
            <td class="text-center number-format">{{ $totalPenyesuaian ?? 0 }}</td>

            <td class="bold">Jumlah Stock Opname</td>
            <td class="text-center number-format">{{ $totalOpname ?? 0 }}</td>

            <td class="bold">Selisih Bertambah</td>
            <td class="text-center number-format">{{ $totalSelisihPlus ?? 0 }}</td>

            <td class="bold">Selisih Berkurang</td>
            <td class="text-center number-format">{{ $totalSelisihMinus ?? 0 }}</td>

            <td class="bold">Netto Perubahan</td>
            <td class="text-center number-format">{{ $nettoPerubahan }}</td>
        </tr>

        <tr>
            <td colspan="16"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Tanggal</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Satuan</td>
            <td>Jenis Pergerakan</td>
            <td>Tipe Riwayat</td>
            <td>Jumlah</td>
            <td>Stok Sebelum</td>
            <td>Stok Sesudah</td>
            <td>Selisih</td>
            <td>Arah Selisih</td>
            <td>Sumber Transaksi</td>
            <td>Keterangan</td>
            <td>Dibuat Oleh</td>
            <td>Waktu Input</td>
        </tr>

        @foreach ($riwayatStok as $item)
        @php
        $stokSebelum = (int) ($item->stok_sebelum ?? 0);
        $stokSesudah = (int) ($item->stok_sesudah ?? 0);
        $selisih = $stokSesudah - $stokSebelum;
        $isOpname = str_starts_with((string) $item->sumber_transaksi, 'STOCK-OPNAME');

        if ($item->jenis_pergerakan === 'masuk') {
        $jenisLabel = 'Masuk';
        $jenisClass = 'success';
        } elseif ($item->jenis_pergerakan === 'keluar') {
        $jenisLabel = 'Keluar';
        $jenisClass = 'danger';
        } else {
        $jenisLabel = 'Penyesuaian';
        $jenisClass = 'warning';
        }

        if ($selisih > 0) {
        $arahSelisih = 'Bertambah';
        $selisihClass = 'success';
        } elseif ($selisih < 0) {
            $arahSelisih='Berkurang' ;
            $selisihClass='danger' ;
            } else {
            $arahSelisih='Tetap' ;
            $selisihClass='' ;
            }
            @endphp

            <tr>
            <td class="text-center">{{ $loop->iteration }}</td>

            <td class="text-center">
                {{ $item->tanggal ? $item->tanggal->format('d-m-Y') : '-' }}
            </td>

            <td class="text-format">{{ $item->barang->kode_barang ?? '-' }}</td>

            <td>{{ $item->barang->nama_barang ?? '-' }}</td>

            <td class="text-center">{{ strtoupper($item->barang->satuan ?? '-') }}</td>

            <td class="text-center {{ $jenisClass }}">{{ $jenisLabel }}</td>

            <td class="text-center {{ $isOpname ? 'info' : '' }}">
                {{ $isOpname ? 'Stock Opname' : 'Transaksi' }}
            </td>

            <td class="text-center number-format">{{ $item->jumlah ?? 0 }}</td>

            <td class="text-center number-format">{{ $stokSebelum }}</td>

            <td class="text-center number-format">{{ $stokSesudah }}</td>

            <td class="text-center number-format {{ $selisihClass }}">{{ $selisih }}</td>

            <td class="text-center {{ $selisihClass }}">{{ $arahSelisih }}</td>

            <td class="text-format">{{ $item->sumber_transaksi ?? '-' }}</td>

            <td>{{ $item->keterangan ?? '-' }}</td>

            <td>{{ $item->user->nama_user ?? '-' }}</td>

            <td class="text-center">
                {{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : '-' }}
            </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="16"></td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">TOTAL DATA</td>
                <td colspan="8" class="text-center number-format">{{ $totalData ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">TOTAL BARANG MASUK</td>
                <td colspan="8" class="text-center number-format">{{ $totalMasuk ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">TOTAL BARANG KELUAR</td>
                <td colspan="8" class="text-center number-format">{{ $totalKeluar ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">JUMLAH PENYESUAIAN</td>
                <td colspan="8" class="text-center number-format">{{ $totalPenyesuaian ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">JUMLAH STOCK OPNAME</td>
                <td colspan="8" class="text-center number-format">{{ $totalOpname ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">TOTAL SELISIH BERTAMBAH</td>
                <td colspan="8" class="text-center number-format">{{ $totalSelisihPlus ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">TOTAL SELISIH BERKURANG</td>
                <td colspan="8" class="text-center number-format">{{ $totalSelisihMinus ?? 0 }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="8" class="bold">NETTO PERUBAHAN</td>
                <td colspan="8" class="text-center number-format">{{ $nettoPerubahan }}</td>
            </tr>
    </table>
</body>

</html>