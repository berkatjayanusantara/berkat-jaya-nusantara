<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Supplier',
            'username' => 'admin_supplier',
            'email' => 'admin_supplier@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    public function test_guest_can_not_access_supplier_page(): void
    {
        $response = $this->get('/suppliers');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_supplier_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/suppliers');

        $response->assertStatus(200);
        $response->assertSee('Data Supplier', false);
    }

    public function test_admin_can_open_create_supplier_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/suppliers/create');

        $response->assertStatus(200);
        $response->assertSee('SUP-0001', false);
    }

    public function test_admin_can_store_new_supplier_with_auto_code(): void
    {
        $response = $this->actingAs($this->admin())->post('/suppliers', [
            'nama_supplier' => 'Supplier Testing',
            'nomor_telepon' => '081234567891',
            'alamat' => 'Jl. Testing Supplier No. 1',
            'catatan' => 'Supplier untuk testing',
        ]);

        $response->assertRedirect(route('suppliers.index', absolute: false));

        $this->assertDatabaseHas('suppliers', [
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Testing',
            'nomor_telepon' => '081234567891',
            'alamat' => 'Jl. Testing Supplier No. 1',
            'status_aktif' => true,
        ]);
    }

    public function test_auto_code_supplier_continues_from_last_data(): void
    {
        Supplier::factory()->create([
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Lama',
        ]);

        $response = $this->actingAs($this->admin())->post('/suppliers', [
            'nama_supplier' => 'Supplier Baru',
            'nomor_telepon' => '089999999991',
            'alamat' => 'Alamat supplier baru',
            'catatan' => 'Testing kode otomatis',
        ]);

        $response->assertRedirect(route('suppliers.index', absolute: false));

        $this->assertDatabaseHas('suppliers', [
            'kode_supplier' => 'SUP-0002',
            'nama_supplier' => 'Supplier Baru',
            'nomor_telepon' => '089999999991',
        ]);
    }

    public function test_admin_can_update_supplier(): void
    {
        $supplier = Supplier::factory()->create([
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Lama',
            'nomor_telepon' => '081111111112',
            'alamat' => 'Alamat lama',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())->put('/suppliers/' . $supplier->id_supplier, [
            'nama_supplier' => 'Supplier Baru',
            'nomor_telepon' => '082222222223',
            'alamat' => 'Alamat baru',
            'catatan' => 'Data diperbarui',
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('suppliers.index', absolute: false));

        $this->assertDatabaseHas('suppliers', [
            'id_supplier' => $supplier->id_supplier,
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Baru',
            'nomor_telepon' => '082222222223',
            'alamat' => 'Alamat baru',
            'status_aktif' => true,
        ]);
    }

    public function test_admin_can_nonaktifkan_supplier(): void
    {
        $supplier = Supplier::factory()->create([
            'kode_supplier' => 'SUP-0001',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->patch('/suppliers/' . $supplier->id_supplier . '/nonaktifkan');

        $response->assertRedirect(route('suppliers.index', absolute: false));

        $this->assertDatabaseHas('suppliers', [
            'id_supplier' => $supplier->id_supplier,
            'status_aktif' => false,
        ]);
    }

    public function test_supplier_validation_fails_when_name_is_empty(): void
    {
        $response = $this->actingAs($this->admin())->from('/suppliers/create')->post('/suppliers', [
            'nama_supplier' => '',
            'nomor_telepon' => '081234567891',
            'alamat' => 'Alamat testing',
            'catatan' => null,
        ]);

        $response->assertRedirect('/suppliers/create');
        $response->assertSessionHasErrors('nama_supplier');

        $this->assertDatabaseMissing('suppliers', [
            'nomor_telepon' => '081234567891',
        ]);
    }

    public function test_quick_store_supplier_can_create_new_supplier(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/suppliers/quick-store', [
            'nama_supplier' => 'Quick Supplier',
            'nomor_telepon' => '087777777771',
            'alamat' => 'Alamat quick supplier',
            'catatan' => 'Dibuat dari quick store',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'created',
            'message' => 'Supplier baru berhasil ditambahkan dan langsung dipilih.',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Quick Supplier',
            'nomor_telepon' => '087777777771',
            'status_aktif' => true,
        ]);
    }

    public function test_quick_store_supplier_uses_existing_supplier_when_name_or_phone_already_exists(): void
    {
        $supplier = Supplier::factory()->create([
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Duplikat',
            'nomor_telepon' => '088888888881',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())->postJson('/suppliers/quick-store', [
            'nama_supplier' => 'Supplier Duplikat',
            'nomor_telepon' => '088888888881',
            'alamat' => 'Alamat baru tidak dipakai',
            'catatan' => 'Harus pakai supplier lama',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'exists',
            'message' => 'Supplier sudah tersedia dan langsung dipilih.',
            'supplier' => [
                'id_supplier' => $supplier->id_supplier,
                'kode_supplier' => 'SUP-0001',
                'nama_supplier' => 'Supplier Duplikat',
                'nomor_telepon' => '088888888881',
            ],
        ]);

        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_quick_store_supplier_reactivates_inactive_existing_supplier(): void
    {
        $supplier = Supplier::factory()->nonaktif()->create([
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Nonaktif',
            'nomor_telepon' => '086666666661',
        ]);

        $response = $this->actingAs($this->admin())->postJson('/suppliers/quick-store', [
            'nama_supplier' => 'Supplier Nonaktif',
            'nomor_telepon' => '086666666661',
            'alamat' => 'Alamat supplier nonaktif',
            'catatan' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'exists',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'id_supplier' => $supplier->id_supplier,
            'status_aktif' => true,
        ]);

        $this->assertDatabaseCount('suppliers', 1);
    }
}
