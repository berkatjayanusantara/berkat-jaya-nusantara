@php
$namaPerusahaan = 'CV. BERKAT JAYA NUSANTARA';
$alamatPerusahaan = 'Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460';
$teleponPerusahaan = '(021) 5664892, 5676277';

$periodeAwal = $tanggalAwal === 'awal' ? 'Awal' : $tanggalAwal;
$periodeAkhir = $tanggalAkhir === 'akhir' ? 'Akhir' : $tanggalAkhir;

$formatRupiah = function ($nilai) {
return 'Rp ' . number_format((float) ($nilai ?? 0), 0, ',', '.');
};

$formatAngka = function ($nilai) {
return rtrim(rtrim(number_format((float) ($nilai ?? 0), 3, ',', '.'), '0'), ',');
};

$normalisasiModePpn = function ($modePpn, $persentasePajak = 0, $pajakDitambahkan = false) {
if (in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
return $modePpn;
}

if ((float) ($persentasePajak ?? 0) <= 0) {
    return 'tanpa_ppn' ;
    }

    return (bool) $pajakDitambahkan ? 'exclude' : 'include' ;
    };

    $labelModePpn=function ($modePpn) {
    return [ 'tanpa_ppn'=> 'Tanpa PPN',
    'include' => 'PPN Include',
    'exclude' => 'PPN Exclude',
    ][$modePpn] ?? 'Tanpa PPN';
    };

    $labelStatusPembayaran = function ($status) {
    return [
    'lunas' => 'Lunas',
    'sebagian' => 'Sebagian',
    'belum_lunas' => 'Belum Lunas',
    ][$status] ?? 'Belum Lunas';
    };

    $labelPenyesuaian = function ($jenis, $nominal) use ($formatRupiah) {
    $nominal = (float) ($nominal ?? 0);
    if ($nominal <= 0 || !$jenis || $jenis==='tidak_ada' ) {
        return '-' ;
        }

        return ($jenis==='tambah' ? '+' : '-' ) . $formatRupiah($nominal);
        };
        @endphp

        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <title>Laporan Penjualan</title>

            <style>
                body {
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 7px;
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
                    font-size: 7.5px;
                    color: #4b5563;
                    margin-bottom: 4px;
                }

                .title {
                    text-align: center;
                    font-size: 15px;
                    font-weight: bold;
                    margin-bottom: 3px;
                }

                .subtitle {
                    text-align: center;
                    font-size: 8px;
                    margin-bottom: 8px;
                }

                .summary-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 7px;
                }

                .summary-table td {
                    border: 1px solid #d1d5db;
                    padding: 3px;
                    vertical-align: top;
                }

                .summary-label {
                    font-size: 6.5px;
                    color: #4b5563;
                }

                .summary-value {
                    font-size: 8px;
                    font-weight: bold;
                    margin-top: 1px;
                }

                .data-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .data-table th {
                    border: 1px solid #9ca3af;
                    background-color: #e5e7eb;
                    padding: 3px 2px;
                    font-weight: bold;
                    text-align: center;
                }

                .data-table td {
                    border: 1px solid #d1d5db;
                    padding: 2px;
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
                    font-size: 6px;
                    color: #4b5563;
                }

                .footer {
                    margin-top: 8px;
                    font-size: 7px;
                    color: #6b7280;
                    text-align: right;
                }
            </style>
        </head>

        <body>
            <div class="company">{{ $namaPerusahaan }}</div>
            <div class="company-info">{{ $alamatPerusahaan }} | Telp: {{ $teleponPerusahaan }}</div>
            <div class="title">LAPORAN PENJUALAN</div>
            <div class="subtitle">
                Periode: {{ $periodeAwal }} s/d {{ $periodeAkhir }} | Dicetak: {{ now()->format('d-m-Y H:i') }}
            </div>

            <table class="summary-table">
                <tr>
                    <td>
                        <div class="summary-label">Total Transaksi</div>
                        <div class="summary-value">{{ $totalTransaksi ?? 0 }}</div>
                        <div class="small-text">Sistem: {{ $totalSistemBerjalan ?? 0 }} | Historis: {{ $totalHistoris ?? 0 }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Total Subtotal</div>
                        <div class="summary-value">{{ $formatRupiah($totalSubtotal ?? 0) }}</div>
                        <div class="small-text">Kena PPN: {{ $formatRupiah($totalSubtotalKenaPpn ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Total PPN</div>
                        <div class="summary-value">{{ $formatRupiah($totalPajak ?? 0) }}</div>
                        <div class="small-text">DPP: {{ $formatRupiah($totalDppPpn ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Total Akhir</div>
                        <div class="summary-value">{{ $formatRupiah($totalAkhir ?? 0) }}</div>
                        <div class="small-text">Sebelum Adj: {{ $formatRupiah($totalSebelumPenyesuaian ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Sisa Piutang</div>
                        <div class="summary-value">{{ $formatRupiah($totalSisaPiutang ?? 0) }}</div>
                        <div class="small-text">Dibayar: {{ $formatRupiah($totalDibayar ?? 0) }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="summary-label">Tunai / Kredit</div>
                        <div class="summary-value">{{ $formatRupiah($totalTunai ?? 0) }} / {{ $formatRupiah($totalKredit ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Mode PPN</div>
                        <div class="summary-value">Tanpa: {{ $totalPpnTanpaPpn ?? 0 }} | Inc: {{ $totalPpnInclude ?? 0 }} | Exc: {{ $totalPpnExclude ?? 0 }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Faktur Pajak</div>
                        <div class="summary-value">Butuh: {{ $totalButuhFakturPajak ?? 0 }} | Tidak: {{ $totalTidakButuhFakturPajak ?? 0 }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Penyesuaian</div>
                        <div class="summary-value">+{{ $formatRupiah($totalPenyesuaianTambah ?? 0) }} / -{{ $formatRupiah($totalPenyesuaianKurang ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="summary-label">Detail Barang</div>
                        <div class="summary-value">Baris: {{ $totalItemBarang ?? 0 }} | Qty: {{ $formatAngka($totalJumlahTerjual ?? 0) }}</div>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 3%;">No</th>
                        <th style="width: 6%;">Tanggal</th>
                        <th style="width: 9%;">Invoice</th>
                        <th style="width: 11%;">Customer</th>
                        <th style="width: 17%;">Detail Barang</th>
                        <th style="width: 8%;">Bayar</th>
                        <th style="width: 8%;">Tipe</th>
                        <th style="width: 8%;">PPN / Faktur</th>
                        <th style="width: 7%;">Subtotal</th>
                        <th style="width: 7%;">DPP</th>
                        <th style="width: 6%;">PPN</th>
                        <th style="width: 6%;">Adj</th>
                        <th style="width: 7%;">Total</th>
                        <th style="width: 7%;">Sisa</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($penjualan as $item)
                    @php
                    $isHistoris = (bool) ($item->is_historical ?? false);
                    $nomorInvoiceTampil = $isHistoris && !empty($item->nomor_dokumen_asli)
                    ? $item->nomor_dokumen_asli
                    : $item->nomor_invoice;
                    $affectStock = (bool) ($item->affect_stock ?? true);
                    $modePpn = $normalisasiModePpn($item->mode_ppn ?? null, $item->persentase_pajak ?? 0, $item->pajak_ditambahkan ?? false);
                    $statusPembayaran = $labelStatusPembayaran($item->status_pembayaran);
                    $statusClass = $item->status_pembayaran === 'lunas' ? 'status-lunas' : ($item->status_pembayaran === 'sebagian' ? 'status-sebagian' : 'status-belum');
                    $tipeLabel = $isHistoris ? 'Historis' : 'Sistem';
                    $tipeClass = $isHistoris ? 'badge-historis' : 'badge-sistem';
                    $penyesuaian = $labelPenyesuaian($item->jenis_penyesuaian_total ?? 'tidak_ada', $item->nominal_penyesuaian_total ?? 0);
                    @endphp

                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $item->tanggal_penjualan ? $item->tanggal_penjualan->format('d-m-Y') : '-' }}</td>
                        <td>
                            <strong>{{ $nomorInvoiceTampil }}</strong>
                            @if (!$isHistoris && $item->nomor_dokumen_asli)
                            <br><span class="small-text">Asli: {{ $item->nomor_dokumen_asli }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $item->customer->nama_customer ?? '-' }}
                            <br><span class="small-text">{{ $item->customer->nomor_telepon ?? '-' }}</span>
                            @if ($item->customer?->npwp)
                            <br><span class="small-text">NPWP: {{ $item->customer->npwp }}</span>
                            @endif
                        </td>
                        <td>
                            @forelse ($item->detailPenjualan as $detail)
                            @php
                            $tipeHarga = $detail->tipe_perhitungan_harga ?? 'normal';
                            $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '-');
                            $satuanHitungHarga = $detail->satuan_hitung_harga ?? $satuanTransaksi;
                            $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);
                            $kenaPpn = (bool) ($detail->kena_ppn ?? false);
                            $rumus = $tipeHarga === 'isi_kemasan'
                            ? $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatAngka($isiPerSatuan) . ' ' . $satuanHitungHarga . ' x ' . $formatRupiah($detail->harga_jual)
                            : $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $formatRupiah($detail->harga_jual);
                            @endphp
                            <strong>{{ $detail->barang->kode_barang ?? '-' }}</strong> - {{ $detail->barang->nama_barang ?? '-' }}
                            <br><span class="small-text">{{ $rumus }} = {{ $formatRupiah($detail->subtotal ?? 0) }}</span>
                            <br><span class="small-text">{{ $tipeHarga === 'isi_kemasan' ? 'Isi Kemasan' : 'Normal' }} | {{ $kenaPpn ? 'Kena PPN' : 'Non PPN' }} | PPN: {{ $formatRupiah($detail->nilai_ppn ?? 0) }}</span>
                            @if (!$loop->last)<br><br>@endif
                            @empty
                            Detail barang tidak tersedia.
                            @endforelse
                        </td>
                        <td class="text-center">
                            {{ ucfirst($item->metode_pembayaran ?? '-') }}
                            <br><span class="{{ $statusClass }}">{{ $statusPembayaran }}</span>
                            @if ($item->tanggal_jatuh_tempo)
                            <br><span class="small-text">JT: {{ $item->tanggal_jatuh_tempo->format('d-m-Y') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="{{ $tipeClass }}">{{ $tipeLabel }}</span>
                            <br><span class="small-text">{{ $affectStock ? 'Pengaruh Stok' : 'Tanpa Stok' }}</span>
                        </td>
                        <td>
                            {{ $labelModePpn($modePpn) }}
                            <br><span class="small-text">Kena: {{ $formatRupiah($item->subtotal_kena_ppn ?? 0) }}</span>
                            <br><span class="small-text">Faktur: {{ $item->butuh_faktur_pajak ? 'Ya' : 'Tidak' }}</span>
                            @if ($item->nomor_faktur_pajak)
                            <br><span class="small-text">{{ $item->nomor_faktur_pajak }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ $formatRupiah($item->subtotal ?? 0) }}</td>
                        <td class="text-right">{{ $formatRupiah($item->dpp_ppn ?? 0) }}</td>
                        <td class="text-right">{{ $formatRupiah($item->nilai_pajak ?? 0) }}</td>
                        <td class="text-right">{{ $penyesuaian }}</td>
                        <td class="text-right">{{ $formatRupiah($item->total_akhir ?? 0) }}</td>
                        <td class="text-right">{{ $formatRupiah($item->piutang->sisa_piutang ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center">Data laporan penjualan belum tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="footer">
                Laporan ini dibuat otomatis oleh sistem Berkat Jaya Nusantara.
            </div>
        </body>

        </html>