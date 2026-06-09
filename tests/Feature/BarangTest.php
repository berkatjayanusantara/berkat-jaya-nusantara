<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Test',
            'username' => 'admin_barang',
            'email' => 'admin_barang@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    public function test_guest_can_not_access_barang_page(): void
    {
        $response = $this->get('/barang');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_barang_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/barang');

        $response->assertStatus(200);
        $response->assertSee('Data Barang', false);
    }

    public function test_admin_can_open_create_barang_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/barang/create');

        $response->assertStatus(200);
        $response->assertSee('BRG-0001', false);
    }

    public function test_admin_can_store_new_barang_with_auto_code(): void
    {
        $response = $this->actingAs($this->admin())->post('/barang', [
            'nama_barang' => 'Semen Tiga Roda',
            'satuan' => 'sak',
            'stok_saat_ini' => 20,
            'harga_beli_terakhir' => 50000,
            'harga_jual_default' => 55000,
            'keterangan' => 'Barang testing',
        ]);

        $response->assertRedirect(route('barang.index', absolute: false));

        $this->assertDatabaseHas('barang', [
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Tiga Roda',
            'satuan' => 'sak',
            'stok_saat_ini' => 20,
            'harga_beli_terakhir' => 50000,
            'harga_jual_default' => 55000,
            'status_aktif' => true,
        ]);
    }

    public function test_auto_code_barang_continues_from_last_data(): void
    {
        Barang::factory()->create([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Lama',
        ]);

        $response = $this->actingAs($this->admin())->post('/barang', [
            'nama_barang' => 'Barang Baru',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 10000,
            'harga_jual_default' => 15000,
            'keterangan' => 'Testing kode otomatis',
        ]);

        $response->assertRedirect(route('barang.index', absolute: false));

        $this->assertDatabaseHas('barang', [
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Baru',
        ]);
    }

    public function test_admin_can_update_barang(): void
    {
        $barang = Barang::factory()->create([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Nama Lama',
            'satuan' => 'pcs',
            'stok_saat_ini' => 5,
            'harga_beli_terakhir' => 10000,
            'harga_jual_default' => 12000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())->put('/barang/' . $barang->id_barang, [
            'nama_barang' => 'Nama Baru',
            'satuan' => 'dus',
            'stok_saat_ini' => 15,
            'harga_beli_terakhir' => 20000,
            'harga_jual_default' => 25000,
            'keterangan' => 'Sudah diperbarui',
            'status_aktif' => 1,
        ]);

        $response->assertRedirect(route('barang.index', absolute: false));

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Nama Baru',
            'satuan' => 'dus',
            'stok_saat_ini' => 15,
            'harga_beli_terakhir' => 20000,
            'harga_jual_default' => 25000,
            'status_aktif' => true,
        ]);
    }

    public function test_admin_can_nonaktifkan_barang(): void
    {
        $barang = Barang::factory()->create([
            'kode_barang' => 'BRG-0001',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->patch('/barang/' . $barang->id_barang . '/nonaktifkan');

        $response->assertRedirect(route('barang.index', absolute: false));

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'status_aktif' => false,
        ]);
    }

    public function test_barang_validation_fails_when_stock_is_negative(): void
    {
        $response = $this->actingAs($this->admin())->from('/barang/create')->post('/barang', [
            'nama_barang' => 'Barang Minus',
            'satuan' => 'pcs',
            'stok_saat_ini' => -1,
            'harga_beli_terakhir' => 10000,
            'harga_jual_default' => 15000,
            'keterangan' => null,
        ]);

        $response->assertRedirect('/barang/create');
        $response->assertSessionHasErrors('stok_saat_ini');

        $this->assertDatabaseMissing('barang', [
            'nama_barang' => 'Barang Minus',
        ]);
    }

    public function test_barang_validation_fails_when_name_is_empty(): void
    {
        $response = $this->actingAs($this->admin())->from('/barang/create')->post('/barang', [
            'nama_barang' => '',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 10000,
            'harga_jual_default' => 15000,
            'keterangan' => null,
        ]);

        $response->assertRedirect('/barang/create');
        $response->assertSessionHasErrors('nama_barang');
    }
}
