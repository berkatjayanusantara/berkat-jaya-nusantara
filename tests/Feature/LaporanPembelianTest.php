<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LaporanPembelianTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Laporan Pembelian',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ], $override));

        return $user;
    }

    private function supplier(array $override = []): Supplier
    {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create(array_merge([
            'kode_supplier' => 'SUP-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_supplier' => 'Supplier Laporan Pembelian',
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => 'Alamat supplier laporan',
            'status_aktif' => true,
        ], $override));

        return $supplier;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_barang' => 'Barang Laporan Pembelian',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    private function pembelian(Supplier $supplier, User $user, array $override = []): Pembelian
    {
        /** @var Pembelian $pembelian */
        $pembelian = Pembelian::factory()->create(array_merge([
            'nomor_pembelian' => 'PB-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'tanggal_pembelian' => now()->toDateString(),
            'id_supplier' => $supplier->id_supplier,
            'status_penerimaan' => 'lengkap',
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'catatan' => 'Pembelian laporan testing',
            'dibuat_oleh' => $user->id_user,
        ], $override));

        return $pembelian;
    }

    private function detailPembelian(Pembelian $pembelian, Barang $barang, array $override = []): DetailPembelian
    {
        /** @var DetailPembelian $detail */
        $detail = DetailPembelian::factory()->create(array_merge([
            'id_pembelian' => $pembelian->id_pembelian,
            'id_barang' => $barang->id_barang,
            'jumlah_dipesan' => 10,
            'jumlah' => 10,
            'harga_beli' => 10000,
            'subtotal' => 100000,
        ], $override));

        return $detail;
    }

    private function pembelianIdsFromResponse(TestResponse $response): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('pembelian');

        return $paginator
            ->getCollection()
            ->pluck('id_pembelian')
            ->values()
            ->all();
    }

    private function assertResponseHasPembelian(TestResponse $response, Pembelian $pembelian): void
    {
        $this->assertContains(
            $pembelian->id_pembelian,
            $this->pembelianIdsFromResponse($response)
        );
    }

    private function assertResponseDoesNotHavePembelian(TestResponse $response, Pembelian $pembelian): void
    {
        $this->assertNotContains(
            $pembelian->id_pembelian,
            $this->pembelianIdsFromResponse($response)
        );
    }

    private function pembelianCollectionFromExcelResponse(TestResponse $response): Collection
    {
        /** @var Collection $pembelian */
        $pembelian = $response->viewData('pembelian');

        return $pembelian;
    }

    public function test_guest_can_not_access_laporan_pembelian_page(): void
    {
        $response = $this->get('/laporan/pembelian');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_laporan_pembelian_page(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->get('/laporan/pembelian');

        $response->assertStatus(200);
        $response->assertViewHas('pembelian');
        $response->assertViewHas('suppliers');
        $response->assertViewHas('totalTransaksi');
        $response->assertViewHas('totalSubtotal');
        $response->assertViewHas('totalPajak');
        $response->assertViewHas('totalAkhir');
        $response->assertViewHas('totalDipesan');
        $response->assertViewHas('totalDiterima');
        $response->assertViewHas('totalSisa');
    }

    public function test_laporan_pembelian_displays_pembelian_data(): void
    {
        $user = $this->admin();

        $supplier = $this->supplier([
            'nama_supplier' => 'Supplier Tampil Pembelian',
        ]);

        $pembelian = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-TAMPIL-0001',
        ]);

        $this->detailPembelian($pembelian, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelian);
    }

    public function test_laporan_pembelian_summary_totals_are_correct(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianSatu = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-TOTAL-0001',
            'subtotal' => 100000,
            'nilai_pajak' => 11000,
            'total_akhir' => 111000,
        ]);

        $this->detailPembelian($pembelianSatu, $this->barang(), [
            'jumlah_dipesan' => 10,
            'jumlah' => 8,
            'harga_beli' => 10000,
            'subtotal' => 80000,
        ]);

        $pembelianDua = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-TOTAL-0002',
            'subtotal' => 200000,
            'nilai_pajak' => 22000,
            'total_akhir' => 222000,
        ]);

        $this->detailPembelian($pembelianDua, $this->barang(), [
            'jumlah_dipesan' => 5,
            'jumlah' => 5,
            'harga_beli' => 40000,
            'subtotal' => 200000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/pembelian');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->viewData('totalTransaksi'));
        $this->assertEquals(300000, $response->viewData('totalSubtotal'));
        $this->assertEquals(33000, $response->viewData('totalPajak'));
        $this->assertEquals(333000, $response->viewData('totalAkhir'));
        $this->assertEquals(15, $response->viewData('totalDipesan'));
        $this->assertEquals(13, $response->viewData('totalDiterima'));
        $this->assertEquals(2, $response->viewData('totalSisa'));
    }

    public function test_admin_can_filter_laporan_pembelian_by_tanggal_awal(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianLama = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-LAMA-0001',
            'tanggal_pembelian' => now()->subDays(10)->toDateString(),
        ]);

        $this->detailPembelian($pembelianLama, $this->barang());

        $pembelianBaru = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-BARU-0001',
            'tanggal_pembelian' => now()->toDateString(),
        ]);

        $this->detailPembelian($pembelianBaru, $this->barang());

        $tanggalAwal = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/laporan/pembelian?tanggal_awal=' . $tanggalAwal);

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianBaru);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianLama);
    }

    public function test_admin_can_filter_laporan_pembelian_by_tanggal_akhir(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianLama = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-LAMA-0002',
            'tanggal_pembelian' => now()->subDays(10)->toDateString(),
        ]);

        $this->detailPembelian($pembelianLama, $this->barang());

        $pembelianBaru = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-BARU-0002',
            'tanggal_pembelian' => now()->toDateString(),
        ]);

        $this->detailPembelian($pembelianBaru, $this->barang());

        $tanggalAkhir = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/laporan/pembelian?tanggal_akhir=' . $tanggalAkhir);

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianLama);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianBaru);
    }

    public function test_admin_can_filter_laporan_pembelian_by_supplier(): void
    {
        $user = $this->admin();

        $supplierSatu = $this->supplier([
            'nama_supplier' => 'Supplier Satu',
        ]);

        $supplierDua = $this->supplier([
            'nama_supplier' => 'Supplier Dua',
        ]);

        $pembelianSatu = $this->pembelian($supplierSatu, $user, [
            'nomor_pembelian' => 'PB-SUP-0001',
        ]);

        $this->detailPembelian($pembelianSatu, $this->barang());

        $pembelianDua = $this->pembelian($supplierDua, $user, [
            'nomor_pembelian' => 'PB-SUP-0002',
        ]);

        $this->detailPembelian($pembelianDua, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian?id_supplier=' . $supplierSatu->id_supplier);

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianSatu);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianDua);
    }

    public function test_admin_can_filter_laporan_pembelian_by_status_lengkap(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianLengkap = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-LENGKAP-0001',
            'status_penerimaan' => 'lengkap',
        ]);

        $this->detailPembelian($pembelianLengkap, $this->barang());

        $pembelianSebagian = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-SEBAGIAN-0001',
            'status_penerimaan' => 'sebagian',
        ]);

        $this->detailPembelian($pembelianSebagian, $this->barang(), [
            'jumlah_dipesan' => 10,
            'jumlah' => 5,
        ]);

        $response = $this->actingAs($user)->get('/laporan/pembelian?status_penerimaan=lengkap');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianLengkap);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianSebagian);
    }

    public function test_admin_can_filter_laporan_pembelian_by_status_sebagian(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianLengkap = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-LENGKAP-0002',
            'status_penerimaan' => 'lengkap',
        ]);

        $this->detailPembelian($pembelianLengkap, $this->barang());

        $pembelianSebagian = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-SEBAGIAN-0002',
            'status_penerimaan' => 'sebagian',
        ]);

        $this->detailPembelian($pembelianSebagian, $this->barang(), [
            'jumlah_dipesan' => 10,
            'jumlah' => 5,
        ]);

        $response = $this->actingAs($user)->get('/laporan/pembelian?status_penerimaan=sebagian');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianSebagian);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianLengkap);
    }

    public function test_admin_can_filter_laporan_pembelian_by_search_nomor_pembelian(): void
    {
        $user = $this->admin();
        $supplier = $this->supplier();

        $pembelianSatu = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-CARI-0001',
        ]);

        $this->detailPembelian($pembelianSatu, $this->barang());

        $pembelianDua = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-LAIN-0002',
        ]);

        $this->detailPembelian($pembelianDua, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian?search=PB-CARI-0001');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianSatu);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianDua);
    }

    public function test_admin_can_filter_laporan_pembelian_by_search_supplier_name(): void
    {
        $user = $this->admin();

        $supplierSemen = $this->supplier([
            'nama_supplier' => 'Supplier Semen',
        ]);

        $supplierPasir = $this->supplier([
            'nama_supplier' => 'Supplier Pasir',
        ]);

        $pembelianSemen = $this->pembelian($supplierSemen, $user, [
            'nomor_pembelian' => 'PB-SEMEN-0001',
        ]);

        $this->detailPembelian($pembelianSemen, $this->barang());

        $pembelianPasir = $this->pembelian($supplierPasir, $user, [
            'nomor_pembelian' => 'PB-PASIR-0001',
        ]);

        $this->detailPembelian($pembelianPasir, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian?search=Semen');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianSemen);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianPasir);
    }

    public function test_admin_can_filter_laporan_pembelian_by_search_supplier_phone(): void
    {
        $user = $this->admin();

        $supplierSatu = $this->supplier([
            'nama_supplier' => 'Supplier Telepon Satu',
            'nomor_telepon' => '081111111111',
        ]);

        $supplierDua = $this->supplier([
            'nama_supplier' => 'Supplier Telepon Dua',
            'nomor_telepon' => '082222222222',
        ]);

        $pembelianSatu = $this->pembelian($supplierSatu, $user, [
            'nomor_pembelian' => 'PB-TELP-0001',
        ]);

        $this->detailPembelian($pembelianSatu, $this->barang());

        $pembelianDua = $this->pembelian($supplierDua, $user, [
            'nomor_pembelian' => 'PB-TELP-0002',
        ]);

        $this->detailPembelian($pembelianDua, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian?search=081111111111');

        $response->assertStatus(200);
        $this->assertResponseHasPembelian($response, $pembelianSatu);
        $this->assertResponseDoesNotHavePembelian($response, $pembelianDua);
    }

    public function test_admin_can_export_laporan_pembelian_excel(): void
    {
        $user = $this->admin();

        $supplier = $this->supplier();

        $pembelian = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-EXPORT-0001',
        ]);

        $this->detailPembelian($pembelian, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $pembelianExport = $this->pembelianCollectionFromExcelResponse($response);

        $this->assertTrue(
            $pembelianExport->pluck('id_pembelian')->contains($pembelian->id_pembelian)
        );
    }

    public function test_admin_can_export_laporan_pembelian_excel_with_filter(): void
    {
        $user = $this->admin();

        $supplierSatu = $this->supplier([
            'nama_supplier' => 'Supplier Export Satu',
        ]);

        $supplierDua = $this->supplier([
            'nama_supplier' => 'Supplier Export Dua',
        ]);

        $pembelianSatu = $this->pembelian($supplierSatu, $user, [
            'nomor_pembelian' => 'PB-EXPORT-SATU',
        ]);

        $this->detailPembelian($pembelianSatu, $this->barang());

        $pembelianDua = $this->pembelian($supplierDua, $user, [
            'nomor_pembelian' => 'PB-EXPORT-DUA',
        ]);

        $this->detailPembelian($pembelianDua, $this->barang());

        $response = $this->actingAs($user)->get('/laporan/pembelian/export-excel?search=SATU');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $pembelianExport = $this->pembelianCollectionFromExcelResponse($response);

        $this->assertTrue(
            $pembelianExport->pluck('id_pembelian')->contains($pembelianSatu->id_pembelian)
        );

        $this->assertFalse(
            $pembelianExport->pluck('id_pembelian')->contains($pembelianDua->id_pembelian)
        );
    }
}
