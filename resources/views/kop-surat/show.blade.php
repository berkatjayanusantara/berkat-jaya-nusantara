<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Surat
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Preview surat resmi sebelum diunduh dalam format Word.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kop-surat.downloadWord', $suratKeluar) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-center">
                    Download Word
                </a>

                <a href="{{ route('kop-surat.edit', $suratKeluar) }}"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-center">
                    Edit
                </a>

                <a href="{{ route('kop-surat.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
    $tanggalSurat = $suratKeluar->tanggal_surat
    ? $suratKeluar->tanggal_surat->translatedFormat('d F Y')
    : now()->translatedFormat('d F Y');

    $logoPath = asset('assets/img/logo-bjn.png');

    $pecahBaris = function ($teks) {
    return preg_split('/\R+/', (string) $teks);
    };
    @endphp

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nomor Surat</p>
                        <p class="font-semibold">{{ $suratKeluar->nomor_surat }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Tanggal</p>
                        <p class="font-semibold">
                            {{ $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->format('d-m-Y') : '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-gray-500">Jenis Surat</p>
                        <p class="font-semibold">{{ $suratKeluar->jenis_surat }}</p>
                    </div>

                    <div>
                        <p class="text-gray-500">Status</p>
                        @if ($suratKeluar->status_surat === 'final')
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Final</span>
                        @else
                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Draft</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4 md:p-8 overflow-x-auto">
                <div class="mx-auto bg-white border border-gray-200 text-gray-900 shadow-sm"
                    style="width: 794px; min-height: 1123px; padding: 56px 56px 48px 56px; font-family: 'Times New Roman', Times, serif; font-size: 16px; line-height: 1.55;">

                    {{-- Header kop surat dibuat seimbang seperti file Word download: 3 kolom --}}
                    <div style="display: grid; grid-template-columns: 115px 1fr 115px; align-items: center; column-gap: 16px; padding-bottom: 22px; border-bottom: 4px solid #000; margin-bottom: 34px;">
                        <div style="height: 86px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ $logoPath }}"
                                alt="Logo BJN"
                                style="max-width: 74px; max-height: 74px; object-fit: contain; display: block;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">

                            <div style="display: none; font-size: 20px; font-weight: bold; color: #1f2937;">
                                BJN
                            </div>
                        </div>

                        <div style="text-align: center;">
                            <div style="font-size: 24px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; line-height: 1.2;">
                                CV. BERKAT JAYA NUSANTARA
                            </div>
                            <div style="font-size: 14px; margin-top: 6px; line-height: 1.35;">
                                Jl. Jelambar Utama 1 No. 6A RT. 007 RW. 004, Jakarta Barat 11460
                            </div>
                            <div style="font-size: 14px; margin-top: 3px; line-height: 1.35;">
                                Telp: (021) 5664892, 5676277
                            </div>
                        </div>

                        {{-- Kolom kosong untuk menyeimbangkan logo kiri, supaya teks benar-benar tengah --}}
                        <div style="height: 86px;"></div>
                    </div>

                    <div style="text-align: right; margin-bottom: 28px;">
                        {{ $suratKeluar->kota_ttd ?: 'Jakarta' }}, {{ $tanggalSurat }}
                    </div>

                    <div style="margin-bottom: 28px;">
                        <table style="border-collapse: collapse; width: 100%; border: none;">
                            <tr>
                                <td style="width: 90px; padding: 1px 0; border: none; vertical-align: top;">Nomor</td>
                                <td style="width: 14px; padding: 1px 0; border: none; vertical-align: top;">:</td>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">{{ $suratKeluar->nomor_surat }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">Lampiran</td>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">:</td>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">{{ $suratKeluar->lampiran ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">Perihal</td>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">:</td>
                                <td style="padding: 1px 0; border: none; vertical-align: top;">{{ $suratKeluar->perihal }}</td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 28px;">
                        <p style="margin: 0 0 4px 0;">Kepada Yth.</p>
                        <p style="margin: 0 0 4px 0;">{{ $suratKeluar->tujuan }}</p>

                        @if ($suratKeluar->alamat_tujuan)
                        @foreach ($pecahBaris($suratKeluar->alamat_tujuan) as $alamat)
                        @if (trim($alamat) !== '')
                        <p style="margin: 0 0 4px 0;">{{ $alamat }}</p>
                        @endif
                        @endforeach
                        @endif
                    </div>

                    <div style="text-align: justify; margin-bottom: 26px;">
                        @if ($suratKeluar->pembuka)
                        @foreach ($pecahBaris($suratKeluar->pembuka) as $paragraf)
                        @if (trim($paragraf) !== '')
                        <p style="margin: 0 0 14px 0;">{{ $paragraf }}</p>
                        @endif
                        @endforeach
                        @endif

                        @foreach ($pecahBaris($suratKeluar->isi_surat) as $paragraf)
                        @if (trim($paragraf) !== '')
                        <p style="margin: 0 0 14px 0; text-indent: 36px;">{{ $paragraf }}</p>
                        @endif
                        @endforeach

                        @if ($suratKeluar->penutup)
                        @foreach ($pecahBaris($suratKeluar->penutup) as $paragraf)
                        @if (trim($paragraf) !== '')
                        <p style="margin: 0 0 14px 0;">{{ $paragraf }}</p>
                        @endif
                        @endforeach
                        @endif
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 48px;">
                        <div style="width: 260px; text-align: center; line-height: 1.45;">
                            <p style="margin: 0 0 4px 0;">Hormat kami,</p>
                            <p style="margin: 0 0 72px 0; font-weight: 700;">CV. BERKAT JAYA NUSANTARA</p>
                            <p style="margin: 0; font-weight: 700; text-decoration: underline;">
                                {{ $suratKeluar->nama_penandatangan }}
                            </p>

                            @if ($suratKeluar->jabatan_penandatangan)
                            <p style="margin: 2px 0 0 0;">{{ $suratKeluar->jabatan_penandatangan }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($suratKeluar->catatan_internal)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 mt-6">
                <div class="font-semibold mb-1">Catatan Internal</div>
                <div class="text-sm whitespace-pre-line">{{ $suratKeluar->catatan_internal }}</div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>