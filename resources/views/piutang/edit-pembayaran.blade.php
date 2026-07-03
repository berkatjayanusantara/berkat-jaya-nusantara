<x-app-layout>
    @php
    $backUrl = $backUrl ?? request('back_url', route('piutang.index'));

    $detailUrl = route('piutang.show', [
    'piutang' => $piutang->id_piutang,
    'back_url' => $backUrl,
    ]);
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Pembayaran Piutang
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
                            <td class="py-1 font-medium">Nomor Telepon</td>
                            <td class="py-1">: {{ $piutang->customer->nomor_telepon ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Total Piutang</td>
                            <td class="py-1">
                                : Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Total Dibayar</td>
                            <td class="py-1">
                                : Rp {{ number_format($piutang->total_dibayar, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Sisa Piutang</td>
                            <td class="py-1 font-semibold text-red-700">
                                : Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium">Jatuh Tempo</td>
                            <td class="py-1">
                                : {{ $piutang->tanggal_jatuh_tempo ? $piutang->tanggal_jatuh_tempo->format('d-m-Y') : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>

                <form id="formBayarPiutang" action="{{ route('piutang.updatePembayaran', [$piutang->id_piutang, $pembayaranPiutang->id_pembayaran]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="back_url" value="{{ $backUrl }}">

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Tanggal Pembayaran</label>
                        <input type="date"
                            name="tanggal_pembayaran"
                            value="{{ old('tanggal_pembayaran', $pembayaranPiutang->tanggal_pembayaran instanceof \Carbon\Carbon ? $pembayaranPiutang->tanggal_pembayaran->format('Y-m-d') : date('Y-m-d', strtotime($pembayaranPiutang->tanggal_pembayaran))) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Nominal Pembayaran</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="text"
                                id="nominal_pembayaran_display"
                                class="w-full pl-10 border-gray-300 rounded-md shadow-sm font-medium"
                                placeholder="0"
                                required>
                            <input type="hidden"
                                name="nominal_pembayaran"
                                id="nominal_pembayaran"
                                value="{{ old('nominal_pembayaran', $pembayaranPiutang->nominal_pembayaran) }}">
                        </div>

                        @php
                            $sisaDenganEdit = $piutang->sisa_piutang + $pembayaranPiutang->nominal_pembayaran;
                        @endphp
                        <p class="text-sm text-gray-500 mt-1">
                            Maksimal pembayaran:
                            Rp <span id="max_pembayaran_text">{{ number_format($sisaDenganEdit, 0, ',', '.') }}</span>
                        </p>
                        <p id="error_nominal" class="text-sm text-red-600 mt-1 hidden"></p>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Metode Pembayaran</label>
                        <select name="metode_pembayaran"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required>
                            <option value="tunai" {{ old('metode_pembayaran', $pembayaranPiutang->metode_pembayaran) === 'tunai' ? 'selected' : '' }}>
                                Tunai
                            </option>
                            <option value="transfer" {{ old('metode_pembayaran', $pembayaranPiutang->metode_pembayaran) === 'transfer' ? 'selected' : '' }}>
                                Transfer
                            </option>
                            <option value="giro" {{ old('metode_pembayaran', $pembayaranPiutang->metode_pembayaran) === 'giro' ? 'selected' : '' }}>
                                Giro
                            </option>
                            <option value="lainnya" {{ old('metode_pembayaran', $pembayaranPiutang->metode_pembayaran) === 'lainnya' ? 'selected' : '' }}>
                                Lainnya
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1 font-medium">Catatan</label>
                        <textarea name="catatan"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm">{{ old('catatan', $pembayaranPiutang->catatan) }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ $detailUrl }}"
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const displayInput = document.getElementById('nominal_pembayaran_display');
            const hiddenInput = document.getElementById('nominal_pembayaran');
            
            let maxPembayaran = {{ ceil($sisaDenganEdit) }};
            if (maxPembayaran < 1 && {{ (float)$sisaDenganEdit }} > 0) {
                maxPembayaran = 1;
            }
            
            const errorNominal = document.getElementById('error_nominal');

            function formatRupiah(angka) {
                let number_string = angka.replace(/[^,\d]/g, '').toString();
                let split = number_string.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }

            function cleanNumber(rupiah) {
                return rupiah.replace(/\./g, '').replace(',', '.');
            }

            // Initialize display if there's an old value
            if (hiddenInput.value) {
                displayInput.value = formatRupiah(hiddenInput.value.replace('.', ','));
            }

            displayInput.addEventListener('input', function(e) {
                let val = this.value;
                this.value = formatRupiah(val);
                let cleanVal = cleanNumber(this.value);
                hiddenInput.value = cleanVal;

                if (parseFloat(cleanVal || 0) > maxPembayaran) {
                    errorNominal.textContent = 'Nominal pembayaran melebihi sisa piutang!';
                    errorNominal.classList.remove('hidden');
                    displayInput.classList.add('border-red-500');
                } else {
                    errorNominal.classList.add('hidden');
                    displayInput.classList.remove('border-red-500');
                }
            });

            // Prevent form submit if invalid
            document.getElementById('formBayarPiutang').addEventListener('submit', function(e) {
                let cleanVal = parseFloat(hiddenInput.value || 0);
                if (cleanVal <= 0 || cleanVal > maxPembayaran || isNaN(cleanVal)) {
                    e.preventDefault();
                    alert('Nominal pembayaran tidak valid atau melebihi sisa piutang.');
                }
            });
        });
    </script>
</x-app-layout>