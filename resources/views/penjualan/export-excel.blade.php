@php
$pajakDitambahkan = $penjualan->pajak_ditambahkan ?? true;

$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$nomorTeleponCustomer = $penjualan->customer->nomor_telepon ?? '-';
$npwpCustomer = $penjualan->customer->npwp ?? '-';

$statusPembayaran = str_replace('_', ' ', ucfirst($penjualan->status_pembayaran ?? '-'));
$metodePembayaran = ucfirst($penjualan->metode_pembayaran ?? '-');

$modePajak = $pajakDitambahkan
? 'Pajak ditambahkan ke total akhir'
: 'Pajak hanya ditampilkan dan tidak ditambahkan ke total akhir';

$formatAngka = function ($angka) {
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

    $terbilangTotal=$bersihkanTerbilang($terbilang(round($penjualan->total_akhir))) . ' rupiah';
    @endphp

    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Invoice Penjualan {{ $penjualan->nomor_invoice }}</title>

        <style>
            table {
                border-collapse: collapse;
            }

            body {
                color: #000000;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                background-color: #ffffff;
            }

            td,
            th {
                color: #000000;
                vertical-align: top;
            }

            .invoice-table {
                width: 100%;
                border-collapse: collapse;
                color: #000000;
            }

            .invoice-table td {
                padding: 3px 5px;
            }

            .no-border {
                border: none;
            }

            .border-all {
                border: 1px solid #000000;
            }

            .border-top {
                border-top: 1px solid #000000;
            }

            .border-bottom {
                border-bottom: 1px solid #000000;
            }

            .border-left {
                border-left: 1px solid #000000;
            }

            .border-right {
                border-right: 1px solid #000000;
            }

            .company-title {
                font-size: 16px;
                font-weight: bold;
                text-align: center;
                color: #000000;
                background-color: #ffffff;
            }

            .company-address {
                font-size: 11px;
                text-align: center;
                color: #000000;
                background-color: #ffffff;
            }

            .logo-box {
                width: 75px;
                height: 55px;
                border: 1px dashed #000000;
                background-color: #ffffff;
            }

            .copy-label {
                font-size: 10px;
                font-weight: bold;
                text-align: center;
                color: #000000;
                border: 1px solid #000000;
                background-color: #ffffff;
            }

            .invoice-title {
                font-size: 15px;
                font-weight: bold;
                color: #000000;
                background-color: #ffffff;
            }

            .section-title {
                font-weight: bold;
                color: #000000;
                background-color: #ffffff;
                border-bottom: 1px solid #000000;
            }

            .table-header {
                font-weight: bold;
                text-align: center;
                color: #000000;
                background-color: #ffffff;
                border-top: 1px solid #000000;
                border-bottom: 1px solid #000000;
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

            .text-left {
                text-align: left;
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

            .total-label {
                font-weight: bold;
                text-align: right;
                color: #000000;
                background-color: #ffffff;
            }

            .total-value {
                font-weight: bold;
                text-align: right;
                color: #000000;
                background-color: #ffffff;
                mso-number-format: "#,##0";
            }

            .terbilang {
                font-size: 11px;
                color: #000000;
            }

            .terbilang-text {
                font-style: italic;
                color: #000000;
            }

            .stamp-cell {
                color: #b91c1c;
                font-family: "Times New Roman", serif;
                text-align: center;
                background-color: #ffffff;
                border: none;
                height: 54px;
            }

            .stamp-company {
                color: #b91c1c;
                font-size: 12px;
                font-weight: bold;
                line-height: 1.1;
            }

            .stamp-bank {
                color: #b91c1c;
                font-size: 11px;
                font-weight: bold;
                line-height: 1.1;
            }

            .stamp-separator {
                color: #b91c1c;
                font-size: 10px;
                line-height: 1;
            }

            .signature-space {
                height: 55px;
            }
        </style>
    </head>

    <body>
        <table class="invoice-table">
            <colgroup>
                <col style="width: 5%;">
                <col style="width: 12%;">
                <col style="width: 20%;">
                <col style="width: 10%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 13%;">
            </colgroup>

            <tr>
                <td rowspan="3" class="logo-box"></td>
                <td colspan="8" class="company-title">
                    {{ $namaPerusahaan }}
                </td>
                <td class="copy-label">
                    CUSTOMER
                </td>
            </tr>

            <tr>
                <td colspan="8" class="company-address">
                    {{ $alamatPerusahaan }}
                </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="8" class="company-address">
                    Telp: {{ $teleponPerusahaan }}
                </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="10" class="border-bottom"></td>
            </tr>

            <tr>
                <td colspan="5" class="invoice-title">
                    INVOICE / NOTA PENJUALAN
                </td>
                <td colspan="5" class="text-right">
                    <span class="bold">Tanggal:</span>
                    {{ $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-' }}
                </td>
            </tr>

            <tr>
                <td colspan="5" class="text-format">
                    <span class="bold">No:</span> {{ $penjualan->nomor_invoice }}
                </td>
                <td colspan="5" class="text-right">
                    <span class="bold">Pembayaran:</span> {{ $metodePembayaran }}
                </td>
            </tr>

            <tr>
                <td colspan="5"></td>
                <td colspan="5" class="text-right">
                    <span class="bold">Status:</span> {{ $statusPembayaran }}
                </td>
            </tr>

            <tr>
                <td colspan="10"></td>
            </tr>

            <tr>
                <td colspan="5" class="section-title">
                    Informasi Customer
                </td>
                <td colspan="5" class="section-title">
                    Informasi Transaksi
                </td>
            </tr>

            <tr>
                <td colspan="1" class="bold">Nama</td>
                <td colspan="4">
                    : {{ $penjualan->customer->nama_customer ?? '-' }}
                </td>

                <td colspan="1" class="bold">Jatuh Tempo</td>
                <td colspan="4">
                    : {{ $penjualan->tanggal_jatuh_tempo ? $penjualan->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                </td>
            </tr>

            <tr>
                <td colspan="1" class="bold">Telepon</td>
                <td colspan="4" class="text-format">
                    :
                    @if ($nomorTeleponCustomer !== '-')
                    &#8203;{{ $nomorTeleponCustomer }}
                    @else
                    -
                    @endif
                </td>

                <td colspan="1" class="bold">Admin</td>
                <td colspan="4">
                    : {{ $penjualan->user->nama_user ?? '-' }}
                </td>
            </tr>

            <tr>
                <td colspan="1" class="bold">NPWP</td>
                <td colspan="4" class="text-format">
                    :
                    @if ($npwpCustomer !== '-')
                    &#8203;{{ $npwpCustomer }}
                    @else
                    -
                    @endif
                </td>

                <td colspan="1" class="bold">Mode Pajak</td>
                <td colspan="4">
                    : {{ $modePajak }}
                </td>
            </tr>

            <tr>
                <td colspan="1" class="bold">Alamat</td>
                <td colspan="4">
                    : {{ $penjualan->customer->alamat ?? '-' }}
                </td>

                <td colspan="1" class="bold">Catatan</td>
                <td colspan="4">
                    : {{ $penjualan->catatan ?? '-' }}
                </td>
            </tr>

            <tr>
                <td colspan="1" class="bold">Kategori</td>
                <td colspan="4">
                    : {{ $penjualan->customer->kategori_customer ?? '-' }}
                </td>

                <td colspan="5"></td>
            </tr>

            <tr>
                <td colspan="10"></td>
            </tr>

            <tr>
                <td colspan="10" class="section-title">
                    Daftar Barang
                </td>
            </tr>

            <tr class="table-header">
                <td class="border-all">No</td>
                <td class="border-all">Kode Barang</td>
                <td class="border-all">Nama Barang</td>
                <td class="border-all">Satuan</td>
                <td class="border-all">Qty</td>
                <td class="border-all">Satuan Harga</td>
                <td class="border-all">Isi/Satuan</td>
                <td class="border-all">Harga</td>
                <td class="border-all">Perhitungan</td>
                <td class="border-all">Subtotal</td>
            </tr>

            @foreach ($penjualan->detailPenjualan as $detail)
            @php
            $tipePerhitungan = $detail->tipe_perhitungan_harga ?? 'normal';
            $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
            $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
            $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);

            if ($tipePerhitungan === 'isi_kemasan') {
            $teksPerhitungan =
            $detail->jumlah . ' ' . strtoupper($satuanTransaksi) .
            ' x ' . $formatAngka($isiPerSatuan) . ' ' . strtoupper($satuanHitung) .
            ' x Rp ' . number_format($detail->harga_jual, 0, ',', '.');
            } else {
            $teksPerhitungan =
            $detail->jumlah . ' ' . strtoupper($satuanTransaksi) .
            ' x Rp ' . number_format($detail->harga_jual, 0, ',', '.');
            }
            @endphp

            <tr>
                <td class="border-all text-center">
                    {{ $loop->iteration }}
                </td>

                <td class="border-all text-format">
                    {{ $detail->barang->kode_barang ?? '-' }}
                </td>

                <td class="border-all">
                    {{ $detail->barang->nama_barang ?? '-' }}
                </td>

                <td class="border-all text-center">
                    {{ strtoupper($satuanTransaksi) }}
                </td>

                <td class="border-all text-center number-format">
                    {{ $detail->jumlah }}
                </td>

                <td class="border-all text-center">
                    {{ strtoupper($tipePerhitungan === 'isi_kemasan' ? $satuanHitung : $satuanTransaksi) }}
                </td>

                <td class="border-all text-center number-format">
                    @if ($tipePerhitungan === 'isi_kemasan')
                    {{ $isiPerSatuan }}
                    @else
                    1
                    @endif
                </td>

                <td class="border-all text-right currency">
                    {{ $detail->harga_jual }}
                </td>

                <td class="border-all">
                    {{ $teksPerhitungan }}
                </td>

                <td class="border-all text-right currency">
                    {{ $detail->subtotal }}
                </td>
            </tr>
            @endforeach

            <tr>
                <td colspan="8"></td>
                <td class="total-label border-bottom">Subtotal</td>
                <td class="total-value border-bottom">
                    {{ $penjualan->subtotal }}
                </td>
            </tr>

            <tr>
                <td colspan="8"></td>
                <td class="total-label border-bottom">
                    Pajak {{ number_format($penjualan->persentase_pajak, 2, ',', '.') }}%
                </td>
                <td class="total-value border-bottom">
                    {{ $penjualan->nilai_pajak }}
                </td>
            </tr>

            <tr>
                <td colspan="8"></td>
                <td class="total-label border-bottom">Total Akhir</td>
                <td class="total-value border-bottom">
                    {{ $penjualan->total_akhir }}
                </td>
            </tr>

            <tr>
                <td colspan="10"></td>
            </tr>

            <tr>
                <td colspan="6" class="terbilang">
                    <span class="bold">Terbilang:</span>
                    <span class="terbilang-text">{{ $terbilangTotal }}</span>
                </td>
                <td colspan="4"></td>
            </tr>

            <tr>
                <td colspan="4" class="stamp-cell">
                    <div class="stamp-company">Berkat</div>
                    <div class="stamp-bank">BCA : 5280902227</div>
                    <div class="stamp-separator">------------------------------</div>
                    <div class="stamp-company">Berkat</div>
                    <div class="stamp-bank">OCBC NISP : 565 8000 15150</div>
                </td>
                <td colspan="6"></td>
            </tr>

            @if ($penjualan->piutang)
            <tr>
                <td colspan="10"></td>
            </tr>

            <tr>
                <td colspan="10" class="section-title">
                    Informasi Piutang
                </td>
            </tr>

            <tr>
                <td colspan="2" class="bold border-all">Total Piutang</td>
                <td colspan="2" class="text-right currency border-all">
                    {{ $penjualan->piutang->total_piutang }}
                </td>

                <td colspan="2" class="bold border-all">Total Dibayar</td>
                <td colspan="2" class="text-right currency border-all">
                    {{ $penjualan->piutang->total_dibayar }}
                </td>

                <td class="bold border-all">Sisa Piutang</td>
                <td class="text-right currency border-all">
                    {{ $penjualan->piutang->sisa_piutang }}
                </td>
            </tr>

            <tr>
                <td colspan="2" class="bold border-all">Tanggal Jatuh Tempo</td>
                <td colspan="2" class="border-all">
                    {{ $penjualan->piutang->tanggal_jatuh_tempo ? $penjualan->piutang->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                </td>

                <td colspan="2" class="bold border-all">Status Piutang</td>
                <td colspan="4" class="border-all">
                    {{ str_replace('_', ' ', ucfirst($penjualan->piutang->status_piutang)) }}
                </td>
            </tr>

            <tr>
                <td colspan="2" class="bold border-all">Catatan Piutang</td>
                <td colspan="8" class="border-all">
                    {{ $penjualan->piutang->catatan ?? '-' }}
                </td>
            </tr>
            @endif

            <tr>
                <td colspan="10"></td>
            </tr>

            <tr>
                <td colspan="5" class="text-center bold">
                    Penerima,
                </td>
                <td colspan="5" class="text-center bold">
                    Hormat Kami,
                </td>
            </tr>

            <tr>
                <td colspan="5" class="signature-space"></td>
                <td colspan="5" class="signature-space"></td>
            </tr>

            <tr>
                <td colspan="5" class="text-center bold">
                    {{ $penjualan->customer->nama_customer ?? 'Customer' }}
                </td>
                <td colspan="5" class="text-center bold">
                    {{ $namaPerusahaan }}
                </td>
            </tr>
        </table>
    </body>

    </html>