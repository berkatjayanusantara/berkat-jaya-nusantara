<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Customer',
            'username' => 'admin_customer',
            'email' => 'admin_customer@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    public function test_guest_can_not_access_customer_page(): void
    {
        $response = $this->get('/customers');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_customer_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/customers');

        $response->assertStatus(200);
        $response->assertSee('Data Customer', false);
    }

    public function test_admin_can_open_create_customer_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/customers/create');

        $response->assertStatus(200);
        $response->assertSee('CUS-0001', false);
    }

    public function test_admin_can_store_new_customer_with_auto_code(): void
    {
        $response = $this->actingAs($this->admin())->post('/customers', [
            'nama_customer' => 'Customer Testing',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Testing Customer No. 1',
            'kategori_customer' => 'Grosir',
            'catatan' => 'Customer untuk testing',
        ]);

        $response->assertRedirect(route('customers.index', absolute: false));

        $this->assertDatabaseHas('customers', [
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Testing',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Testing Customer No. 1',
            'kategori_customer' => 'Grosir',
            'status_aktif' => true,
        ]);
    }

    public function test_auto_code_customer_continues_from_last_data(): void
    {
        Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Lama',
        ]);

        $response = $this->actingAs($this->admin())->post('/customers', [
            'nama_customer' => 'Customer Baru',
            'nomor_telepon' => '089999999999',
            'alamat' => 'Alamat customer baru',
            'kategori_customer' => 'Retail',
            'catatan' => 'Testing kode otomatis',
        ]);

        $response->assertRedirect(route('customers.index', absolute: false));

        $this->assertDatabaseHas('customers', [
            'kode_customer' => 'CUS-0002',
            'nama_customer' => 'Customer Baru',
            'nomor_telepon' => '089999999999',
        ]);
    }

    public function test_admin_can_update_customer(): void
    {
        $customer = Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Nama Lama',
            'nomor_telepon' => '081111111111',
            'alamat' => 'Alamat lama',
            'kategori_customer' => 'Retail',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())->put('/customers/' . $customer->id_customer, [
            'nama_customer' => 'Nama Baru',
            'nomor_telepon' => '082222222222',
            'alamat' => 'Alamat baru',
            'kategori_customer' => 'Grosir',
            'catatan' => 'Data diperbarui',
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('customers.index', absolute: false));

        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Nama Baru',
            'nomor_telepon' => '082222222222',
            'alamat' => 'Alamat baru',
            'kategori_customer' => 'Grosir',
            'status_aktif' => true,
        ]);
    }

    public function test_admin_can_nonaktifkan_customer(): void
    {
        $customer = Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->patch('/customers/' . $customer->id_customer . '/nonaktifkan');

        $response->assertRedirect(route('customers.index', absolute: false));

        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'status_aktif' => false,
        ]);
    }

    public function test_customer_validation_fails_when_name_is_empty(): void
    {
        $response = $this->actingAs($this->admin())->from('/customers/create')->post('/customers', [
            'nama_customer' => '',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Alamat testing',
            'kategori_customer' => 'Retail',
            'catatan' => null,
        ]);

        $response->assertRedirect('/customers/create');
        $response->assertSessionHasErrors('nama_customer');

        $this->assertDatabaseMissing('customers', [
            'nomor_telepon' => '081234567890',
        ]);
    }

    public function test_quick_store_customer_can_create_new_customer(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/customers/quick-store', [
            'nama_customer' => 'Quick Customer',
            'nomor_telepon' => '087777777777',
            'alamat' => 'Alamat quick customer',
            'kategori_customer' => 'Langganan',
            'catatan' => 'Dibuat dari quick store',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'created',
            'message' => 'Customer baru berhasil ditambahkan dan langsung dipilih.',
        ]);

        $this->assertDatabaseHas('customers', [
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Quick Customer',
            'nomor_telepon' => '087777777777',
            'status_aktif' => true,
        ]);
    }

    public function test_quick_store_customer_uses_existing_customer_when_name_or_phone_already_exists(): void
    {
        $customer = Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Duplikat',
            'nomor_telepon' => '088888888888',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())->postJson('/customers/quick-store', [
            'nama_customer' => 'Customer Duplikat',
            'nomor_telepon' => '088888888888',
            'alamat' => 'Alamat baru tidak dipakai',
            'kategori_customer' => 'Grosir',
            'catatan' => 'Harus pakai customer lama',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'exists',
            'message' => 'Customer sudah tersedia dan langsung dipilih.',
            'customer' => [
                'id_customer' => $customer->id_customer,
                'kode_customer' => 'CUS-0001',
                'nama_customer' => 'Customer Duplikat',
                'nomor_telepon' => '088888888888',
            ],
        ]);

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_quick_store_customer_reactivates_inactive_existing_customer(): void
    {
        $customer = Customer::factory()->nonaktif()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Nonaktif',
            'nomor_telepon' => '086666666666',
        ]);

        $response = $this->actingAs($this->admin())->postJson('/customers/quick-store', [
            'nama_customer' => 'Customer Nonaktif',
            'nomor_telepon' => '086666666666',
            'alamat' => 'Alamat customer nonaktif',
            'kategori_customer' => 'Retail',
            'catatan' => null,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'exists',
        ]);

        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'status_aktif' => true,
        ]);

        $this->assertDatabaseCount('customers', 1);
    }
}
