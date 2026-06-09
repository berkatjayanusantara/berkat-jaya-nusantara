<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Laporan Penjualan',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ], $override));

        return $user;
    }

    private function customer(array $override = []): Customer
    {
        /** @var Customer $customer */
        $customer = Customer::factory()->create(array_merge([
            'kode_customer' => 'CUS-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_customer' => 'Customer Laporan Penjualan',
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => 'Alamat customer laporan',
            'kategori_customer' => 'Retail',
            'status_aktif' => true,
        ], $override));

        return $customer;
    }

    private function penjualan(Customer $customer, User $user, array $override = []): Penjualan
    {
        /** @var Penjualan $penjualan */
        $penjualan = Penjualan::factory()->create(array_merge([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Penjualan laporan testing',
            'dibuat_oleh' => $user->id_user,
        ], $override));

        return $penjualan;
    }

    private function penjualanIdsFromResponse(TestResponse $response): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('penjualan');

        return $paginator
            ->getCollection()
            ->pluck('id_penjualan')
            ->values()
            ->all();
    }

    private function assertResponseHasPenjualan(TestResponse $response, Penjualan $penjualan): void
    {
        $this->assertContains(
            $penjualan->id_penjualan,
            $this->penjualanIdsFromResponse($response)
        );
    }

    private function assertResponseDoesNotHavePenjualan(TestResponse $response, Penjualan $penjualan): void
    {
        $this->assertNotContains(
            $penjualan->id_penjualan,
            $this->penjualanIdsFromResponse($response)
        );
    }

    private function penjualanCollectionFromExcelResponse(TestResponse $response): Collection
    {
        /** @var Collection $penjualan */
        $penjualan = $response->viewData('penjualan');

        return $penjualan;
    }

    public function test_guest_can_not_access_laporan_penjualan_page(): void
    {
        $response = $this->get('/laporan/penjualan');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_laporan_penjualan_page(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->get('/laporan/penjualan');

        $response->assertStatus(200);
        $response->assertViewHas('penjualan');
        $response->assertViewHas('customers');
        $response->assertViewHas('totalTransaksi');
        $response->assertViewHas('totalSubtotal');
        $response->assertViewHas('totalPajak');
        $response->assertViewHas('totalAkhir');
    }

    public function test_laporan_penjualan_displays_penjualan_data(): void
    {
        $user = $this->admin();

        $customer = $this->customer([
            'nama_customer' => 'Customer Tampil Penjualan',
        ]);

        $penjualan = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-TAMPIL-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualan);
    }

    public function test_laporan_penjualan_summary_totals_are_correct(): void
    {
        $user = $this->admin();

        $customer = $this->customer();

        $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-TOTAL-0001',
            'subtotal' => 100000,
            'nilai_pajak' => 11000,
            'total_akhir' => 111000,
        ]);

        $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-TOTAL-0002',
            'subtotal' => 200000,
            'nilai_pajak' => 22000,
            'total_akhir' => 222000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->viewData('totalTransaksi'));
        $this->assertEquals(300000, $response->viewData('totalSubtotal'));
        $this->assertEquals(33000, $response->viewData('totalPajak'));
        $this->assertEquals(333000, $response->viewData('totalAkhir'));
    }

    public function test_admin_can_filter_laporan_penjualan_by_tanggal_awal(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLama = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-LAMA-0001',
            'tanggal_penjualan' => now()->subDays(10)->toDateString(),
        ]);

        $penjualanBaru = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-BARU-0001',
            'tanggal_penjualan' => now()->toDateString(),
        ]);

        $tanggalAwal = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/laporan/penjualan?tanggal_awal=' . $tanggalAwal);

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanBaru);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanLama);
    }

    public function test_admin_can_filter_laporan_penjualan_by_tanggal_akhir(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLama = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-LAMA-0002',
            'tanggal_penjualan' => now()->subDays(10)->toDateString(),
        ]);

        $penjualanBaru = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-BARU-0002',
            'tanggal_penjualan' => now()->toDateString(),
        ]);

        $tanggalAkhir = now()->subDays(1)->toDateString();

        $response = $this->actingAs($user)->get('/laporan/penjualan?tanggal_akhir=' . $tanggalAkhir);

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanLama);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanBaru);
    }

    public function test_admin_can_filter_laporan_penjualan_by_customer(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer Dua',
        ]);

        $penjualanSatu = $this->penjualan($customerSatu, $user, [
            'nomor_invoice' => 'INV-CUS-0001',
        ]);

        $penjualanDua = $this->penjualan($customerDua, $user, [
            'nomor_invoice' => 'INV-CUS-0002',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?id_customer=' . $customerSatu->id_customer);

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanSatu);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanDua);
    }

    public function test_admin_can_filter_laporan_penjualan_by_metode_tunai(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanTunai = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-TUNAI-0001',
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
        ]);

        $penjualanKredit = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-KREDIT-0001',
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?metode_pembayaran=tunai');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanTunai);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanKredit);
    }

    public function test_admin_can_filter_laporan_penjualan_by_metode_kredit(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanTunai = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-TUNAI-0002',
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
        ]);

        $penjualanKredit = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-KREDIT-0002',
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?metode_pembayaran=kredit');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanKredit);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanTunai);
    }

    public function test_admin_can_filter_laporan_penjualan_by_status_lunas(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLunas = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-0001',
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'tunai',
        ]);

        $penjualanBelumLunas = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-0001',
            'status_pembayaran' => 'belum_lunas',
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?status_pembayaran=lunas');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanLunas);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanBelumLunas);
    }

    public function test_admin_can_filter_laporan_penjualan_by_status_belum_lunas(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLunas = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-0002',
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'tunai',
        ]);

        $penjualanBelumLunas = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-0002',
            'status_pembayaran' => 'belum_lunas',
            'metode_pembayaran' => 'kredit',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?status_pembayaran=belum_lunas');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanBelumLunas);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanLunas);
    }

    public function test_admin_can_filter_laporan_penjualan_by_search_invoice(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanSatu = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-CARI-0001',
        ]);

        $penjualanDua = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-LAIN-0002',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?search=INV-CARI-0001');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanSatu);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanDua);
    }

    public function test_admin_can_filter_laporan_penjualan_by_search_customer_name(): void
    {
        $user = $this->admin();

        $customerSemen = $this->customer([
            'nama_customer' => 'Customer Semen',
        ]);

        $customerPasir = $this->customer([
            'nama_customer' => 'Customer Pasir',
        ]);

        $penjualanSemen = $this->penjualan($customerSemen, $user, [
            'nomor_invoice' => 'INV-SEMEN-0001',
        ]);

        $penjualanPasir = $this->penjualan($customerPasir, $user, [
            'nomor_invoice' => 'INV-PASIR-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan?search=Semen');

        $response->assertStatus(200);
        $this->assertResponseHasPenjualan($response, $penjualanSemen);
        $this->assertResponseDoesNotHavePenjualan($response, $penjualanPasir);
    }

    public function test_admin_can_export_laporan_penjualan_excel(): void
    {
        $user = $this->admin();

        $customer = $this->customer();

        $penjualan = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-EXPORT-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $penjualanExport = $this->penjualanCollectionFromExcelResponse($response);

        $this->assertTrue(
            $penjualanExport->pluck('id_penjualan')->contains($penjualan->id_penjualan)
        );
    }

    public function test_admin_can_export_laporan_penjualan_excel_with_filter(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer Export Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer Export Dua',
        ]);

        $penjualanSatu = $this->penjualan($customerSatu, $user, [
            'nomor_invoice' => 'INV-EXPORT-SATU',
        ]);

        $penjualanDua = $this->penjualan($customerDua, $user, [
            'nomor_invoice' => 'INV-EXPORT-DUA',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan/export-excel?search=SATU');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $penjualanExport = $this->penjualanCollectionFromExcelResponse($response);

        $this->assertTrue(
            $penjualanExport->pluck('id_penjualan')->contains($penjualanSatu->id_penjualan)
        );

        $this->assertFalse(
            $penjualanExport->pluck('id_penjualan')->contains($penjualanDua->id_penjualan)
        );
    }
}
