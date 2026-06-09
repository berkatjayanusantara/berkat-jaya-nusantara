<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Penjualan',
            'username' => 'admin_penjualan',
            'email' => 'admin_penjualan@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        return $user;
    }

    private function customer(): Customer
    {
        /** @var Customer $customer */
        $customer = Customer::factory()->create([
            'kode_customer' => 'CUS-0001',
            'nama_customer' => 'Customer Test',
            'nomor_telepon' => '081234567890',
            'status_aktif' => true,
        ]);

        return $customer;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Test',
            'satuan' => 'pcs',
            'stok_saat_ini' => 20,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 10000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    public function test_guest_can_not_access_penjualan_page(): void
    {
        $response = $this->get('/penjualan');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_penjualan_index_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/penjualan');

        $response->assertStatus(200);
        $response->assertSee('Penjualan', false);
    }

    public function test_admin_can_open_create_penjualan_page(): void
    {
        $this->customer();
        $this->barang();

        $response = $this->actingAs($this->admin())->get('/penjualan/create');

        $response->assertStatus(200);
        $response->assertSee('INV-' . now()->format('Ymd') . '-0001', false);
    }

    public function test_admin_can_store_cash_penjualan_and_decrease_stock(): void
    {
        $user = $this->admin();
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($user)->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'tunai',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Penjualan tunai testing',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [5],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'subtotal' => 50000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 50000,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang' => $barang->id_barang,
            'jumlah' => 5,
            'harga_jual' => 10000,
            'subtotal' => 50000,
        ]);

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 15,
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang' => $barang->id_barang,
            'jenis_pergerakan' => 'keluar',
            'jumlah' => 5,
            'stok_sebelum' => 20,
            'stok_sesudah' => 15,
            'sumber_transaksi' => 'INV-' . now()->format('Ymd') . '-0001',
            'dibuat_oleh' => $user->id_user,
        ]);

        $this->assertDatabaseCount('piutang', 0);
    }

    public function test_credit_penjualan_creates_piutang(): void
    {
        $user = $this->admin();
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $tanggalJatuhTempo = now()->addDays(14)->toDateString();
        $tanggalJatuhTempoDatabase = now()->addDays(14)->startOfDay()->format('Y-m-d H:i:s');

        $response = $this->actingAs($user)->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
            'catatan' => 'Penjualan kredit testing',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [3],
            'harga_jual' => [20000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'subtotal' => 60000,
            'total_akhir' => 60000,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => $tanggalJatuhTempoDatabase,
        ]);

        $this->assertDatabaseHas('piutang', [
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'total_piutang' => 60000,
            'total_dibayar' => 0,
            'sisa_piutang' => 60000,
            'tanggal_jatuh_tempo' => $tanggalJatuhTempoDatabase,
            'status_piutang' => 'belum_lunas',
        ]);

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 17,
        ]);
    }

    public function test_penjualan_tax_can_be_added_to_total(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($this->admin())->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 1,
            'metode_pembayaran' => 'tunai',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Pajak ditambahkan',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [10],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'subtotal' => 100000,
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => true,
            'total_akhir' => 111000,
        ]);
    }

    public function test_penjualan_tax_can_be_only_displayed_without_adding_to_total(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($this->admin())->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'tunai',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Pajak hanya ditampilkan',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [10],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'subtotal' => 100000,
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
        ]);
    }

    public function test_penjualan_validation_fails_when_stock_is_not_enough(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 2,
        ]);

        $response = $this->actingAs($this->admin())->from('/penjualan/create')->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'tunai',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Stok tidak cukup',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [3],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect('/penjualan/create');
        $response->assertSessionHasErrors('stok');

        $this->assertDatabaseCount('penjualan', 0);
        $this->assertDatabaseCount('detail_penjualan', 0);
        $this->assertDatabaseCount('riwayat_stok', 0);

        $this->assertDatabaseHas('barang', [
            'id_barang' => $barang->id_barang,
            'stok_saat_ini' => 2,
        ]);
    }

    public function test_penjualan_validation_fails_when_credit_without_due_date(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($this->admin())->from('/penjualan/create')->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 0,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Kredit tanpa jatuh tempo',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [3],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect('/penjualan/create');
        $response->assertSessionHasErrors('tanggal_jatuh_tempo');

        $this->assertDatabaseCount('penjualan', 0);
        $this->assertDatabaseCount('piutang', 0);
    }

    public function test_admin_can_open_penjualan_detail_page(): void
    {
        $user = $this->admin();
        $customer = $this->customer();
        $barang = $this->barang();

        $penjualan = Penjualan::factory()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        DetailPenjualan::factory()->create([
            'id_penjualan' => $penjualan->id_penjualan,
            'id_barang' => $barang->id_barang,
            'jumlah' => 5,
            'harga_jual' => 10000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/penjualan/' . $penjualan->id_penjualan);

        $response->assertStatus(200);
        $response->assertSee($penjualan->nomor_invoice, false);
    }

    public function test_admin_can_export_penjualan_excel(): void
    {
        $user = $this->admin();
        $customer = $this->customer();
        $barang = $this->barang();

        $penjualan = Penjualan::factory()->create([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-0001',
            'id_customer' => $customer->id_customer,
            'dibuat_oleh' => $user->id_user,
        ]);

        DetailPenjualan::factory()->create([
            'id_penjualan' => $penjualan->id_penjualan,
            'id_barang' => $barang->id_barang,
            'jumlah' => 5,
            'harga_jual' => 10000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/penjualan/' . $penjualan->id_penjualan . '/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_credit_penjualan_with_tax_added_creates_piutang_using_total_akhir(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $tanggalJatuhTempo = now()->addDays(14)->toDateString();

        $response = $this->actingAs($this->admin())->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 1,
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
            'catatan' => 'Kredit dengan pajak ditambahkan',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [10],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'subtotal' => 100000,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => true,
            'total_akhir' => 111000,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $this->assertDatabaseHas('piutang', [
            'total_piutang' => 111000,
            'total_dibayar' => 0,
            'sisa_piutang' => 111000,
            'status_piutang' => 'belum_lunas',
        ]);
    }

    public function test_credit_penjualan_with_tax_only_displayed_creates_piutang_using_subtotal(): void
    {
        $customer = $this->customer();
        $barang = $this->barang([
            'stok_saat_ini' => 20,
        ]);

        $tanggalJatuhTempo = now()->addDays(14)->toDateString();

        $response = $this->actingAs($this->admin())->post('/penjualan', [
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'persentase_pajak' => 11,
            'pajak_ditambahkan' => 0,
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
            'catatan' => 'Kredit dengan pajak hanya ditampilkan',
            'id_barang' => [$barang->id_barang],
            'jumlah' => [10],
            'harga_jual' => [10000],
        ]);

        $response->assertRedirect(route('penjualan.index', absolute: false));

        $this->assertDatabaseHas('penjualan', [
            'subtotal' => 100000,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
        ]);

        $this->assertDatabaseHas('piutang', [
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'status_piutang' => 'belum_lunas',
        ]);
    }
}
