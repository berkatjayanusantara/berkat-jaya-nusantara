<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LaporanStokBarangTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Laporan Stok',
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
            'nama_barang' => 'Barang Laporan Stok',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    private function barangIdsFromResponse(TestResponse $response): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('barang');

        return $paginator
            ->getCollection()
            ->pluck('id_barang')
            ->values()
            ->all();
    }

    private function assertResponseHasBarang(TestResponse $response, Barang $barang): void
    {
        $this->assertContains(
            $barang->id_barang,
            $this->barangIdsFromResponse($response)
        );
    }

    private function assertResponseDoesNotHaveBarang(TestResponse $response, Barang $barang): void
    {
        $this->assertNotContains(
            $barang->id_barang,
            $this->barangIdsFromResponse($response)
        );
    }

    private function barangCollectionFromExcelResponse(TestResponse $response): Collection
    {
        /** @var Collection $barang */
        $barang = $response->viewData('barang');

        return $barang;
    }

    public function test_guest_can_not_access_laporan_stok_barang_page(): void
    {
        $response = $this->get('/laporan/stok-barang');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_laporan_stok_barang_page(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->get('/laporan/stok-barang');

        $response->assertStatus(200);
        $response->assertViewHas('barang');
        $response->assertViewHas('totalBarang');
        $response->assertViewHas('totalStok');
        $response->assertViewHas('totalBarangKosong');
        $response->assertViewHas('totalBarangStokRendah');
        $response->assertViewHas('totalNilaiStok');
        $response->assertViewHas('totalEstimasiNilaiJual');
    }

    public function test_laporan_stok_barang_displays_barang_data(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Laporan',
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barang);
    }

    public function test_laporan_stok_barang_summary_totals_are_correct(): void
    {
        $user = $this->admin();

        $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Normal',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ]);

        $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Rendah',
            'stok_saat_ini' => 3,
            'harga_beli_terakhir' => 4000,
            'harga_jual_default' => 7000,
            'status_aktif' => true,
        ]);

        $this->barang([
            'kode_barang' => 'BRG-0003',
            'nama_barang' => 'Barang Kosong',
            'stok_saat_ini' => 0,
            'harga_beli_terakhir' => 6000,
            'harga_jual_default' => 9000,
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?batas_stok_rendah=5');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->viewData('totalBarang'));
        $this->assertEquals(13, $response->viewData('totalStok'));
        $this->assertEquals(1, $response->viewData('totalBarangKosong'));
        $this->assertEquals(1, $response->viewData('totalBarangStokRendah'));
        $this->assertEquals(62000, $response->viewData('totalNilaiStok'));
        $this->assertEquals(101000, $response->viewData('totalEstimasiNilaiJual'));
    }

    public function test_admin_can_filter_laporan_stok_by_search_name(): void
    {
        $user = $this->admin();

        $barangSemen = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Filter Stok',
        ]);

        $barangPasir = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Pasir Filter Stok',
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?search=Semen');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangSemen);
        $this->assertResponseDoesNotHaveBarang($response, $barangPasir);
    }

    public function test_admin_can_filter_laporan_stok_by_search_code(): void
    {
        $user = $this->admin();

        $barangSatu = $this->barang([
            'kode_barang' => 'BRG-CARI-001',
            'nama_barang' => 'Barang Kode Satu',
        ]);

        $barangDua = $this->barang([
            'kode_barang' => 'BRG-CARI-002',
            'nama_barang' => 'Barang Kode Dua',
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?search=BRG-CARI-001');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangSatu);
        $this->assertResponseDoesNotHaveBarang($response, $barangDua);
    }

    public function test_admin_can_filter_laporan_stok_by_search_satuan(): void
    {
        $user = $this->admin();

        $barangSak = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Satuan',
            'satuan' => 'sak',
        ]);

        $barangPcs = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Paku Satuan',
            'satuan' => 'pcs',
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?search=sak');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangSak);
        $this->assertResponseDoesNotHaveBarang($response, $barangPcs);
    }

    public function test_admin_can_filter_laporan_stok_by_status_active(): void
    {
        $user = $this->admin();

        $barangAktif = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Aktif',
            'status_aktif' => true,
        ]);

        $barangNonaktif = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Nonaktif',
            'status_aktif' => false,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?status_barang=1');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangAktif);
        $this->assertResponseDoesNotHaveBarang($response, $barangNonaktif);
    }

    public function test_admin_can_filter_laporan_stok_by_status_inactive(): void
    {
        $user = $this->admin();

        $barangAktif = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Aktif',
            'status_aktif' => true,
        ]);

        $barangNonaktif = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Nonaktif',
            'status_aktif' => false,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?status_barang=0');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangNonaktif);
        $this->assertResponseDoesNotHaveBarang($response, $barangAktif);
    }

    public function test_admin_can_filter_laporan_stok_kosong(): void
    {
        $user = $this->admin();

        $barangKosong = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Kosong',
            'stok_saat_ini' => 0,
        ]);

        $barangTersedia = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Tersedia',
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?kondisi_stok=kosong');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangKosong);
        $this->assertResponseDoesNotHaveBarang($response, $barangTersedia);
    }

    public function test_admin_can_filter_laporan_stok_rendah(): void
    {
        $user = $this->admin();

        $barangRendah = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Rendah',
            'stok_saat_ini' => 3,
        ]);

        $barangKosong = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Kosong',
            'stok_saat_ini' => 0,
        ]);

        $barangTersedia = $this->barang([
            'kode_barang' => 'BRG-0003',
            'nama_barang' => 'Barang Tersedia',
            'stok_saat_ini' => 20,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?kondisi_stok=rendah&batas_stok_rendah=5');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangRendah);
        $this->assertResponseDoesNotHaveBarang($response, $barangKosong);
        $this->assertResponseDoesNotHaveBarang($response, $barangTersedia);
    }

    public function test_admin_can_filter_laporan_stok_tersedia(): void
    {
        $user = $this->admin();

        $barangTersedia = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Tersedia',
            'stok_saat_ini' => 20,
        ]);

        $barangRendah = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Barang Rendah',
            'stok_saat_ini' => 5,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang?kondisi_stok=tersedia&batas_stok_rendah=5');

        $response->assertStatus(200);
        $this->assertResponseHasBarang($response, $barangTersedia);
        $this->assertResponseDoesNotHaveBarang($response, $barangRendah);
    }

    public function test_admin_can_export_laporan_stok_barang_excel(): void
    {
        $user = $this->admin();

        $barang = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Barang Export Excel',
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $barangExport = $this->barangCollectionFromExcelResponse($response);

        $this->assertTrue(
            $barangExport->pluck('id_barang')->contains($barang->id_barang)
        );
    }

    public function test_admin_can_export_laporan_stok_barang_excel_with_filter(): void
    {
        $user = $this->admin();

        $barangSemen = $this->barang([
            'kode_barang' => 'BRG-0001',
            'nama_barang' => 'Semen Export Excel',
        ]);

        $barangPasir = $this->barang([
            'kode_barang' => 'BRG-0002',
            'nama_barang' => 'Pasir Export Excel',
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang/export-excel?search=Semen');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $barangExport = $this->barangCollectionFromExcelResponse($response);

        $this->assertTrue(
            $barangExport->pluck('id_barang')->contains($barangSemen->id_barang)
        );

        $this->assertFalse(
            $barangExport->pluck('id_barang')->contains($barangPasir->id_barang)
        );
    }
}
