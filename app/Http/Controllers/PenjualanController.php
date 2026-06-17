<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $penjualan = Penjualan::with(['customer', 'user'])
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_invoice', 'like', "%{$search}%")
                        ->orWhere('nomor_dokumen_asli', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('nama_customer', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('tanggal_penjualan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('penjualan.index', compact('penjualan', 'search'));
    }

    public function create()
    {
        $customers = Customer::where('status_aktif', true)->orderBy('nama_customer')->get();
        $barang = Barang::where('status_aktif', true)->orderBy('nama_barang')->get();

        return view('penjualan.create', compact('customers', 'barang'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rulesPenjualan($request, null));

        DB::transaction(function () use ($request) {
            $ringkasanSubtotal = $this->hitungSubtotalPenjualanDariRequest($request, true);
            $perhitunganPpn = $this->hitungPpnPenjualan(
                $ringkasanSubtotal['subtotal_penjualan'],
                $ringkasanSubtotal['subtotal_kena_ppn'],
                $request->mode_ppn
            );

            $perhitunganTotal = $this->hitungTotalDenganPenyesuaian(
                $perhitunganPpn['total_akhir'],
                $request->jenis_penyesuaian_total,
                $request->nominal_penyesuaian_total,
                $request->keterangan_penyesuaian_total
            );

            $statusPembayaran = $request->metode_pembayaran === 'tunai' ? 'lunas' : 'belum_lunas';

            $penjualan = Penjualan::create(array_merge([
                'nomor_invoice' => trim($request->nomor_invoice),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $ringkasanSubtotal['subtotal_penjualan'],
                'subtotal_kena_ppn' => $perhitunganPpn['subtotal_kena_ppn'],
                'subtotal_non_ppn' => $perhitunganPpn['subtotal_non_ppn'],
                'dpp_ppn' => $perhitunganPpn['dpp_ppn'],
                'persentase_pajak' => $perhitunganPpn['persentase_pajak'],
                'mode_ppn' => $perhitunganPpn['mode_ppn'],
                'nilai_pajak' => $perhitunganPpn['nilai_pajak'],
                'pajak_ditambahkan' => $perhitunganPpn['pajak_ditambahkan'],
                'total_sebelum_penyesuaian' => $perhitunganTotal['total_sebelum_penyesuaian'],
                'jenis_penyesuaian_total' => $perhitunganTotal['jenis_penyesuaian_total'],
                'nominal_penyesuaian_total' => $perhitunganTotal['nominal_penyesuaian_total'],
                'keterangan_penyesuaian_total' => $perhitunganTotal['keterangan_penyesuaian_total'],
                'total_akhir' => $perhitunganTotal['total_akhir'],
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->metode_pembayaran === 'kredit' ? $request->tanggal_jatuh_tempo : null,
                'catatan' => $request->catatan,
                'dibuat_oleh' => Auth::id(),
            ], $this->ambilDataFakturPajak($request)));

            $this->simpanDetailPenjualanDariRequest($penjualan, $request, true, 'Stok keluar dari penjualan');

            if ($request->metode_pembayaran === 'kredit') {
                Piutang::create([
                    'id_penjualan' => $penjualan->id_penjualan,
                    'nomor_invoice' => $penjualan->nomor_invoice,
                    'id_customer' => $penjualan->id_customer,
                    'total_piutang' => $penjualan->total_akhir,
                    'total_dibayar' => 0,
                    'sisa_piutang' => $penjualan->total_akhir,
                    'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                    'status_piutang' => 'belum_lunas',
                    'catatan' => 'Piutang otomatis dari transaksi penjualan kredit',
                ]);
            }
        });

        return redirect()->route('penjualan.index')->with('success', 'Transaksi penjualan berhasil disimpan.');
    }

    public function edit(Penjualan $penjualan)
    {
        $penjualan->load(['customer', 'detailPenjualan.barang', 'piutang.pembayaranPiutang']);

        $customers = Customer::where('status_aktif', true)
            ->orWhere('id_customer', $penjualan->id_customer)
            ->orderBy('nama_customer')
            ->get();

        $barang = Barang::where('status_aktif', true)
            ->orWhereIn('id_barang', $penjualan->detailPenjualan->pluck('id_barang'))
            ->orderBy('nama_barang')
            ->get();

        return view('penjualan.edit', compact('penjualan', 'customers', 'barang'));
    }

    public function update(Request $request, Penjualan $penjualan)
    {
        $request->validate($this->rulesPenjualan($request, $penjualan));

        DB::transaction(function () use ($request, $penjualan) {
            $penjualan->load(['detailPenjualan', 'piutang.pembayaranPiutang']);

            $totalDibayarLama = $penjualan->piutang ? (float) $penjualan->piutang->total_dibayar : 0;

            if ($request->metode_pembayaran === 'tunai' && $totalDibayarLama > 0) {
                throw ValidationException::withMessages([
                    'metode_pembayaran' => 'Penjualan kredit yang sudah memiliki pembayaran piutang tidak bisa diubah menjadi tunai. Hapus/atur pembayaran piutang terlebih dahulu jika memang diperlukan.',
                ]);
            }

            $affectStock = $penjualan->affect_stock ?? true;

            if ($affectStock) {
                foreach ($penjualan->detailPenjualan as $detailLama) {
                    $barangLama = Barang::where('id_barang', $detailLama->id_barang)->lockForUpdate()->first();

                    if (!$barangLama) {
                        continue;
                    }

                    $stokSebelum = $barangLama->stok_saat_ini;
                    $stokSesudah = $stokSebelum + $detailLama->jumlah;

                    $barangLama->update(['stok_saat_ini' => $stokSesudah]);

                    RiwayatStok::create([
                        'id_barang' => $barangLama->id_barang,
                        'tanggal' => $request->tanggal_penjualan,
                        'jenis_pergerakan' => 'penyesuaian',
                        'jumlah' => $detailLama->jumlah,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'sumber_transaksi' => $penjualan->nomor_invoice,
                        'keterangan' => 'Pengembalian stok karena edit penjualan',
                        'dibuat_oleh' => Auth::id(),
                        'created_at' => now(),
                    ]);
                }
            }

            $ringkasanSubtotal = $this->hitungSubtotalPenjualanDariRequest($request, $affectStock);
            $perhitunganPpn = $this->hitungPpnPenjualan(
                $ringkasanSubtotal['subtotal_penjualan'],
                $ringkasanSubtotal['subtotal_kena_ppn'],
                $request->mode_ppn
            );

            $perhitunganTotal = $this->hitungTotalDenganPenyesuaian(
                $perhitunganPpn['total_akhir'],
                $request->jenis_penyesuaian_total,
                $request->nominal_penyesuaian_total,
                $request->keterangan_penyesuaian_total
            );

            $statusPembayaran = $this->hitungStatusPembayaran($request->metode_pembayaran, $perhitunganTotal['total_akhir'], $totalDibayarLama);

            $penjualan->update(array_merge([
                'nomor_invoice' => trim($request->nomor_invoice),
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'id_customer' => $request->id_customer,
                'subtotal' => $ringkasanSubtotal['subtotal_penjualan'],
                'subtotal_kena_ppn' => $perhitunganPpn['subtotal_kena_ppn'],
                'subtotal_non_ppn' => $perhitunganPpn['subtotal_non_ppn'],
                'dpp_ppn' => $perhitunganPpn['dpp_ppn'],
                'persentase_pajak' => $perhitunganPpn['persentase_pajak'],
                'mode_ppn' => $perhitunganPpn['mode_ppn'],
                'nilai_pajak' => $perhitunganPpn['nilai_pajak'],
                'pajak_ditambahkan' => $perhitunganPpn['pajak_ditambahkan'],
                'total_sebelum_penyesuaian' => $perhitunganTotal['total_sebelum_penyesuaian'],
                'jenis_penyesuaian_total' => $perhitunganTotal['jenis_penyesuaian_total'],
                'nominal_penyesuaian_total' => $perhitunganTotal['nominal_penyesuaian_total'],
                'keterangan_penyesuaian_total' => $perhitunganTotal['keterangan_penyesuaian_total'],
                'total_akhir' => $perhitunganTotal['total_akhir'],
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_jatuh_tempo' => $request->metode_pembayaran === 'kredit' ? $request->tanggal_jatuh_tempo : null,
                'catatan' => $request->catatan,
            ], $this->ambilDataFakturPajak($request)));

            $penjualan->detailPenjualan()->delete();
            $this->simpanDetailPenjualanDariRequest($penjualan, $request, $affectStock, 'Stok keluar dari edit penjualan');

            $this->sinkronkanPiutangSetelahEdit($penjualan, $request, $perhitunganTotal['total_akhir'], $totalDibayarLama);
        });

        return redirect()->route('penjualan.show', $penjualan->id_penjualan)->with('success', 'Transaksi penjualan berhasil diperbarui.');
    }

    public function show(Penjualan $penjualan)
    {
        $penjualan->load(['customer', 'user', 'detailPenjualan.barang', 'piutang']);
        return view('penjualan.show', compact('penjualan'));
    }

    public function exportExcel(Penjualan $penjualan)
    {
        $penjualan->load([
            'customer',
            'user',
            'detailPenjualan.barang',
            'piutang',
        ]);

        $modePpn = $this->normalisasiModePpn($penjualan->mode_ppn ?? null, $penjualan);
        $labelModePpn = $this->labelModePpn($modePpn);

        $isInvoiceHistoris = (bool) ($penjualan->is_historical ?? false);
        $nomorInvoiceTampil = $isInvoiceHistoris && !empty($penjualan->nomor_dokumen_asli)
            ? $penjualan->nomor_dokumen_asli
            : $penjualan->nomor_invoice;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice Penjualan');

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        foreach (['A' => 6, 'B' => 18, 'C' => 30, 'D' => 12, 'E' => 16, 'F' => 16, 'G' => 18] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $border = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $headerFill = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EFEFEF'],
            ],
        ];

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'CV. BERKAT JAYA NUSANTARA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'INVOICE / NOTA PENJUALAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'Nomor Invoice');
        $sheet->setCellValue('B4', $nomorInvoiceTampil);

        if ($isInvoiceHistoris && !empty($penjualan->nomor_invoice)) {
            $sheet->setCellValue('A5', 'Nomor Sistem');
            $sheet->setCellValue('B5', $penjualan->nomor_invoice);

            $sheet->setCellValue('D4', 'Tanggal');
            $sheet->setCellValue('E4', $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-');

            $sheet->setCellValue('D5', 'Metode Bayar');
            $sheet->setCellValue('E5', ucfirst($penjualan->metode_pembayaran ?? '-'));

            $sheet->setCellValue('A6', 'Customer');
            $sheet->setCellValue('B6', $penjualan->customer->nama_customer ?? '-');
            $sheet->setCellValue('D6', 'Status');
            $sheet->setCellValue('E6', str_replace('_', ' ', ucfirst($penjualan->status_pembayaran ?? '-')));

            $sheet->setCellValue('A7', 'NPWP');
            $sheet->setCellValue('B7', $penjualan->customer->npwp ?? '-');

            $sheet->setCellValue('A8', 'Mode PPN');
            $sheet->setCellValue('B8', $labelModePpn);
            $sheet->setCellValue('D8', 'Butuh Faktur Pajak');
            $sheet->setCellValue('E8', $penjualan->butuh_faktur_pajak ? 'Ya' : 'Tidak');

            $infoRowFaktur = 9;
            $rowTanpaFaktur = 10;
            $rowDenganFaktur = 12;
        } else {
            $sheet->setCellValue('D4', 'Tanggal');
            $sheet->setCellValue('E4', $penjualan->tanggal_penjualan ? $penjualan->tanggal_penjualan->format('d-m-Y') : '-');

            $sheet->setCellValue('A5', 'Customer');
            $sheet->setCellValue('B5', $penjualan->customer->nama_customer ?? '-');
            $sheet->setCellValue('D5', 'Metode Bayar');
            $sheet->setCellValue('E5', ucfirst($penjualan->metode_pembayaran ?? '-'));

            $sheet->setCellValue('A6', 'NPWP');
            $sheet->setCellValue('B6', $penjualan->customer->npwp ?? '-');
            $sheet->setCellValue('D6', 'Status');
            $sheet->setCellValue('E6', str_replace('_', ' ', ucfirst($penjualan->status_pembayaran ?? '-')));

            $sheet->setCellValue('A7', 'Mode PPN');
            $sheet->setCellValue('B7', $labelModePpn);
            $sheet->setCellValue('D7', 'Butuh Faktur Pajak');
            $sheet->setCellValue('E7', $penjualan->butuh_faktur_pajak ? 'Ya' : 'Tidak');

            $infoRowFaktur = 8;
            $rowTanpaFaktur = 9;
            $rowDenganFaktur = 11;
        }

        if ($penjualan->butuh_faktur_pajak) {
            $sheet->setCellValue('A' . $infoRowFaktur, 'No Faktur');
            $sheet->setCellValue('B' . $infoRowFaktur, $penjualan->nomor_faktur_pajak ?? '-');
            $sheet->setCellValue('D' . $infoRowFaktur, 'Tanggal Faktur');
            $sheet->setCellValue('E' . $infoRowFaktur, $penjualan->tanggal_faktur_pajak ? $penjualan->tanggal_faktur_pajak->format('d-m-Y') : '-');

            $sheet->setCellValue('A' . ($infoRowFaktur + 1), 'Nama Faktur');
            $sheet->setCellValue('B' . ($infoRowFaktur + 1), $penjualan->nama_faktur_pajak ?? '-');
            $sheet->setCellValue('D' . ($infoRowFaktur + 1), 'NPWP Faktur');
            $sheet->setCellValue('E' . ($infoRowFaktur + 1), $penjualan->npwp_faktur_pajak ?? '-');

            $row = $rowDenganFaktur;
        } else {
            $row = $rowTanpaFaktur;
        }

        $sheet->fromArray(['No', 'Kode Barang', 'Nama Barang', 'Qty', 'Harga', 'Subtotal', 'Keterangan'], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerFill);
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($border);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $row++;

        foreach ($penjualan->detailPenjualan as $index => $detail) {
            $tipe = $detail->tipe_perhitungan_harga ?? 'normal';
            $satuanTransaksi = $detail->satuan_transaksi ?? ($detail->barang->satuan ?? '');
            $satuanHitung = $detail->satuan_hitung_harga ?? $satuanTransaksi;
            $isiPerSatuan = (float) ($detail->isi_per_satuan ?? 1);

            $keterangan = $tipe === 'isi_kemasan'
                ? $detail->jumlah . ' ' . $satuanTransaksi . ' x ' . $isiPerSatuan . ' ' . $satuanHitung . ' x harga per ' . $satuanHitung
                : 'Harga per ' . $satuanTransaksi;

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $detail->barang->kode_barang ?? '-');
            $sheet->setCellValue('C' . $row, $detail->barang->nama_barang ?? '-');
            $sheet->setCellValue('D' . $row, $detail->jumlah . ' ' . $satuanTransaksi);
            $sheet->setCellValue('E' . $row, (float) $detail->harga_jual);
            $sheet->setCellValue('F' . $row, (float) $detail->subtotal);
            $sheet->setCellValue('G' . $row, $keterangan);
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($border);
            $row++;
        }

        $row++;
        $sheet->setCellValue('E' . $row, 'Subtotal');
        $sheet->setCellValue('F' . $row, (float) $penjualan->subtotal);
        $row++;

        if ($modePpn !== 'tanpa_ppn') {
            $sheet->setCellValue('E' . $row, 'DPP');
            $sheet->setCellValue('F' . $row, (float) ($penjualan->dpp_ppn ?? $penjualan->subtotal));
            $row++;

            $sheet->setCellValue('E' . $row, 'PPN 11%');
            $sheet->setCellValue('F' . $row, (float) $penjualan->nilai_pajak);
            $row++;
        } else {
            $sheet->setCellValue('E' . $row, 'PPN');
            $sheet->setCellValue('F' . $row, 0);
            $row++;
        }

        $totalSebelumPenyesuaian = (float) ($penjualan->total_sebelum_penyesuaian ?? $penjualan->total_akhir);
        $jenisPenyesuaian = $penjualan->jenis_penyesuaian_total ?? 'tidak_ada';
        $nominalPenyesuaian = (float) ($penjualan->nominal_penyesuaian_total ?? 0);

        $sheet->setCellValue('E' . $row, 'Total Sebelum Penyesuaian');
        $sheet->setCellValue('F' . $row, $totalSebelumPenyesuaian);
        $row++;

        if ($jenisPenyesuaian !== 'tidak_ada' && $nominalPenyesuaian > 0) {
            $labelPenyesuaian = $jenisPenyesuaian === 'tambah'
                ? 'Penyesuaian Tambah'
                : 'Penyesuaian Kurang';

            $nilaiPenyesuaianExcel = $jenisPenyesuaian === 'kurang'
                ? -$nominalPenyesuaian
                : $nominalPenyesuaian;

            $sheet->setCellValue('E' . $row, $labelPenyesuaian);
            $sheet->setCellValue('F' . $row, $nilaiPenyesuaianExcel);
            $row++;

            if ($penjualan->keterangan_penyesuaian_total) {
                $sheet->setCellValue('E' . $row, 'Ket. Penyesuaian');
                $sheet->setCellValue('F' . $row, $penjualan->keterangan_penyesuaian_total);
                $row++;
            }
        }

        $sheet->setCellValue('E' . $row, 'Total Akhir');
        $sheet->setCellValue('F' . $row, (float) $penjualan->total_akhir);
        $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);

        $sheet->getStyle('E1:F' . $row)
            ->getNumberFormat()
            ->setFormatCode('"Rp" #,##0');

        $sheet->getStyle('A1:G' . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:G' . $row)->getAlignment()->setWrapText(true);

        $safeInvoice = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $nomorInvoiceTampil ?? 'nota');
        $safeInvoice = trim(preg_replace('/-+/', '-', $safeInvoice), '-');
        $fileName = 'Invoice-' . ($safeInvoice ?: 'nota') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }


    private function rulesPenjualan(Request $request, ?Penjualan $penjualan): array
    {
        $uniqueInvoice = Rule::unique('penjualan', 'nomor_invoice');
        if ($penjualan) {
            $uniqueInvoice->ignore($penjualan->id_penjualan, 'id_penjualan');
        }

        return [
            'nomor_invoice' => ['required', 'string', 'max:100', $uniqueInvoice],
            'tanggal_penjualan' => 'required|date',
            'id_customer' => 'required|exists:customers,id_customer',
            'mode_ppn' => 'required|in:tanpa_ppn,include,exclude',
            'jenis_penyesuaian_total' => 'nullable|in:tidak_ada,tambah,kurang',
            'nominal_penyesuaian_total' => 'nullable|numeric|min:0',
            'keterangan_penyesuaian_total' => 'nullable|string',
            'butuh_faktur_pajak' => 'nullable|boolean',
            'nomor_faktur_pajak' => 'nullable|string|max:100',
            'tanggal_faktur_pajak' => 'nullable|date',
            'nama_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:255',
            'npwp_faktur_pajak' => 'nullable|required_if:butuh_faktur_pajak,1|string|max:50',
            'alamat_faktur_pajak' => 'nullable|string',
            'metode_pembayaran' => 'required|in:tunai,kredit',
            'tanggal_jatuh_tempo' => 'nullable|required_if:metode_pembayaran,kredit|date',
            'catatan' => 'nullable|string',
            'id_barang' => 'required|array|min:1',
            'id_barang.*' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|array|min:1',
            'jumlah.*' => 'required|integer|min:1',
            'harga_jual' => 'required|array|min:1',
            'harga_jual.*' => 'required|numeric|min:0',
        ];
    }

    private function hitungSubtotalDetail(Barang $barang, int $jumlah, float $hargaJual): float
    {
        if (($barang->tipe_perhitungan_harga ?? 'normal') === 'isi_kemasan') {
            return round($jumlah * (float) ($barang->isi_per_satuan ?? 1) * $hargaJual, 2);
        }

        return round($jumlah * $hargaJual, 2);
    }

    private function hitungSubtotalPenjualanDariRequest(Request $request, bool $cekStok = true): array
    {
        $subtotalPenjualan = 0;
        $subtotalKenaPpn = 0;

        foreach ($request->id_barang as $index => $idBarang) {
            $barang = Barang::where('id_barang', $idBarang)->lockForUpdate()->firstOrFail();
            $jumlah = (int) $request->jumlah[$index];

            if ($cekStok && $jumlah > $barang->stok_saat_ini) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                ]);
            }

            $subtotalDetail = $this->hitungSubtotalDetail($barang, $jumlah, (float) $request->harga_jual[$index]);
            $subtotalPenjualan += $subtotalDetail;

            if ((bool) ($barang->kena_ppn ?? true)) {
                $subtotalKenaPpn += $subtotalDetail;
            }
        }

        return [
            'subtotal_penjualan' => round($subtotalPenjualan, 2),
            'subtotal_kena_ppn' => round($subtotalKenaPpn, 2),
            'subtotal_non_ppn' => round(max($subtotalPenjualan - $subtotalKenaPpn, 0), 2),
        ];
    }

    private function hitungPpnDetail(float $subtotalDetail, bool $kenaPpn, string $modePpn): array
    {
        $tarifPpn = 11.0;
        $subtotal = round(max($subtotalDetail, 0), 2);

        if (!$kenaPpn || $modePpn === 'tanpa_ppn') {
            return ['dpp_ppn' => 0, 'nilai_ppn' => 0];
        }

        if ($modePpn === 'exclude') {
            return ['dpp_ppn' => $subtotal, 'nilai_ppn' => round($subtotal * ($tarifPpn / 100), 2)];
        }

        $dppPpn = round($subtotal * 100 / 111, 2);
        return ['dpp_ppn' => $dppPpn, 'nilai_ppn' => round($subtotal - $dppPpn, 2)];
    }

    private function simpanDetailPenjualanDariRequest(Penjualan $penjualan, Request $request, bool $affectStock, string $keteranganRiwayat): void
    {
        $modePpn = $this->normalisasiModePpn($request->mode_ppn ?? null);

        foreach ($request->id_barang as $index => $idBarang) {
            $barang = Barang::where('id_barang', $idBarang)->lockForUpdate()->firstOrFail();
            $jumlah = (int) $request->jumlah[$index];
            $hargaJual = (float) $request->harga_jual[$index];

            if ($affectStock && $jumlah > $barang->stok_saat_ini) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok barang ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok_saat_ini . ' ' . $barang->satuan,
                ]);
            }

            $tipePerhitunganHarga = $barang->tipe_perhitungan_harga ?? 'normal';
            $satuanTransaksi = $barang->satuan;
            $satuanHitungHarga = $tipePerhitunganHarga === 'isi_kemasan' ? $barang->satuan_hitung_harga : $barang->satuan;
            $isiPerSatuan = $tipePerhitunganHarga === 'isi_kemasan' ? (float) $barang->isi_per_satuan : 1;
            $subtotalDetail = $this->hitungSubtotalDetail($barang, $jumlah, $hargaJual);
            $kenaPpn = (bool) ($barang->kena_ppn ?? true);
            $ppnDetail = $this->hitungPpnDetail($subtotalDetail, $kenaPpn, $modePpn);

            DetailPenjualan::create([
                'id_penjualan' => $penjualan->id_penjualan,
                'id_barang' => $barang->id_barang,
                'jumlah' => $jumlah,
                'harga_jual' => $hargaJual,
                'tipe_perhitungan_harga' => $tipePerhitunganHarga,
                'satuan_transaksi' => $satuanTransaksi,
                'satuan_hitung_harga' => $satuanHitungHarga,
                'isi_per_satuan' => $isiPerSatuan,
                'kena_ppn' => $kenaPpn,
                'dpp_ppn' => $ppnDetail['dpp_ppn'],
                'nilai_ppn' => $ppnDetail['nilai_ppn'],
                'subtotal' => $subtotalDetail,
            ]);

            if ($affectStock) {
                $stokSebelum = $barang->stok_saat_ini;
                $stokSesudah = $stokSebelum - $jumlah;

                $barang->update(['stok_saat_ini' => $stokSesudah]);

                RiwayatStok::create([
                    'id_barang' => $barang->id_barang,
                    'tanggal' => $request->tanggal_penjualan,
                    'jenis_pergerakan' => 'keluar',
                    'jumlah' => $jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'sumber_transaksi' => $penjualan->nomor_invoice,
                    'keterangan' => $keteranganRiwayat,
                    'dibuat_oleh' => Auth::id(),
                    'created_at' => now(),
                ]);
            }
        }
    }

    private function hitungPpnPenjualan(float $subtotalPenjualan, float $subtotalKenaPpn, string $modePpn): array
    {
        $tarifPpn = 11.0;
        $subtotal = round(max($subtotalPenjualan, 0), 2);
        $subtotalPpnable = round(max($subtotalKenaPpn, 0), 2);
        $subtotalNonPpn = round(max($subtotal - $subtotalPpnable, 0), 2);

        if (!in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
            $modePpn = 'include';
        }

        if ($modePpn === 'tanpa_ppn') {
            return [
                'mode_ppn' => 'tanpa_ppn',
                'subtotal_kena_ppn' => $subtotalPpnable,
                'subtotal_non_ppn' => $subtotalNonPpn,
                'persentase_pajak' => 0,
                'dpp_ppn' => 0,
                'nilai_pajak' => 0,
                'pajak_ditambahkan' => false,
                'total_akhir' => $subtotal,
            ];
        }

        if ($modePpn === 'exclude') {
            $nilaiPpn = round($subtotalPpnable * ($tarifPpn / 100), 2);
            return [
                'mode_ppn' => 'exclude',
                'subtotal_kena_ppn' => $subtotalPpnable,
                'subtotal_non_ppn' => $subtotalNonPpn,
                'persentase_pajak' => $tarifPpn,
                'dpp_ppn' => $subtotalPpnable,
                'nilai_pajak' => $nilaiPpn,
                'pajak_ditambahkan' => true,
                'total_akhir' => round($subtotal + $nilaiPpn, 2),
            ];
        }

        $dppPpn = round($subtotalPpnable * 100 / 111, 2);
        $nilaiPpn = round($subtotalPpnable - $dppPpn, 2);

        return [
            'mode_ppn' => 'include',
            'subtotal_kena_ppn' => $subtotalPpnable,
            'subtotal_non_ppn' => $subtotalNonPpn,
            'persentase_pajak' => $tarifPpn,
            'dpp_ppn' => $dppPpn,
            'nilai_pajak' => $nilaiPpn,
            'pajak_ditambahkan' => false,
            'total_akhir' => $subtotal,
        ];
    }

    private function hitungTotalDenganPenyesuaian(float $totalSebelumPenyesuaian, ?string $jenisPenyesuaian, $nominalPenyesuaian, ?string $keteranganPenyesuaian = null): array
    {
        $totalSebelum = round(max($totalSebelumPenyesuaian, 0), 2);
        $jenis = $jenisPenyesuaian ?: 'tidak_ada';
        if (!in_array($jenis, ['tidak_ada', 'tambah', 'kurang'], true)) {
            $jenis = 'tidak_ada';
        }

        $nominal = round(max((float) ($nominalPenyesuaian ?? 0), 0), 2);
        if ($jenis === 'tidak_ada') {
            $nominal = 0;
        }

        if ($jenis === 'kurang' && $nominal > $totalSebelum) {
            throw ValidationException::withMessages([
                'nominal_penyesuaian_total' => 'Nominal pengurangan total akhir tidak boleh lebih besar dari total sebelum penyesuaian.',
            ]);
        }

        $totalAkhir = $totalSebelum;
        if ($jenis === 'tambah') {
            $totalAkhir = $totalSebelum + $nominal;
        } elseif ($jenis === 'kurang') {
            $totalAkhir = $totalSebelum - $nominal;
        }

        return [
            'total_sebelum_penyesuaian' => $totalSebelum,
            'jenis_penyesuaian_total' => $jenis,
            'nominal_penyesuaian_total' => $nominal,
            'keterangan_penyesuaian_total' => $nominal > 0 ? $keteranganPenyesuaian : null,
            'total_akhir' => round(max($totalAkhir, 0), 2),
        ];
    }

    private function ambilDataFakturPajak(Request $request): array
    {
        $butuhFakturPajak = $request->boolean('butuh_faktur_pajak');

        return [
            'butuh_faktur_pajak' => $butuhFakturPajak,
            'nomor_faktur_pajak' => $butuhFakturPajak ? $request->nomor_faktur_pajak : null,
            'tanggal_faktur_pajak' => $butuhFakturPajak ? $request->tanggal_faktur_pajak : null,
            'nama_faktur_pajak' => $butuhFakturPajak ? $request->nama_faktur_pajak : null,
            'npwp_faktur_pajak' => $butuhFakturPajak ? $request->npwp_faktur_pajak : null,
            'alamat_faktur_pajak' => $butuhFakturPajak ? $request->alamat_faktur_pajak : null,
        ];
    }

    private function hitungStatusPembayaran(string $metodePembayaran, float $totalAkhir, float $totalDibayar): string
    {
        if ($metodePembayaran === 'tunai') {
            return 'lunas';
        }
        if ($totalDibayar >= $totalAkhir && $totalAkhir > 0) {
            return 'lunas';
        }
        if ($totalDibayar > 0) {
            return 'sebagian';
        }
        return 'belum_lunas';
    }

    private function sinkronkanPiutangSetelahEdit(Penjualan $penjualan, Request $request, float $totalAkhir, float $totalDibayarLama): void
    {
        $penjualan->load('piutang');

        if ($request->metode_pembayaran === 'tunai') {
            if ($penjualan->piutang && $totalDibayarLama <= 0) {
                $penjualan->piutang->delete();
            }
            return;
        }

        $sisaPiutang = max($totalAkhir - $totalDibayarLama, 0);
        $statusPiutang = $this->hitungStatusPiutang($sisaPiutang, $totalDibayarLama, $request->tanggal_jatuh_tempo);

        if ($penjualan->piutang) {
            $penjualan->piutang->update([
                'nomor_invoice' => $penjualan->nomor_invoice,
                'id_customer' => $penjualan->id_customer,
                'total_piutang' => $totalAkhir,
                'total_dibayar' => $totalDibayarLama,
                'sisa_piutang' => $sisaPiutang,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'status_piutang' => $statusPiutang,
                'catatan' => 'Piutang diperbarui dari edit transaksi penjualan',
            ]);
            return;
        }

        Piutang::create([
            'id_penjualan' => $penjualan->id_penjualan,
            'nomor_invoice' => $penjualan->nomor_invoice,
            'id_customer' => $penjualan->id_customer,
            'total_piutang' => $totalAkhir,
            'total_dibayar' => 0,
            'sisa_piutang' => $totalAkhir,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'status_piutang' => 'belum_lunas',
            'catatan' => 'Piutang otomatis dari edit transaksi penjualan kredit',
        ]);
    }

    private function hitungStatusPiutang(float $sisaPiutang, float $totalDibayar, ?string $tanggalJatuhTempo): string
    {
        if ($sisaPiutang <= 0) {
            return 'lunas';
        }
        if ($tanggalJatuhTempo && now()->toDateString() > $tanggalJatuhTempo) {
            return 'jatuh_tempo';
        }
        if ($totalDibayar > 0) {
            return 'sebagian_dibayar';
        }
        return 'belum_lunas';
    }

    private function normalisasiModePpn(?string $modePpn, ?Penjualan $penjualan = null): string
    {
        if (in_array($modePpn, ['tanpa_ppn', 'include', 'exclude'], true)) {
            return $modePpn;
        }
        if (!$penjualan) {
            return 'include';
        }
        if ((float) ($penjualan->persentase_pajak ?? 0) <= 0) {
            return 'tanpa_ppn';
        }
        return ($penjualan->pajak_ditambahkan ?? false) ? 'exclude' : 'include';
    }

    private function labelModePpn(string $modePpn): string
    {
        return [
            'tanpa_ppn' => 'Tidak Pakai PPN',
            'include' => 'Harga Sudah Termasuk PPN',
            'exclude' => 'Harga Belum Termasuk PPN',
        ][$modePpn] ?? 'Harga Sudah Termasuk PPN';
    }
}
