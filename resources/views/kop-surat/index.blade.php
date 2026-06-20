<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kop Surat & Surat Keluar
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Buat surat resmi perusahaan dan unduh kop surat atau surat lengkap dalam format Word.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kop-surat.downloadKopKosong') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-center">
                    Download Kop Word
                </a>

                <a href="{{ route('kop-surat.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-center">
                    Buat Surat
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Surat</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSurat ?? 0, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Surat Final</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($totalFinal ?? 0, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Draft</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ number_format($totalDraft ?? 0, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <p class="text-sm text-gray-500">Jenis Surat</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($totalJenisSurat ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('kop-surat.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block mb-1 font-medium">Tanggal Awal</label>
                            <input type="date"
                                name="tanggal_awal"
                                value="{{ request('tanggal_awal') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Tanggal Akhir</label>
                            <input type="date"
                                name="tanggal_akhir"
                                value="{{ request('tanggal_akhir') }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Status</label>
                            <select name="status_surat" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                <option value="final" {{ request('status_surat') === 'final' ? 'selected' : '' }}>Final</option>
                                <option value="draft" {{ request('status_surat') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 font-medium">Jenis Surat</label>
                            <select name="jenis_surat" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Semua</option>
                                @foreach ($jenisSuratOptions as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis_surat') === $jenis ? 'selected' : '' }}>
                                        {{ $jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block mb-1 font-medium">Cari</label>
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nomor/perihal/tujuan/isi surat..."
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('kop-surat.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Reset
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2 text-left">No</th>
                                <th class="border px-3 py-2 text-left">Tanggal</th>
                                <th class="border px-3 py-2 text-left">Nomor Surat</th>
                                <th class="border px-3 py-2 text-left">Jenis</th>
                                <th class="border px-3 py-2 text-left">Tujuan</th>
                                <th class="border px-3 py-2 text-left">Perihal</th>
                                <th class="border px-3 py-2 text-center">Status</th>
                                <th class="border px-3 py-2 text-left">Dibuat Oleh</th>
                                <th class="border px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($suratKeluar as $surat)
                                <tr>
                                    <td class="border px-3 py-2">
                                        {{ $suratKeluar->firstItem() + $loop->index }}
                                    </td>

                                    <td class="border px-3 py-2 whitespace-nowrap">
                                        {{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d-m-Y') : '-' }}
                                    </td>

                                    <td class="border px-3 py-2 font-semibold whitespace-nowrap">
                                        {{ $surat->nomor_surat }}
                                    </td>

                                    <td class="border px-3 py-2">
                                        {{ $surat->jenis_surat }}
                                    </td>

                                    <td class="border px-3 py-2 min-w-[180px]">
                                        {{ $surat->tujuan }}
                                    </td>

                                    <td class="border px-3 py-2 min-w-[220px]">
                                        {{ $surat->perihal }}
                                    </td>

                                    <td class="border px-3 py-2 text-center">
                                        @if ($surat->status_surat === 'final')
                                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Final</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Draft</span>
                                        @endif
                                    </td>

                                    <td class="border px-3 py-2">
                                        {{ $surat->user->nama_user ?? '-' }}
                                    </td>

                                    <td class="border px-3 py-2">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('kop-surat.show', $surat) }}"
                                                class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                                Detail
                                            </a>

                                            <a href="{{ route('kop-surat.edit', $surat) }}"
                                                class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                                                Edit
                                            </a>

                                            <a href="{{ route('kop-surat.downloadWord', $surat) }}"
                                                class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                                                Word
                                            </a>

                                            <form method="POST"
                                                action="{{ route('kop-surat.destroy', $surat) }}"
                                                onsubmit="return confirm('Yakin ingin menghapus surat ini?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="border px-3 py-6 text-center text-gray-500">
                                        Belum ada surat yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $suratKeluar->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
