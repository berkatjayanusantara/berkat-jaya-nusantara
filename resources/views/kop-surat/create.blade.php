<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Buat Surat Baru
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Form pembuatan surat resmi dengan kop surat CV. Berkat Jaya Nusantara.
                </p>
            </div>

            <a href="{{ route('kop-surat.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
                    <div class="font-semibold mb-1">Terdapat kesalahan:</div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('kop-surat.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Nomor Surat <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="nomor_surat"
                                value="{{ old('nomor_surat', $surat->nomor_surat) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Surat <span class="text-red-500">*</span></label>
                            <input type="date"
                                name="tanggal_surat"
                                value="{{ old('tanggal_surat', optional($surat->tanggal_surat)->format('Y-m-d') ?? $surat->tanggal_surat) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status <span class="text-red-500">*</span></label>
                            <select name="status_surat" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="final" {{ old('status_surat', $surat->status_surat) === 'final' ? 'selected' : '' }}>Final</option>
                                <option value="draft" {{ old('status_surat', $surat->status_surat) === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 font-medium">Jenis Surat <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="jenis_surat"
                                value="{{ old('jenis_surat', $surat->jenis_surat) }}"
                                placeholder="Contoh: Surat Umum / Penawaran / Pemberitahuan"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Perihal <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="perihal"
                                value="{{ old('perihal', $surat->perihal) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 font-medium">Tujuan Surat <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="tujuan"
                                value="{{ old('tujuan', $surat->tujuan) }}"
                                placeholder="Contoh: PT ABC / Bapak/Ibu ..."
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Lampiran</label>
                            <input type="text"
                                name="lampiran"
                                value="{{ old('lampiran', $surat->lampiran) }}"
                                placeholder="Contoh: - / 1 berkas"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Alamat Tujuan</label>
                        <textarea name="alamat_tujuan"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Alamat penerima surat, opsional.">{{ old('alamat_tujuan', $surat->alamat_tujuan) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Pembuka</label>
                        <textarea name="pembuka"
                            rows="2"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('pembuka', $surat->pembuka) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Isi Surat <span class="text-red-500">*</span></label>
                        <textarea name="isi_surat"
                            rows="8"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required
                            placeholder="Tulis isi surat di sini. Setiap baris akan menjadi paragraf di Word.">{{ old('isi_surat', $surat->isi_surat) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Penutup</label>
                        <textarea name="penutup"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('penutup', $surat->penutup) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 font-medium">Kota Tanda Tangan <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="kota_ttd"
                                value="{{ old('kota_ttd', $surat->kota_ttd) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Nama Penandatangan <span class="text-red-500">*</span></label>
                            <input type="text"
                                name="nama_penandatangan"
                                value="{{ old('nama_penandatangan', $surat->nama_penandatangan) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jabatan</label>
                            <input type="text"
                                name="jabatan_penandatangan"
                                value="{{ old('jabatan_penandatangan', $surat->jabatan_penandatangan) }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-1 font-medium">Catatan Internal</label>
                        <textarea name="catatan_internal"
                            rows="2"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            placeholder="Catatan ini hanya tampil di sistem, tidak masuk file Word.">{{ old('catatan_internal', $surat->catatan_internal) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <a href="{{ route('kop-surat.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Simpan Surat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
