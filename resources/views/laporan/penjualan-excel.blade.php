@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;

$formatIsi = function ($angka) {
return rtrim(rtrim(number_format((float) $angka, 3, ',', '.'), '0'), ',');
};

$nomor = 1;
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>

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

        .header {
            font-weight: bold;
            background-color: #eeeeee;
            text-align: center;
        }

        .section-header {
            font-weight: bold;
            background-color: #dbeafe;
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

        .isi-kemasan {
            background-color: #f5f3ff;
        }

        .normal {
            background-color: #f9fafb;
        }
    </style>
</head>

<body>
    <table border="1">
        <tr>
            <td colspan="27" class="company-title">
                {{ $namaPerusahaan }}
            </td>
        </tr>

        <tr>
            <td colspan="27" class="text-center">
                {{ $alamatPerusahaan }} | Telp: {{ $teleponPerusahaan }}
            </td>
        </tr>

        <tr>
            <td colspan="27" class="title">
                LAPORAN PENJUALAN
            </td>
        </tr>

        <tr>
            <td colspan="27" class="subtitle">
                Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }}
            </td>
        </tr>

        <tr>
            <td colspan="27" class="subtitle">
                Dicetak: {{ now()->format('d-m-Y H:i') }}
            </td>
        </tr>

        <tr>
            <td colspan="27"></td>
        </tr>

        <tr class="section-header">
            <td colspan="27">Ringkasan Laporan</td>
        </tr>

        <tr>
            <td class="bold">Total Transaksi</td>
            <td class="text-center">{{ $totalTransaksi }}</td>

            <td class="bold">Sistem Berjalan</td>
            <td class="text-center">{{ $totalSistemBerjalan }}</td>

            <td class="bold">Historis</td>
            <td class="text-center">{{ $totalHistoris }}</td>

            <td class="bold">Total Tunai</td>
            <td class="text-right currency">{{ $totalTunai }}</td>

            <td class="bold">Total Kredit</td>
            <td class="text-right currency">{{ $totalKredit }}</td>

            <td class="bold">Total Piutang</td>
            <td class="text-right currency">{{ $totalPiutang }}</td>

            <td class="bold">Total Dibayar</td>
            <td class="text-right currency">{{ $totalDibayar }}</td>

            <td class="bold">Sisa Piutang</td>
            <td colspan="12" class="text-right currency">{{ $totalSisaPiutang }}</td>
        </tr>

        <tr>
            <td class="bold">Total Subtotal</td>
            <td colspan="3" class="text-right currency">{{ $totalSubtotal }}</td>

            <td class="bold">Total Pajak</td>
            <td colspan="3" class="text-right currency">{{ $totalPajak }}</td>

            <td class="bold">Total Akhir</td>
            <td colspan="3" class="text-right currency">{{ $totalAkhir }}</td>

            <td class="bold">Total Baris Barang</td>
            <td colspan="3" class="text-center number-format">{{ $totalItemBarang }}</td>

            <td class="bold">Total Jumlah Terjual</td>
            <td colspan="10" class="text-center number-format">{{ $totalJumlahTerjual }}</td>
        </tr>

        <tr>
            <td class="bold">Barang Normal</td>
            <td colspan="3" class="text-center">{{ $totalBarangNormal }}</td>

            <td class="bold">Nilai Barang Normal</td>
            <td colspan="4" class="text-right currency">{{ $totalNilaiBarangNormal }}</td>

            <td class="bold">Barang Isi Kemasan</td>
            <td colspan="3" class="text-center">{{ $totalBarangIsiKemasan }}</td>

            <td class="bold">Nilai Barang Isi Kemasan</td>
            <td colspan="13" class="text-right currency">{{ $totalNilaiBarangIsiKemasan }}</td>
        </tr>

        <tr>
            <td colspan="27"></td>
        </tr>

        <tr class="header">
            <td>No</td>
            <td>Tanggal</td>
            <td>No Invoice Sistem</td>
            <td>No Dokumen Asli</td>
            <td>Customer</td>
            <td>Nomor Telepon</td>
            <td>NPWP</td>
            <td>Metode</td>
            <td>Status Pembayaran</td>
            <td>Tipe Invoice</td>
            <td>Mode Pajak</td>
            <td>Kode Barang</td>
            <td>Nama Barang</td>
            <td>Tipe Harga</td>
            <td>Jumlah</td>
            <td>Satuan Transaksi</td>
            <td>Isi Per Satuan</td>
            <td>Satuan Hitung Harga</td>
            <td>Harga Jual</td>
            <td>Rumus Perhitungan</td>
            <td>Subtotal Barang</td>
            <td>Subtotal Invoice</td>
            <td>Pajak Invoice</td>
            <td>Total Akhir Invoice</td>
            <td>Total Piutang</td>
            <td>Total Dibayar</td>
            <td>Sisa Piutang</td>
        </tr>

        @foreach ($penjualan as $item)
        @php
        $nomorTelepon = $item->customer->nomor_telepon ?? '-';
        $npwp = $item->customer->npwp ?? '-';
        $isHistoris = (bool) ($item->is_historical ?? false);
        $pajakDitambahkan = $item->pajak_ditambahkan ?? true;

        $statusPembayaran = match ($item->status_pembayaran) {
        'lunas' => 'Lunas',
        'sebagian' => 'Sebagian',
        default => 'Belum Lunas',
        };

        $tipeInvoice = $isHistoris ? 'Historis / Lama' : 'Sistem Berjalan';

        $modePajak = $pajakDitambahkan
        ? 'Pajak ditambahkan ke total akhir'
        : 'Pajak hanya ditampilkan';

        $detailList = $item->detailPenjualan;
        @endphp

        @forelse ($detailList as $detail)
        @php
        $tipeHarga = $detail->tipe_perhitungan_harga ?? 'normal';
        $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '-');
        $satuanHitungHarga = $detail->satuan_hitung_harga ?? $satuanTransaksi;
        $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);

        $tipeHargaText = $tipeHarga === 'isi_kemasan'
        ? 'Isi Kemasan'
        : 'Normal';

        $rumusPerhitungan = $tipeHarga === 'isi_kemasan'
        ? $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatIsi($isiPerSatuan) . ' ' . $satuanHitungHarga . ' x Rp ' . number_format($detail->harga_jual, 0, ',', '.')
        : $detail->jumlah . ' ' . $satuanTransaksi . ' x Rp ' . number_format($detail->harga_jual, 0, ',', '.');
        @endphp

        <tr class="{{ $isHistoris ? 'historis' : ($tipeHarga === 'isi_kemasan' ? 'isi-kemasan' : 'sistem') }}">
            <td class="text-center">{{ $nomor++ }}</td>

            <td class="text-center">
                {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
            </td>

            <td class="text-format">{{ $item->nomor_invoice }}</td>

            <td class="text-format">{{ $item->nomor_dokumen_asli ?? '-' }}</td>

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

            <td class="text-center">{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>

            <td class="text-center">{{ $statusPembayaran }}</td>

            <td class="text-center">{{ $tipeInvoice }}</td>

            <td>{{ $modePajak }}</td>

            <td class="text-format">{{ $detail->barang->kode_barang ?? '-' }}</td>

            <td>{{ $detail->barang->nama_barang ?? '-' }}</td>

            <td class="text-center">{{ $tipeHargaText }}</td>

            <td class="text-center number-format">{{ $detail->jumlah }}</td>

            <td class="text-center">{{ $satuanTransaksi }}</td>

            <td class="text-center number-format">
                {{ $tipeHarga === 'isi_kemasan' ? $isiPerSatuan : 1 }}
            </td>

            <td class="text-center">{{ $satuanHitungHarga }}</td>

            <td class="text-right currency">{{ $detail->harga_jual }}</td>

            <td>{{ $rumusPerhitungan }}</td>

            <td class="text-right currency">{{ $detail->subtotal }}</td>

            <td class="text-right currency">{{ $item->subtotal }}</td>

            <td class="text-right currency">{{ $item->nilai_pajak }}</td>

            <td class="text-right currency">{{ $item->total_akhir }}</td>

            <td class="text-right currency">{{ $item->piutang->total_piutang ?? 0 }}</td>

            <td class="text-right currency">{{ $item->piutang->total_dibayar ?? 0 }}</td>

            <td class="text-right currency">{{ $item->piutang->sisa_piutang ?? 0 }}</td>
        </tr>
        @empty
        <tr class="{{ $isHistoris ? 'historis' : 'sistem' }}">
            <td class="text-center">{{ $nomor++ }}</td>
            <td class="text-center">
                {{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}
            </td>
            <td class="text-format">{{ $item->nomor_invoice }}</td>
            <td class="text-format">{{ $item->nomor_dokumen_asli ?? '-' }}</td>
            <td>{{ $item->customer->nama_customer ?? '-' }}</td>
            <td class="text-format">{{ $nomorTelepon }}</td>
            <td class="text-format">{{ $npwp }}</td>
            <td class="text-center">{{ ucfirst($item->metode_pembayaran ?? '-') }}</td>
            <td class="text-center">{{ $statusPembayaran }}</td>
            <td class="text-center">{{ $tipeInvoice }}</td>
            <td>{{ $modePajak }}</td>
            <td colspan="10" class="text-center">Detail barang tidak tersedia</td>
            <td class="text-right currency">{{ $item->subtotal }}</td>
            <td class="text-right currency">{{ $item->nilai_pajak }}</td>
            <td class="text-right currency">{{ $item->total_akhir }}</td>
            <td class="text-right currency">{{ $item->piutang->total_piutang ?? 0 }}</td>
            <td class="text-right currency">{{ $item->piutang->total_dibayar ?? 0 }}</td>
            <td class="text-right currency">{{ $item->piutang->sisa_piutang ?? 0 }}</td>
        </tr>
        @endforelse
        @endforeach

        <tr>
            <td colspan="27"></td>
        </tr>

        <tr class="total-row">
            <td colspan="20" class="bold">TOTAL SUBTOTAL INVOICE</td>
            <td colspan="7" class="text-right currency">{{ $totalSubtotal }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="20" class="bold">TOTAL PAJAK INVOICE</td>
            <td colspan="7" class="text-right currency">{{ $totalPajak }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="20" class="bold">TOTAL AKHIR INVOICE</td>
            <td colspan="7" class="text-right currency">{{ $totalAkhir }}</td>
        </tr>

        <tr class="total-row">
            <td colspan="20" class="bold">TOTAL SISA PIUTANG</td>
            <td colspan="7" class="text-right currency">{{ $totalSisaPiutang }}</td>
        </tr>
    </table>
</body>

</html>