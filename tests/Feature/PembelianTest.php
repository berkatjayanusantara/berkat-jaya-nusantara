<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\RiwayatStok;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembelianTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Pembelian',
            'username' => 'admin_pembelian',
            'email' => 'admin_pembelian@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    private function supplier(): Supplier
    {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create([
            'kode_supplier' => 'SUP-0001',
            'nama_supplier' => 'Supplier Test',
            'nomor_telepon' => '081234567891',
            'status_aktif' => true,
        ]);

        return $supplier;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Test',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    public function test_guest_can_not_access_pembelian_page(): void
    {
        $response = $this->get('/pembelian');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_pembelian_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/pembelian');

        $response->assertStatus(200);
        $response->assertSee('Pembelian', false);
    }

    public function test_admin_can_open_create_pembelian_page(): void
    {
        $this->supplier();
        $this->barang();

        $response = $this->actingAs($this->admin())->get('/pembelian/create');

        $response->assertStatus(200);
        $response->assertSee('PB-' . now()->format('Ymd') . '-0001', false);
    }

    public function test_admin_can_store_complete_pembelian_and_increase_stock(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();
        $barang = $this->barang([
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
        ]);

        $response = $this->actingAs($user)->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'catatan' => 'Pembelian testing lengkap',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [5],
            'jumlah' => [5],
            'harga_beli' => [7000],
        ]);

        $response->assertRedirect(route('pembelian.index', absolute: false));

        $this->assertDatabaseHas('pembelian', [
            'nomor_pembelian' => 'PB-' . now()->format('Ymd') . '-0001',
            'id_supplier' => $supplier->id_supplier,
            'status_penerimaan' => 'lengkap',
            'subtotal' => 35000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 35000,
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->assertDatabaseHas('detail_pembelian', [
            'id_barang' => $barang->id_barang,
            'jumlah_dipesan' => 5,
            'jumlah' => 5,
            'harga_beli' => 7000,
            'subtotal' => 35000,
        ]);

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 15,
            'harga_beli_terakhir' => 7000,
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang' => $barang->id_barang,
            'jenis_pergerakan' => 'masuk',
            'jumlah' => 5,
            'stok_sebelum' => 10,
            'stok_sesudah' => 15,
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0001',
            'dibuat_oleh' => $user->id_user,
        ]);
    }

    public function test_admin_can_store_partial_pembelian(): void
    {
        $supplier = $this->supplier();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($this->admin())->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'catatan' => 'Pembelian sebagian',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [10],
            'jumlah' => [6],
            'harga_beli' => [10000],
        ]);

        $response->assertRedirect(route('pembelian.index', absolute: false));

        $this->assertDatabaseHas('pembelian', [
            'status_penerimaan' => 'sebagian',
            'subtotal' => 60000,
            'total_akhir' => 60000,
        ]);

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 26,
        ]);

        $this->assertDatabaseHas('detail_pembelian', [
            'id_barang' => $barang->id_barang,
            'jumlah_dipesan' => 10,
            'jumlah' => 6,
            'subtotal' => 60000,
        ]);
    }

    public function test_pembelian_tax_can_be_added_to_total(): void
    {
        $supplier = $this->supplier();
        $barang = $this->barang([
            'stok_saat_ini' => 0,
        ]);

        $response = $this->actingAs($this->admin())->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 1,
            'catatan' => 'Pajak ditambahkan',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [10],
            'jumlah' => [10],
            'harga_beli' => [10000],
        ]);

        $response->assertRedirect(route('pembelian.index', absolute: false));

        $this->assertDatabaseHas('pembelian', [
            'subtotal' => 100000,
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => true,
            'total_akhir' => 111000,
        ]);
    }

    public function test_pembelian_tax_can_be_only_displayed_without_adding_to_total(): void
    {
        $supplier = $this->supplier();
        $barang = $this->barang([
            'stok_saat_ini' => 0,
        ]);

        $response = $this->actingAs($this->admin())->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 0,
            'catatan' => 'Pajak hanya ditampilkan',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [10],
            'jumlah' => [10],
            'harga_beli' => [10000],
        ]);

        $response->assertRedirect(route('pembelian.index', absolute: false));

        $this->assertDatabaseHas('pembelian', [
            'subtotal' => 100000,
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
        ]);
    }

    public function test_pembelian_validation_fails_when_jumlah_diterima_more_than_jumlah_dipesan(): void
    {
        $supplier = $this->supplier();
        $barang = $this->barang();

        $response = $this->actingAs($this->admin())->from('/pembelian/create')->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'catatan' => 'Jumlah diterima lebih besar',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [5],
            'jumlah' => [6],
            'harga_beli' => [10000],
        ]);

        $response->assertRedirect('/pembelian/create');
        $response->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('pembelian', 0);
        $this->assertDatabaseCount('detail_pembelian', 0);
        $this->assertDatabaseCount('riwayat_stok', 0);
    }

    public function test_pembelian_validation_fails_when_no_item_received(): void
    {
        $supplier = $this->supplier();
        $barang = $this->barang();

        $response = $this->actingAs($this->admin())->from('/pembelian/create')->post('/pembelian', [
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'catatan' => 'Tidak ada barang diterima',
            'id_barang' => [$barang->id_barang],
            'jumlah_dipesan' => [5],
            'jumlah' => [0],
            'harga_beli' => [10000],
        ]);

        $response->assertRedirect('/pembelian/create');
        $response->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('pembelian', 0);
        $this->assertDatabaseCount('detail_pembelian', 0);
        $this->assertDatabaseCount('riwayat_stok', 0);
    }

    public function test_admin_can_open_pembelian_detail_page(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();
        $barang = $this->barang();

        $pembelian = Pembelian::factory()->create([
            'nomor_pembelian' => 'PB-' . now()->format('Ymd') . '-0001',
            'id_supplier' => $supplier->id_supplier,
            'dibuat_oleh' => $user->id_user,
        ]);

        DetailPembelian::factory()->create([
            'id_pembelian' => $pembelian->id_pembelian,
            'id_barang' => $barang->id_barang,
            'jumlah_dipesan' => 5,
            'jumlah' => 5,
            'harga_beli' => 10000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/pembelian/' . $pembelian->id_pembelian);

        $response->assertStatus(200);
        $response->assertSee($pembelian->nomor_pembelian, false);
    }

    public function test_admin_can_export_pembelian_excel(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();
        $barang = $this->barang();

        $pembelian = Pembelian::factory()->create([
            'nomor_pembelian' => 'PB-' . now()->format('Ymd') . '-0001',
            'id_supplier' => $supplier->id_supplier,
            'dibuat_oleh' => $user->id_user,
        ]);

        DetailPembelian::factory()->create([
            'id_pembelian' => $pembelian->id_pembelian,
            'id_barang' => $barang->id_barang,
            'jumlah_dipesan' => 5,
            'jumlah' => 5,
            'harga_beli' => 10000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/pembelian/' . $pembelian->id_pembelian . '/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
    }
}
