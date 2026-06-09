<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Stock Opname',
            'username' => 'admin_stock_opname',
            'email' => 'admin_stock_opname@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Stock Opname',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    public function test_guest_can_not_access_stock_opname_page(): void
    {
        $response = $this->get('/stock-opname');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_stock_opname_page(): void
    {
        $this->barang();

        $response = $this->actingAs($this->admin())->get('/stock-opname');

        $response->assertStatus(200);
        $response->assertSee('Stock Opname', false);
    }

    public function test_stock_opname_can_increase_stock_when_physical_stock_is_higher(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'stok_saat_ini' => 10,
        ]);

        $tanggalInput = now()->toDateString();
        $tanggalDatabase = now()->startOfDay()->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)->post('/stock-opname', [
            'tanggal' => $tanggalInput,
            'id_barang' => $barang->id_barang,
            'stok_fisik' => 15,
            'keterangan' => 'Stok fisik lebih banyak dari sistem',
        ]);

        $response->assertRedirect(route('stock-opname.create', absolute: false));

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 15,
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang' => $barang->id_barang,
            'tanggal' => $tanggalDatabase,
            'jenis_pergerakan' => 'penyesuaian',
            'jumlah' => 5,
            'stok_sebelum' => 10,
            'stok_sesudah' => 15,
            'keterangan' => 'Stok fisik lebih banyak dari sistem',
            'dibuat_oleh' => $user->id_user,
        ]);

        $riwayat = RiwayatStok::where('id_barang', $barang->id_barang)->first();

        $this->assertNotNull($riwayat);
        $this->assertStringStartsWith('STOCK-OPNAME-', $riwayat->sumber_transaksi);
    }

    public function test_stock_opname_can_decrease_stock_when_physical_stock_is_lower(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'stok_saat_ini' => 10,
        ]);

        $tanggalInput = now()->toDateString();
        $tanggalDatabase = now()->startOfDay()->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)->post('/stock-opname', [
            'tanggal' => $tanggalInput,
            'id_barang' => $barang->id_barang,
            'stok_fisik' => 6,
            'keterangan' => 'Stok fisik lebih sedikit dari sistem',
        ]);

        $response->assertRedirect(route('stock-opname.create', absolute: false));

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 6,
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang' => $barang->id_barang,
            'tanggal' => $tanggalDatabase,
            'jenis_pergerakan' => 'penyesuaian',
            'jumlah' => 4,
            'stok_sebelum' => 10,
            'stok_sesudah' => 6,
            'keterangan' => 'Stok fisik lebih sedikit dari sistem',
            'dibuat_oleh' => $user->id_user,
        ]);

        $riwayat = RiwayatStok::where('id_barang', $barang->id_barang)->first();

        $this->assertNotNull($riwayat);
        $this->assertStringStartsWith('STOCK-OPNAME-', $riwayat->sumber_transaksi);
    }

    public function test_stock_opname_with_same_stock_still_creates_history(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'stok_saat_ini' => 10,
        ]);

        $tanggalInput = now()->toDateString();
        $tanggalDatabase = now()->startOfDay()->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)->post('/stock-opname', [
            'tanggal' => $tanggalInput,
            'id_barang' => $barang->id_barang,
            'stok_fisik' => 10,
            'keterangan' => null,
        ]);

        $response->assertRedirect(route('stock-opname.create', absolute: false));

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 10,
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang' => $barang->id_barang,
            'tanggal' => $tanggalDatabase,
            'jenis_pergerakan' => 'penyesuaian',
            'jumlah' => 0,
            'stok_sebelum' => 10,
            'stok_sesudah' => 10,
            'dibuat_oleh' => $user->id_user,
        ]);

        $riwayat = RiwayatStok::where('id_barang', $barang->id_barang)->first();

        $this->assertNotNull($riwayat);
        $this->assertStringStartsWith('STOCK-OPNAME-', $riwayat->sumber_transaksi);
        $this->assertStringContainsString('Stok fisik sama dengan stok sistem', $riwayat->keterangan);
    }

    public function test_stock_opname_validation_fails_when_barang_is_empty(): void
    {
        $response = $this->actingAs($this->admin())->from('/stock-opname')->post('/stock-opname', [
            'tanggal' => now()->toDateString(),
            'id_barang' => '',
            'stok_fisik' => 10,
            'keterangan' => 'Barang kosong',
        ]);

        $response->assertRedirect('/stock-opname');
        $response->assertSessionHasErrors('id_barang');

        $this->assertDatabaseCount('riwayat_stok', 0);
    }

    public function test_stock_opname_validation_fails_when_physical_stock_is_negative(): void
    {
        $barang = $this->barang([
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($this->admin())->from('/stock-opname')->post('/stock-opname', [
            'tanggal' => now()->toDateString(),
            'id_barang' => $barang->id_barang,
            'stok_fisik' => -1,
            'keterangan' => 'Stok fisik minus',
        ]);

        $response->assertRedirect('/stock-opname');
        $response->assertSessionHasErrors('stok_fisik');

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 10,
        ]);

        $this->assertDatabaseCount('riwayat_stok', 0);
    }

    public function test_stock_opname_can_be_filtered_by_search_on_create_page(): void
    {
        $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Testing',
        ]);

        $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Pasir Testing',
        ]);

        $response = $this->actingAs($this->admin())->get('/stock-opname?search=Semen');

        $response->assertStatus(200);
        $response->assertSee('Semen Testing', false);
        $response->assertDontSee('Pasir Testing', false);
    }
}
