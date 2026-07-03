<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class KopSuratController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratKeluar::with('user')
            ->when($request->tanggal_awal, function ($query) use ($request) {
                $query->whereDate('tanggal_surat', '>=', $request->tanggal_awal);
            })
            ->when($request->tanggal_akhir, function ($query) use ($request) {
                $query->whereDate('tanggal_surat', '<=', $request->tanggal_akhir);
            })
            ->when($request->status_surat, function ($query) use ($request) {
                $query->where('status_surat', $request->status_surat);
            })
            ->when($request->jenis_surat, function ($query) use ($request) {
                $query->where('jenis_surat', $request->jenis_surat);
            })
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('jenis_surat', 'like', "%{$search}%")
                        ->orWhere('tujuan', 'like', "%{$search}%")
                        ->orWhere('alamat_tujuan', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%")
                        ->orWhere('isi_surat', 'like', "%{$search}%")
                        ->orWhere('nama_penandatangan', 'like', "%{$search}%")
                        ->orWhere('jabatan_penandatangan', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('nama_user', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            });

        $suratUntukRingkasan = (clone $query)->get();

        $totalSurat = $suratUntukRingkasan->count();
        $totalDraft = $suratUntukRingkasan->where('status_surat', 'draft')->count();
        $totalFinal = $suratUntukRingkasan->where('status_surat', 'final')->count();
        $totalJenisSurat = $suratUntukRingkasan->pluck('jenis_surat')->filter()->unique()->count();

        $jenisSuratOptions = SuratKeluar::query()
            ->select('jenis_surat')
            ->whereNotNull('jenis_surat')
            ->distinct()
            ->orderBy('jenis_surat')
            ->pluck('jenis_surat');

        $suratKeluar = $query
            ->orderBy('tanggal_surat', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('kop-surat.index', compact(
            'suratKeluar',
            'jenisSuratOptions',
            'totalSurat',
            'totalDraft',
            'totalFinal',
            'totalJenisSurat'
        ));
    }

    public function create()
    {
        $surat = new SuratKeluar([
            'nomor_surat' => $this->buatNomorSuratOtomatis(),
            'tanggal_surat' => now()->toDateString(),
            'jenis_surat' => 'Surat Umum',
            'lampiran' => '-',
            'pembuka' => 'Dengan hormat,',
            'penutup' => 'Demikian surat ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.',
            'kota_ttd' => 'Jakarta',
            'nama_penandatangan' => 'Admin',
            'jabatan_penandatangan' => 'Administrasi',
            'status_surat' => 'final',
        ]);

        return view('kop-surat.create', compact('surat'));
    }

    public function store(Request $request)
    {
        $data = $this->validasiSurat($request);
        $data['dibuat_oleh'] = Auth::id();

        SuratKeluar::create($data);

        return redirect()
            ->route('kop-surat.index')
            ->with('success', 'Surat berhasil dibuat dan sudah bisa diunduh dalam format Word.');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load('user');

        return view('kop-surat.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        return view('kop-surat.edit', [
            'surat' => $suratKeluar,
        ]);
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        $data = $this->validasiSurat($request, $suratKeluar->id_surat);

        $suratKeluar->update($data);

        return redirect()
            ->route('kop-surat.show', $suratKeluar)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        $suratKeluar->delete();

        return redirect()
            ->route('kop-surat.index')
            ->with('success', 'Surat berhasil dihapus dari daftar kop surat.');
    }

    public function downloadKopKosong()
    {
        $filePath = $this->buatFileWord(null, true);
        $fileName = 'Kop-Surat-Berkat-Jaya-Nusantara-' . time() . '.docx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function downloadWord(SuratKeluar $suratKeluar)
    {
        $filePath = $this->buatFileWord($suratKeluar, false);
        $nomorBersih = Str::slug($suratKeluar->nomor_surat ?: 'surat', '-');
        $perihalBersih = Str::slug($suratKeluar->perihal ?: 'kop-surat', '-');
        $fileName = 'Surat-' . $nomorBersih . '-' . $perihalBersih . '-' . time() . '.docx';

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    private function validasiSurat(Request $request, ?int $idSurat = null): array
    {
        $uniqueNomor = 'unique:surat_keluar,nomor_surat';

        if ($idSurat) {
            $uniqueNomor .= ',' . $idSurat . ',id_surat';
        }

        return $request->validate([
            'nomor_surat' => ['required', 'string', 'max:100', $uniqueNomor],
            'tanggal_surat' => ['required', 'date'],
            'jenis_surat' => ['required', 'string', 'max:100'],
            'tujuan' => ['required', 'string', 'max:255'],
            'alamat_tujuan' => ['nullable', 'string'],
            'perihal' => ['required', 'string', 'max:255'],
            'lampiran' => ['nullable', 'string', 'max:100'],
            'pembuka' => ['nullable', 'string'],
            'isi_surat' => ['required', 'string'],
            'penutup' => ['nullable', 'string'],
            'kota_ttd' => ['required', 'string', 'max:100'],
            'nama_penandatangan' => ['required', 'string', 'max:150'],
            'jabatan_penandatangan' => ['nullable', 'string', 'max:150'],
            'status_surat' => ['required', 'in:draft,final'],
            'catatan_internal' => ['nullable', 'string'],
        ], [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.unique' => 'Nomor surat sudah digunakan.',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'jenis_surat.required' => 'Jenis surat wajib diisi.',
            'tujuan.required' => 'Tujuan surat wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'isi_surat.required' => 'Isi surat wajib diisi.',
            'kota_ttd.required' => 'Kota tanda tangan wajib diisi.',
            'nama_penandatangan.required' => 'Nama penandatangan wajib diisi.',
            'status_surat.required' => 'Status surat wajib dipilih.',
        ]);
    }

    private function buatNomorSuratOtomatis(): string
    {
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $tahun = now()->format('Y');
        $bulan = $bulanRomawi[(int) now()->format('n')];
        $jumlahSuratTahunIni = SuratKeluar::whereYear('tanggal_surat', $tahun)->count() + 1;
        $nomorUrut = str_pad((string) $jumlahSuratTahunIni, 3, '0', STR_PAD_LEFT);

        return $nomorUrut . '/BJN/SRT/' . $bulan . '/' . $tahun;
    }

    private function buatFileWord(?SuratKeluar $surat, bool $kopKosong = false): string
    {
        $folder = storage_path('app/temp');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $filePath = $folder . '/' . Str::uuid() . '.docx';
        $logoPath = public_path('assets/img/logo-bjn.png');
        $adaLogo = File::exists($logoPath);

        $zip = new ZipArchive();
        $zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml($adaLogo));
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->documentRelsXml($adaLogo));
        $zip->addFromString('word/styles.xml', $this->stylesXml());
        $zip->addFromString('word/document.xml', $this->documentXml($surat, $kopKosong, $adaLogo));

        if ($adaLogo) {
            $zip->addFile($logoPath, 'word/media/logo-bjn.png');
        }

        $zip->close();

        return $filePath;
    }

    private function contentTypesXml(bool $adaLogo): string
    {
        $pngContentType = $adaLogo ? '<Default Extension="png" ContentType="image/png"/>' : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    ' . $pngContentType . '
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
    <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>';
    }

    private function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';
    }

    private function documentRelsXml(bool $adaLogo): string
    {
        $logoRel = $adaLogo
            ? '<Relationship Id="rIdLogo" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/logo-bjn.png"/>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
    ' . $logoRel . '
</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
        <w:name w:val="Normal"/>
        <w:qFormat/>
        <w:rPr>
            <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>
            <w:sz w:val="24"/>
        </w:rPr>
    </w:style>
</w:styles>';
    }

    private function documentXml(?SuratKeluar $surat, bool $kopKosong, bool $adaLogo): string
    {
        $isi = $this->kopSuratXml($adaLogo);
        $isi .= $this->garisPembatasXml();
        $isi .= $this->spasiXml(1);

        if ($kopKosong || !$surat) {
            $isi .= $this->paragraphXml('', 'left');
            $isi .= $this->spasiXml(18);
        } else {
            $isi .= $this->isiSuratXml($surat);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document
    xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
    xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
    xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
    xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
    xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
    <w:body>
        ' . $isi . '
        <w:sectPr>
            <w:pgSz w:w="11906" w:h="16838"/>
            <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>
        </w:sectPr>
    </w:body>
</w:document>';
    }

    private function kopSuratXml(bool $adaLogo): string
    {
        $logoContent = $adaLogo
            ? $this->imageXml()
            : $this->runTextXml('BJN', true, 32);

        return '
<w:tbl>
    <w:tblPr>
        <w:tblW w:w="9638" w:type="dxa"/>
        <w:tblLayout w:type="fixed"/>
        <w:tblCellMar>
            <w:top w:w="0" w:type="dxa"/>
            <w:left w:w="0" w:type="dxa"/>
            <w:bottom w:w="0" w:type="dxa"/>
            <w:right w:w="0" w:type="dxa"/>
        </w:tblCellMar>
        <w:tblBorders>
            <w:top w:val="nil"/>
            <w:left w:val="nil"/>
            <w:bottom w:val="nil"/>
            <w:right w:val="nil"/>
            <w:insideH w:val="nil"/>
            <w:insideV w:val="nil"/>
        </w:tblBorders>
    </w:tblPr>
    <w:tblGrid>
        <w:gridCol w:w="1600"/>
        <w:gridCol w:w="6438"/>
        <w:gridCol w:w="1600"/>
    </w:tblGrid>
    <w:tr>
        <w:trPr>
            <w:trHeight w:val="1350" w:hRule="atLeast"/>
        </w:trPr>
        <w:tc>
            <w:tcPr>
                <w:tcW w:w="1600" w:type="dxa"/>
                <w:vAlign w:val="center"/>
            </w:tcPr>
            <w:p>
                <w:pPr>
                    <w:jc w:val="center"/>
                    <w:spacing w:before="0" w:after="0"/>
                </w:pPr>
                ' . $logoContent . '
            </w:p>
        </w:tc>
        <w:tc>
            <w:tcPr>
                <w:tcW w:w="6438" w:type="dxa"/>
                <w:vAlign w:val="center"/>
            </w:tcPr>
            ' . $this->paragraphKopXml('CV. BERKAT JAYA NUSANTARA', true, 30, 35) . '
            ' . $this->paragraphKopXml('Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460', false, 20, 20) . '
            ' . $this->paragraphKopXml('Telp: (021) 5664892, 5676277', false, 20, 0) . '
        </w:tc>
        <w:tc>
            <w:tcPr>
                <w:tcW w:w="1600" w:type="dxa"/>
                <w:vAlign w:val="center"/>
            </w:tcPr>
            <w:p>
                <w:pPr>
                    <w:jc w:val="center"/>
                    <w:spacing w:before="0" w:after="0"/>
                </w:pPr>
            </w:p>
        </w:tc>
    </w:tr>
</w:tbl>';
    }

    private function paragraphKopXml(string $text, bool $bold = false, int $size = 22, int $after = 20): string
    {
        return '
<w:p>
    <w:pPr>
        <w:jc w:val="center"/>
        <w:spacing w:before="0" w:after="' . $after . '"/>
    </w:pPr>
    ' . $this->runTextXml($text, $bold, $size) . '
</w:p>';
    }


    private function imageXml(): string
    {
        return '
<w:r>
    <w:drawing>
        <wp:inline distT="0" distB="0" distL="0" distR="0">
            <wp:extent cx="950000" cy="950000"/>
            <wp:effectExtent l="0" t="0" r="0" b="0"/>
            <wp:docPr id="1" name="Logo BJN"/>
            <wp:cNvGraphicFramePr>
                <a:graphicFrameLocks noChangeAspect="1"/>
            </wp:cNvGraphicFramePr>
            <a:graphic>
                <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
                    <pic:pic>
                        <pic:nvPicPr>
                            <pic:cNvPr id="0" name="logo-bjn.png"/>
                            <pic:cNvPicPr/>
                        </pic:nvPicPr>
                        <pic:blipFill>
                            <a:blip r:embed="rIdLogo"/>
                            <a:stretch><a:fillRect/></a:stretch>
                        </pic:blipFill>
                        <pic:spPr>
                            <a:xfrm>
                                <a:off x="0" y="0"/>
                                <a:ext cx="950000" cy="950000"/>
                            </a:xfrm>
                            <a:prstGeom prst="rect"><a:avLst/></a:prstGeom>
                        </pic:spPr>
                    </pic:pic>
                </a:graphicData>
            </a:graphic>
        </wp:inline>
    </w:drawing>
</w:r>';
    }


    private function garisPembatasXml(): string
    {
        return '
<w:p>
    <w:pPr>
        <w:spacing w:before="120" w:after="0"/>
        <w:pBdr>
            <w:bottom w:val="single" w:sz="18" w:space="1" w:color="000000"/>
        </w:pBdr>
    </w:pPr>
</w:p>';
    }


    private function isiSuratXml(SuratKeluar $surat): string
    {
        $tanggal = optional($surat->tanggal_surat)->translatedFormat('d F Y') ?: now()->translatedFormat('d F Y');
        $isi = '';

        $isi .= $this->paragraphXml(($surat->kota_ttd ?: 'Jakarta') . ', ' . $tanggal, 'right');
        $isi .= $this->spasiXml(1);
        $isi .= $this->paragraphXml('Nomor     : ' . ($surat->nomor_surat ?: '-'));
        $isi .= $this->paragraphXml('Lampiran  : ' . ($surat->lampiran ?: '-'));
        $isi .= $this->paragraphXml('Perihal   : ' . ($surat->perihal ?: '-'));
        $isi .= $this->spasiXml(1);
        $isi .= $this->paragraphXml('Kepada Yth.');
        $isi .= $this->paragraphXml($surat->tujuan ?: '-');

        if ($surat->alamat_tujuan) {
            foreach ($this->pecahParagraf($surat->alamat_tujuan) as $barisAlamat) {
                $isi .= $this->paragraphXml($barisAlamat);
            }
        }

        $isi .= $this->spasiXml(1);

        if ($surat->pembuka) {
            foreach ($this->pecahParagraf($surat->pembuka) as $paragrafPembuka) {
                $isi .= $this->paragraphXml($paragrafPembuka, 'both');
            }
        }

        foreach ($this->pecahParagraf($surat->isi_surat) as $paragraf) {
            $isi .= $this->paragraphXml($paragraf, 'both', false, 24, true);
        }

        if ($surat->penutup) {
            foreach ($this->pecahParagraf($surat->penutup) as $paragrafPenutup) {
                $isi .= $this->paragraphXml($paragrafPenutup, 'both');
            }
        }

        $isi .= $this->spasiXml(2);
        $isi .= $this->paragraphXml('Hormat kami,', 'right');
        $isi .= $this->paragraphXml('CV. BERKAT JAYA NUSANTARA', 'right', true);
        $isi .= $this->spasiXml(4);
        $isi .= $this->paragraphXml($surat->nama_penandatangan ?: '-', 'right', true);

        if ($surat->jabatan_penandatangan) {
            $isi .= $this->paragraphXml($surat->jabatan_penandatangan, 'right');
        }

        return $isi;
    }

    private function pecahParagraf(?string $text): array
    {
        $text = trim((string) $text);

        if ($text === '') {
            return [];
        }

        return collect(preg_split('/\R+/', $text))
            ->map(fn($item) => trim((string) $item))
            ->filter(fn($item) => $item !== '')
            ->values()
            ->all();
    }

    private function paragraphXml(string $text, string $align = 'left', bool $bold = false, int $size = 24, bool $firstLineIndent = false): string
    {
        $indentXml = $firstLineIndent ? '<w:ind w:firstLine="720"/>' : '';

        return '
<w:p>
    <w:pPr>
        <w:jc w:val="' . $align . '"/>
        ' . $indentXml . '
        <w:spacing w:after="100"/>
    </w:pPr>
    ' . $this->runTextXml($text, $bold, $size) . '
</w:p>';
    }

    private function runTextXml(string $text, bool $bold = false, int $size = 24): string
    {
        $boldXml = $bold ? '<w:b/>' : '';

        return '<w:r>
    <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>
        ' . $boldXml . '
        <w:sz w:val="' . $size . '"/>
    </w:rPr>
    <w:t xml:space="preserve">' . e($text) . '</w:t>
</w:r>';
    }

    private function spasiXml(int $jumlah = 1): string
    {
        return str_repeat('<w:p><w:r><w:t></w:t></w:r></w:p>', $jumlah);
    }
}
