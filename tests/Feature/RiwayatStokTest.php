<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\RiwayatStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RiwayatStokTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Riwayat Stok',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ], $override));

        return $user;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_barang' => 'Barang Riwayat Stok',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    private function riwayat(Barang $barang, User $user, array $override = []): RiwayatStok
    {
        /** @var RiwayatStok $riwayat */
        $riwayat = RiwayatStok::factory()->create(array_merge([
            'id_barang' => $barang->id_barang,
            'tanggal' => now()->toDateString(),
            'jenis_pergerakan' => 'masuk',
            'jumlah' => 5,
            'stok_sebelum' => 10,
            'stok_sesudah' => 15,
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'keterangan' => 'Riwayat stok testing',
            'dibuat_oleh' => $user->id_user,
            'created_at' => now(),
        ], $override));

        return $riwayat;
    }

    private function riwayatStokIdsFromResponse(TestResponse $response): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('riwayatStok');

        return $paginator
            ->getCollection()
            ->pluck('id_riwayat_stok')
            ->values()
            ->all();
    }

    private function assertResponseHasRiwayat(TestResponse $response, RiwayatStok $riwayat): void
    {
        $this->assertContains(
            $riwayat->id_riwayat_stok,
            $this->riwayatStokIdsFromResponse($response)
        );
    }

    private function assertResponseDoesNotHaveRiwayat(TestResponse $response, RiwayatStok $riwayat): void
    {
        $this->assertNotContains(
            $riwayat->id_riwayat_stok,
            $this->riwayatStokIdsFromResponse($response)
        );
    }

    public function test_guest_can_not_access_riwayat_stok_page(): void
    {
        $response = $this->get('/riwayat-stok');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_riwayat_stok_page(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->get('/riwayat-stok');

        $response->assertStatus(200);
        $response->assertSee('Riwayat Stok', false);
        $response->assertViewHas('riwayatStok');
        $response->assertViewHas('barang');
    }

    public function test_riwayat_stok_masuk_is_displayed(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Masuk',
        ]);

        $riwayat = $this->riwayat($barang, $user, [
            'jenis_pergerakan' => 'masuk',
            'jumlah' => 10,
            'stok_sebelum' => 5,
            'stok_sesudah' => 15,
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0001',
            'keterangan' => 'Stok masuk dari pembelian',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayat);
    }

    public function test_riwayat_stok_keluar_is_displayed(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Semen Keluar',
        ]);

        $riwayat = $this->riwayat($barang, $user, [
            'jenis_pergerakan' => 'keluar',
            'jumlah' => 3,
            'stok_sebelum' => 15,
            'stok_sesudah' => 12,
            'sumber_transaksi' => 'INV-' . now()->format('Ymd') . '-0001',
            'keterangan' => 'Stok keluar dari penjualan',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayat);
    }

    public function test_riwayat_stok_penyesuaian_is_displayed(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'kode_barang' => 'BRG-0003',
            'nama_barang' => 'Semen Opname',
        ]);

        $riwayat = $this->riwayat($barang, $user, [
            'jenis_pergerakan' => 'penyesuaian',
            'jumlah' => 2,
            'stok_sebelum' => 10,
            'stok_sesudah' => 12,
            'sumber_transaksi' => 'STOCK-OPNAME-' . now()->format('YmdHis'),
            'keterangan' => 'Stock opname testing',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayat);
    }

    public function test_admin_can_filter_riwayat_stok_by_barang_name_search(): void
    {
        $user = $this->admin();

        $barangSemen = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Filter',
        ]);

        $barangPasir = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Pasir Filter',
        ]);

        $riwayatSemen = $this->riwayat($barangSemen, $user, [
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0001',
        ]);

        $riwayatPasir = $this->riwayat($barangPasir, $user, [
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0002',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?search=Semen');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatSemen);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatPasir);
    }

    public function test_admin_can_filter_riwayat_stok_by_barang_code_search(): void
    {
        $user = $this->admin();

        $barangSatu = $this->barang([
            'kode_barang' => 'BRG-KODE-001',
            'nama_barang' => 'Barang Kode Satu',
        ]);

        $barangDua = $this->barang([
            'kode_barang' => 'BRG-KODE-002',
            'nama_barang' => 'Barang Kode Dua',
        ]);

        $riwayatSatu = $this->riwayat($barangSatu, $user, [
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0001',
        ]);

        $riwayatDua = $this->riwayat($barangDua, $user, [
            'sumber_transaksi' => 'PB-' . now()->format('Ymd') . '-0002',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?search=BRG-KODE-001');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatSatu);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatDua);
    }

    public function test_admin_can_filter_riwayat_stok_by_sumber_transaksi_search(): void
    {
        $user = $this->admin();

        $barangSatu = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Sumber Satu',
        ]);

        $barangDua = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Sumber Dua',
        ]);

        $riwayatSatu = $this->riwayat($barangSatu, $user, [
            'sumber_transaksi' => 'PB-CARI-0001',
        ]);

        $riwayatDua = $this->riwayat($barangDua, $user, [
            'sumber_transaksi' => 'INV-CARI-0002',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?search=PB-CARI-0001');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatSatu);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatDua);
    }

    public function test_admin_can_filter_riwayat_stok_by_jenis_masuk(): void
    {
        $user = $this->admin();

        $barangMasuk = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Masuk Filter',
        ]);

        $barangKeluar = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Keluar Filter',
        ]);

        $riwayatMasuk = $this->riwayat($barangMasuk, $user, [
            'jenis_pergerakan' => 'masuk',
            'sumber_transaksi' => 'PB-FILTER-0001',
        ]);

        $riwayatKeluar = $this->riwayat($barangKeluar, $user, [
            'jenis_pergerakan' => 'keluar',
            'sumber_transaksi' => 'INV-FILTER-0001',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?jenis_pergerakan=masuk');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatMasuk);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatKeluar);
    }

    public function test_admin_can_filter_riwayat_stok_by_jenis_keluar(): void
    {
        $user = $this->admin();

        $barangMasuk = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Masuk Filter',
        ]);

        $barangKeluar = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Keluar Filter',
        ]);

        $riwayatMasuk = $this->riwayat($barangMasuk, $user, [
            'jenis_pergerakan' => 'masuk',
            'sumber_transaksi' => 'PB-FILTER-0002',
        ]);

        $riwayatKeluar = $this->riwayat($barangKeluar, $user, [
            'jenis_pergerakan' => 'keluar',
            'sumber_transaksi' => 'INV-FILTER-0002',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?jenis_pergerakan=keluar');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatKeluar);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatMasuk);
    }

    public function test_admin_can_filter_riwayat_stok_by_jenis_penyesuaian(): void
    {
        $user = $this->admin();

        $barangMasuk = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Masuk Filter',
        ]);

        $barangOpname = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Opname Filter',
        ]);

        $riwayatMasuk = $this->riwayat($barangMasuk, $user, [
            'jenis_pergerakan' => 'masuk',
            'sumber_transaksi' => 'PB-FILTER-0003',
        ]);

        $riwayatOpname = $this->riwayat($barangOpname, $user, [
            'jenis_pergerakan' => 'penyesuaian',
            'sumber_transaksi' => 'STOCK-OPNAME-FILTER',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?jenis_pergerakan=penyesuaian');

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatOpname);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatMasuk);
    }

    public function test_admin_can_filter_riwayat_stok_by_barang_id(): void
    {
        $user = $this->admin();

        $barangSatu = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang ID Satu',
        ]);

        $barangDua = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang ID Dua',
        ]);

        $riwayatSatu = $this->riwayat($barangSatu, $user, [
            'sumber_transaksi' => 'PB-ID-0001',
        ]);

        $riwayatDua = $this->riwayat($barangDua, $user, [
            'sumber_transaksi' => 'PB-ID-0002',
        ]);

        $response = $this->actingAs($user)->get('/riwayat-stok?id_barang=' . $barangSatu->id_barang);

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatSatu);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatDua);
    }

    public function test_admin_can_filter_riwayat_stok_by_tanggal_mulai(): void
    {
        $user = $this->admin();

        $barangLama = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Lama Tanggal',
        ]);

        $barangBaru = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Baru Tanggal',
        ]);

        $riwayatLama = $this->riwayat($barangLama, $user, [
            'tanggal' => now()->subDays(10)->toDateString(),
            'sumber_transaksi' => 'PB-LAMA-0001',
        ]);

        $riwayatBaru = $this->riwayat($barangBaru, $user, [
            'tanggal' => now()->toDateString(),
            'sumber_transaksi' => 'PB-BARU-0001',
        ]);

        $tanggalMulai = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/riwayat-stok?tanggal_mulai=' . $tanggalMulai);

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatBaru);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatLama);
    }

    public function test_admin_can_filter_riwayat_stok_by_tanggal_selesai(): void
    {
        $user = $this->admin();

        $barangLama = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Lama Selesai',
        ]);

        $barangBaru = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Baru Selesai',
        ]);

        $riwayatLama = $this->riwayat($barangLama, $user, [
            'tanggal' => now()->subDays(10)->toDateString(),
            'sumber_transaksi' => 'PB-LAMA-0002',
        ]);

        $riwayatBaru = $this->riwayat($barangBaru, $user, [
            'tanggal' => now()->toDateString(),
            'sumber_transaksi' => 'PB-BARU-0002',
        ]);

        $tanggalSelesai = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/riwayat-stok?tanggal_selesai=' . $tanggalSelesai);

        $response->assertStatus(200);
        $this->assertResponseHasRiwayat($response, $riwayatLama);
        $this->assertResponseDoesNotHaveRiwayat($response, $riwayatBaru);
    }
}
