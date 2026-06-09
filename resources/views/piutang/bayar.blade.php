<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pembayaran Piutang
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="bg-gray-50 border rounded-md p-4 mb-6">
                    <h3 class="font-semibold text-lg mb-3">Informasi Piutang</h3>

                    <table class="w-full">
                        <tr>
                            <td class="py-1 font-medium">Nomor Invoice</td>
                            <td class="py-1">: {{ $piutang->nomor_invoice }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Customer</td>
                            <td class="py-1">: {{ $piutang->customer->nama_customer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Total Piutang</td>
                            <td class="py-1">: Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Total Dibayar</td>
                            <td class="py-1">: Rp {{ number_format($piutang->total_dibayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Sisa Piutang</td>
                            <td class="py-1 font-semibold">
                                : Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </div>

                <form action="{{ route('piutang.simpanPembayaran', $piutang->id_piutang) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Tanggal Pembayaran</label>
                        <input type="date"
                            name="tanggal_pembayaran"
                            value="{{ old('tanggal_pembayaran', date('Y-m-d')) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Nominal Pembayaran</label>
                        <input type="number"
                            name="nominal_pembayaran"
                            value="{{ old('nominal_pembayaran') }}"
                            min="1"
                            max="{{ $piutang->sisa_piutang }}"
                            step="0.01"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>

                        <p class="text-sm text-gray-500 mt-1">
                            Maksimal pembayaran: Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Metode Pembayaran</label>
                        <select name="metode_pembayaran"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                            <option value="tunai" {{ old('metode_pembayaran') === 'tunai' ? 'selected' : '' }}>
                                Tunai
                            </option>
                            <option value="transfer" {{ old('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>
                                Transfer
                            </option>
                            <option value="giro" {{ old('metode_pembayaran') === 'giro' ? 'selected' : '' }}>
                                Giro
                            </option>
                            <option value="lainnya" {{ old('metode_pembayaran') === 'lainnya' ? 'selected' : '' }}>
                                Lainnya
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Catatan</label>
                        <textarea name="catatan"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('piutang.show', $piutang->id_piutang) }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            onclick="return confirm('Simpan pembayaran piutang ini?')">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>