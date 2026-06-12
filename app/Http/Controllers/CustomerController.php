<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('kode_customer', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%")
                    ->orWhere('npwp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        $kodeCustomer = $this->generateKodeCustomer();

        return view('customers.create', compact('kodeCustomer'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        $this->tambahkanValidasiDuplikatCustomer($validator, $request);

        $validator->validate();

        Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => trim($request->nama_customer),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($request->nomor_telepon),
            'npwp' => $this->ubahKosongMenjadiNull($request->npwp),
            'alamat' => $this->ubahKosongMenjadiNull($request->alamat),
            'kategori_customer' => $this->ubahKosongMenjadiNull($request->kategori_customer),
            'catatan' => $this->ubahKosongMenjadiNull($request->catatan),
            'status_aktif' => true,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Data customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $this->tambahkanValidasiDuplikatCustomer($validator, $request, $customer->id_customer);

        $validator->validate();

        $customer->update([
            'nama_customer' => trim($request->nama_customer),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($request->nomor_telepon),
            'npwp' => $this->ubahKosongMenjadiNull($request->npwp),
            'alamat' => $this->ubahKosongMenjadiNull($request->alamat),
            'kategori_customer' => $this->ubahKosongMenjadiNull($request->kategori_customer),
            'catatan' => $this->ubahKosongMenjadiNull($request->catatan),
            'status_aktif' => $request->status_aktif,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Data customer berhasil diperbarui.');
    }

    public function nonaktifkan(Customer $customer)
    {
        $customer->update([
            'status_aktif' => false,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer berhasil dinonaktifkan.');
    }

    public function quickStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_customer' => 'required|string|max:255',
            'nomor_telepon' => 'nullable|string|max:30',
            'npwp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kategori_customer' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        $this->tambahkanValidasiDuplikatCustomer($validator, $request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data customer tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'kode_customer' => $this->generateKodeCustomer(),
            'nama_customer' => trim($request->nama_customer),
            'nomor_telepon' => $this->ubahKosongMenjadiNull($request->nomor_telepon),
            'npwp' => $this->ubahKosongMenjadiNull($request->npwp),
            'alamat' => $this->ubahKosongMenjadiNull($request->alamat),
            'kategori_customer' => $this->ubahKosongMenjadiNull($request->kategori_customer),
            'catatan' => $this->ubahKosongMenjadiNull($request->catatan),
            'status_aktif' => true,
        ]);

        return response()->json([
            'status' => 'created',
            'message' => 'Customer baru berhasil ditambahkan dan langsung dipilih.',
            'customer' => [
                'id_customer' => $customer->id_customer,
                'kode_customer' => $customer->kode_customer,
                'nama_customer' => $customer->nama_customer,
                'nomor_telepon' => $customer->nomor_telepon,
                'npwp' => $customer->npwp,
                'alamat' => $customer->alamat,
                'kategori_customer' => $customer->kategori_customer,
            ],
        ]);
    }

    private function tambahkanValidasiDuplikatCustomer($validator, Request $request, ?int $ignoreId = null): void
    {
        $validator->after(function ($validator) use ($request, $ignoreId) {
            $namaCustomer = trim($request->nama_customer ?? '');
            $nomorTelepon = trim($request->nomor_telepon ?? '');
            $npwp = trim($request->npwp ?? '');
            $alamat = trim($request->alamat ?? '');

            if ($namaCustomer !== '' && $this->namaCustomerSudahAda($namaCustomer, $ignoreId)) {
                $validator->errors()->add('nama_customer', 'Nama customer sudah digunakan oleh customer lain.');
            }

            if ($nomorTelepon !== '' && $this->nomorTeleponSudahAda($nomorTelepon, $ignoreId)) {
                $validator->errors()->add('nomor_telepon', 'Nomor telepon sudah digunakan oleh customer lain.');
            }

            if ($npwp !== '' && $this->npwpSudahAda($npwp, $ignoreId)) {
                $validator->errors()->add('npwp', 'NPWP sudah digunakan oleh customer lain.');
            }

            if ($alamat !== '' && $this->alamatSudahAda($alamat, $ignoreId)) {
                $validator->errors()->add('alamat', 'Alamat sudah digunakan oleh customer lain.');
            }
        });
    }

    private function namaCustomerSudahAda(string $namaCustomer, ?int $ignoreId = null): bool
    {
        return Customer::query()
            ->when($ignoreId, function ($query, $ignoreId) {
                $query->where('id_customer', '!=', $ignoreId);
            })
            ->whereRaw('LOWER(TRIM(nama_customer)) = ?', [strtolower(trim($namaCustomer))])
            ->exists();
    }

    private function nomorTeleponSudahAda(string $nomorTelepon, ?int $ignoreId = null): bool
    {
        $nomorTeleponNormal = $this->normalisasiNomorTelepon($nomorTelepon);

        if ($nomorTeleponNormal === '') {
            return false;
        }

        return Customer::query()
            ->when($ignoreId, function ($query, $ignoreId) {
                $query->where('id_customer', '!=', $ignoreId);
            })
            ->whereNotNull('nomor_telepon')
            ->get()
            ->contains(function ($customer) use ($nomorTeleponNormal) {
                return $this->normalisasiNomorTelepon($customer->nomor_telepon) === $nomorTeleponNormal;
            });
    }

    private function npwpSudahAda(string $npwp, ?int $ignoreId = null): bool
    {
        $npwpNormal = $this->normalisasiNpwp($npwp);

        if ($npwpNormal === '') {
            return false;
        }

        return Customer::query()
            ->when($ignoreId, function ($query, $ignoreId) {
                $query->where('id_customer', '!=', $ignoreId);
            })
            ->whereNotNull('npwp')
            ->get()
            ->contains(function ($customer) use ($npwpNormal) {
                return $this->normalisasiNpwp($customer->npwp) === $npwpNormal;
            });
    }

    private function alamatSudahAda(string $alamat, ?int $ignoreId = null): bool
    {
        $alamatNormal = $this->normalisasiTeks($alamat);

        if ($alamatNormal === '') {
            return false;
        }

        return Customer::query()
            ->when($ignoreId, function ($query, $ignoreId) {
                $query->where('id_customer', '!=', $ignoreId);
            })
            ->whereNotNull('alamat')
            ->get()
            ->contains(function ($customer) use ($alamatNormal) {
                return $this->normalisasiTeks($customer->alamat) === $alamatNormal;
            });
    }

    private function generateKodeCustomer()
    {
        $lastCustomer = Customer::orderBy('id_customer', 'desc')->first();

        if (!$lastCustomer) {
            return 'CUS-0001';
        }

        $lastNumber = (int) substr($lastCustomer->kode_customer, 4);
        $newNumber = $lastNumber + 1;

        return 'CUS-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function normalisasiNomorTelepon(?string $nomorTelepon): string
    {
        return preg_replace('/[^0-9]/', '', $nomorTelepon ?? '');
    }

    private function normalisasiNpwp(?string $npwp): string
    {
        return preg_replace('/[^0-9]/', '', $npwp ?? '');
    }

    private function normalisasiTeks(?string $teks): string
    {
        $teks = trim($teks ?? '');
        $teks = preg_replace('/\s+/', ' ', $teks);

        return strtolower($teks);
    }

    private function ubahKosongMenjadiNull(?string $value): ?string
    {
        $value = trim($value ?? '');

        return $value === '' ? null : $value;
    }
}
