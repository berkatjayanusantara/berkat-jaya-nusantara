<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LaporanPiutangTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Laporan Piutang',
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
            'nama_customer' => 'Customer Laporan Piutang',
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => 'Alamat customer laporan piutang',
            'kategori_customer' => 'Retail',
            'status_aktif' => true,
        ], $override));

        return $customer;
    }

    private function penjualanKredit(Customer $customer, User $user, array $override = []): Penjualan
    {
        /** @var Penjualan $penjualan */
        $penjualan = Penjualan::factory()->kredit()->create(array_merge([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'subtotal' => 100000,
            'persentase_pajak' => 0,
            'nilai_pajak' => 0,
            'pajak_ditambahkan' => false,
            'total_akhir' => 100000,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'catatan' => 'Penjualan kredit laporan piutang',
            'dibuat_oleh' => $user->id_user,
        ], $override));

        return $penjualan;
    }

    private function piutang(Customer $customer, Penjualan $penjualan, array $override = []): Piutang
    {
        /** @var Piutang $piutang */
        $piutang = Piutang::factory()->create(array_merge([
            'id_penjualan' => $penjualan->id_penjualan,
            'nomor_invoice' => $penjualan->nomor_invoice,
            'id_customer' => $customer->id_customer,
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'catatan' => 'Piutang laporan testing',
        ], $override));

        return $piutang;
    }

    private function piutangIdsFromResponse(TestResponse $response): array
    {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $response->viewData('piutang');

        return $paginator
            ->getCollection()
            ->pluck('id_piutang')
            ->values()
            ->all();
    }

    private function assertResponseHasPiutang(TestResponse $response, Piutang $piutang): void
    {
        $this->assertContains(
            $piutang->id_piutang,
            $this->piutangIdsFromResponse($response)
        );
    }

    private function assertResponseDoesNotHavePiutang(TestResponse $response, Piutang $piutang): void
    {
        $this->assertNotContains(
            $piutang->id_piutang,
            $this->piutangIdsFromResponse($response)
        );
    }

    private function piutangCollectionFromExcelResponse(TestResponse $response): Collection
    {
        /** @var Collection $piutang */
        $piutang = $response->viewData('piutang');

        return $piutang;
    }

    public function test_guest_can_not_access_laporan_piutang_page(): void
    {
        $response = $this->get('/laporan/piutang');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_open_laporan_piutang_page(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->get('/laporan/piutang');

        $response->assertStatus(200);
        $response->assertViewHas('piutang');
        $response->assertViewHas('customers');
        $response->assertViewHas('totalData');
        $response->assertViewHas('totalPiutang');
        $response->assertViewHas('totalDibayar');
        $response->assertViewHas('totalSisa');
        $response->assertViewHas('totalBelumLunas');
        $response->assertViewHas('totalSebagian');
        $response->assertViewHas('totalLunas');
        $response->assertViewHas('totalLewatJatuhTempo');
    }

    public function test_laporan_piutang_displays_piutang_data(): void
    {
        $user = $this->admin();

        $customer = $this->customer([
            'nama_customer' => 'Customer Tampil Piutang',
        ]);

        $penjualan = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-TAMPIL-PIUTANG-0001',
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'nomor_invoice' => 'INV-TAMPIL-PIUTANG-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutang);
    }

    public function test_laporan_piutang_summary_totals_are_correct(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanSatu = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0001',
        ]);

        $this->piutang($customer, $penjualanSatu, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0001',
            'total_piutang' => 100000,
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'status_piutang' => 'belum_lunas',
        ]);

        $penjualanDua = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0002',
        ]);

        $this->piutang($customer, $penjualanDua, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0002',
            'total_piutang' => 200000,
            'total_dibayar' => 50000,
            'sisa_piutang' => 150000,
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status_piutang' => 'sebagian_dibayar',
        ]);

        $penjualanTiga = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0003',
        ]);

        $this->piutang($customer, $penjualanTiga, [
            'nomor_invoice' => 'INV-TOTAL-PIUTANG-0003',
            'total_piutang' => 300000,
            'total_dibayar' => 300000,
            'sisa_piutang' => 0,
            'tanggal_jatuh_tempo' => now()->subDays(20)->toDateString(),
            'status_piutang' => 'lunas',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->viewData('totalData'));
        $this->assertEquals(600000, $response->viewData('totalPiutang'));
        $this->assertEquals(350000, $response->viewData('totalDibayar'));
        $this->assertEquals(250000, $response->viewData('totalSisa'));
        $this->assertEquals(1, $response->viewData('totalBelumLunas'));
        $this->assertEquals(1, $response->viewData('totalSebagian'));
        $this->assertEquals(1, $response->viewData('totalLunas'));
        $this->assertEquals(1, $response->viewData('totalLewatJatuhTempo'));
    }

    public function test_admin_can_filter_laporan_piutang_by_tanggal_awal(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLama = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-JT-LAMA-0001',
        ]);

        $piutangLama = $this->piutang($customer, $penjualanLama, [
            'nomor_invoice' => 'INV-JT-LAMA-0001',
            'tanggal_jatuh_tempo' => now()->subDays(10)->toDateString(),
        ]);

        $penjualanBaru = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-JT-BARU-0001',
        ]);

        $piutangBaru = $this->piutang($customer, $penjualanBaru, [
            'nomor_invoice' => 'INV-JT-BARU-0001',
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
        ]);

        $tanggalAwal = now()->toDateString();

        $response = $this->actingAs($user)->get('/laporan/piutang?tanggal_awal=' . $tanggalAwal);

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangBaru);
        $this->assertResponseDoesNotHavePiutang($response, $piutangLama);
    }

    public function test_admin_can_filter_laporan_piutang_by_tanggal_akhir(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLama = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-JT-LAMA-0002',
        ]);

        $piutangLama = $this->piutang($customer, $penjualanLama, [
            'nomor_invoice' => 'INV-JT-LAMA-0002',
            'tanggal_jatuh_tempo' => now()->subDays(10)->toDateString(),
        ]);

        $penjualanBaru = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-JT-BARU-0002',
        ]);

        $piutangBaru = $this->piutang($customer, $penjualanBaru, [
            'nomor_invoice' => 'INV-JT-BARU-0002',
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
        ]);

        $tanggalAkhir = now()->toDateString();

        $response = $this->actingAs($user)->get('/laporan/piutang?tanggal_akhir=' . $tanggalAkhir);

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangLama);
        $this->assertResponseDoesNotHavePiutang($response, $piutangBaru);
    }

    public function test_admin_can_filter_laporan_piutang_by_customer(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer Piutang Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer Piutang Dua',
        ]);

        $penjualanSatu = $this->penjualanKredit($customerSatu, $user, [
            'nomor_invoice' => 'INV-CUS-PIUTANG-0001',
        ]);

        $piutangSatu = $this->piutang($customerSatu, $penjualanSatu, [
            'nomor_invoice' => 'INV-CUS-PIUTANG-0001',
        ]);

        $penjualanDua = $this->penjualanKredit($customerDua, $user, [
            'nomor_invoice' => 'INV-CUS-PIUTANG-0002',
        ]);

        $piutangDua = $this->piutang($customerDua, $penjualanDua, [
            'nomor_invoice' => 'INV-CUS-PIUTANG-0002',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?id_customer=' . $customerSatu->id_customer);

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangSatu);
        $this->assertResponseDoesNotHavePiutang($response, $piutangDua);
    }

    public function test_admin_can_filter_laporan_piutang_by_status_belum_lunas(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanBelum = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0001',
        ]);

        $piutangBelum = $this->piutang($customer, $penjualanBelum, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0001',
            'status_piutang' => 'belum_lunas',
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
        ]);

        $penjualanLunas = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-PIUTANG-0001',
        ]);

        $piutangLunas = $this->piutang($customer, $penjualanLunas, [
            'nomor_invoice' => 'INV-LUNAS-PIUTANG-0001',
            'status_piutang' => 'lunas',
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?status_piutang=belum_lunas');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangBelum);
        $this->assertResponseDoesNotHavePiutang($response, $piutangLunas);
    }

    public function test_admin_can_filter_laporan_piutang_by_status_sebagian_dibayar(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanSebagian = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-SEBAGIAN-PIUTANG-0001',
        ]);

        $piutangSebagian = $this->piutang($customer, $penjualanSebagian, [
            'nomor_invoice' => 'INV-SEBAGIAN-PIUTANG-0001',
            'status_piutang' => 'sebagian_dibayar',
            'total_dibayar' => 40000,
            'sisa_piutang' => 60000,
        ]);

        $penjualanBelum = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0002',
        ]);

        $piutangBelum = $this->piutang($customer, $penjualanBelum, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0002',
            'status_piutang' => 'belum_lunas',
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?status_piutang=sebagian_dibayar');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangSebagian);
        $this->assertResponseDoesNotHavePiutang($response, $piutangBelum);
    }

    public function test_admin_can_filter_laporan_piutang_by_status_lunas(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLunas = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-PIUTANG-0002',
        ]);

        $piutangLunas = $this->piutang($customer, $penjualanLunas, [
            'nomor_invoice' => 'INV-LUNAS-PIUTANG-0002',
            'status_piutang' => 'lunas',
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
        ]);

        $penjualanBelum = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0003',
        ]);

        $piutangBelum = $this->piutang($customer, $penjualanBelum, [
            'nomor_invoice' => 'INV-BELUM-PIUTANG-0003',
            'status_piutang' => 'belum_lunas',
            'total_dibayar' => 0,
            'sisa_piutang' => 100000,
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?status_piutang=lunas');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangLunas);
        $this->assertResponseDoesNotHavePiutang($response, $piutangBelum);
    }

    public function test_admin_can_filter_laporan_piutang_by_jatuh_tempo_lewat(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LEWAT-PIUTANG-0001',
        ]);

        $piutangLewat = $this->piutang($customer, $penjualanLewat, [
            'nomor_invoice' => 'INV-LEWAT-PIUTANG-0001',
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'sisa_piutang' => 100000,
        ]);

        $penjualanBelumLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-LEWAT-PIUTANG-0001',
        ]);

        $piutangBelumLewat = $this->piutang($customer, $penjualanBelumLewat, [
            'nomor_invoice' => 'INV-BELUM-LEWAT-PIUTANG-0001',
            'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'sisa_piutang' => 100000,
        ]);

        $penjualanLunasLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-LEWAT-PIUTANG-0001',
        ]);

        $piutangLunasLewat = $this->piutang($customer, $penjualanLunasLewat, [
            'nomor_invoice' => 'INV-LUNAS-LEWAT-PIUTANG-0001',
            'tanggal_jatuh_tempo' => now()->subDays(10)->toDateString(),
            'status_piutang' => 'lunas',
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?jatuh_tempo=lewat');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangLewat);
        $this->assertResponseDoesNotHavePiutang($response, $piutangBelumLewat);
        $this->assertResponseDoesNotHavePiutang($response, $piutangLunasLewat);
    }

    public function test_admin_can_filter_laporan_piutang_by_jatuh_tempo_belum(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanBelumLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-BELUM-LEWAT-PIUTANG-0002',
        ]);

        $piutangBelumLewat = $this->piutang($customer, $penjualanBelumLewat, [
            'nomor_invoice' => 'INV-BELUM-LEWAT-PIUTANG-0002',
            'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'sisa_piutang' => 100000,
        ]);

        $penjualanLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LEWAT-PIUTANG-0002',
        ]);

        $piutangLewat = $this->piutang($customer, $penjualanLewat, [
            'nomor_invoice' => 'INV-LEWAT-PIUTANG-0002',
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'status_piutang' => 'belum_lunas',
            'sisa_piutang' => 100000,
        ]);

        $penjualanLunasBelumLewat = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LUNAS-BELUM-PIUTANG-0001',
        ]);

        $piutangLunasBelumLewat = $this->piutang($customer, $penjualanLunasBelumLewat, [
            'nomor_invoice' => 'INV-LUNAS-BELUM-PIUTANG-0001',
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status_piutang' => 'lunas',
            'total_dibayar' => 100000,
            'sisa_piutang' => 0,
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?jatuh_tempo=belum');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangBelumLewat);
        $this->assertResponseDoesNotHavePiutang($response, $piutangLewat);
        $this->assertResponseDoesNotHavePiutang($response, $piutangLunasBelumLewat);
    }

    public function test_admin_can_filter_laporan_piutang_by_search_invoice(): void
    {
        $user = $this->admin();
        $customer = $this->customer();

        $penjualanSatu = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-CARI-PIUTANG-0001',
        ]);

        $piutangSatu = $this->piutang($customer, $penjualanSatu, [
            'nomor_invoice' => 'INV-CARI-PIUTANG-0001',
        ]);

        $penjualanDua = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-LAIN-PIUTANG-0002',
        ]);

        $piutangDua = $this->piutang($customer, $penjualanDua, [
            'nomor_invoice' => 'INV-LAIN-PIUTANG-0002',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?search=INV-CARI-PIUTANG-0001');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangSatu);
        $this->assertResponseDoesNotHavePiutang($response, $piutangDua);
    }

    public function test_admin_can_filter_laporan_piutang_by_search_customer_name(): void
    {
        $user = $this->admin();

        $customerSemen = $this->customer([
            'nama_customer' => 'Customer Semen Piutang',
        ]);

        $customerPasir = $this->customer([
            'nama_customer' => 'Customer Pasir Piutang',
        ]);

        $penjualanSemen = $this->penjualanKredit($customerSemen, $user, [
            'nomor_invoice' => 'INV-SEMEN-PIUTANG-0001',
        ]);

        $piutangSemen = $this->piutang($customerSemen, $penjualanSemen, [
            'nomor_invoice' => 'INV-SEMEN-PIUTANG-0001',
        ]);

        $penjualanPasir = $this->penjualanKredit($customerPasir, $user, [
            'nomor_invoice' => 'INV-PASIR-PIUTANG-0001',
        ]);

        $piutangPasir = $this->piutang($customerPasir, $penjualanPasir, [
            'nomor_invoice' => 'INV-PASIR-PIUTANG-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?search=Semen');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangSemen);
        $this->assertResponseDoesNotHavePiutang($response, $piutangPasir);
    }

    public function test_admin_can_filter_laporan_piutang_by_search_customer_phone(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer Telepon Satu',
            'nomor_telepon' => '081111111111',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer Telepon Dua',
            'nomor_telepon' => '082222222222',
        ]);

        $penjualanSatu = $this->penjualanKredit($customerSatu, $user, [
            'nomor_invoice' => 'INV-TELP-PIUTANG-0001',
        ]);

        $piutangSatu = $this->piutang($customerSatu, $penjualanSatu, [
            'nomor_invoice' => 'INV-TELP-PIUTANG-0001',
        ]);

        $penjualanDua = $this->penjualanKredit($customerDua, $user, [
            'nomor_invoice' => 'INV-TELP-PIUTANG-0002',
        ]);

        $piutangDua = $this->piutang($customerDua, $penjualanDua, [
            'nomor_invoice' => 'INV-TELP-PIUTANG-0002',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang?search=081111111111');

        $response->assertStatus(200);
        $this->assertResponseHasPiutang($response, $piutangSatu);
        $this->assertResponseDoesNotHavePiutang($response, $piutangDua);
    }

    public function test_admin_can_export_laporan_piutang_excel(): void
    {
        $user = $this->admin();

        $customer = $this->customer();

        $penjualan = $this->penjualanKredit($customer, $user, [
            'nomor_invoice' => 'INV-EXPORT-PIUTANG-0001',
        ]);

        $piutang = $this->piutang($customer, $penjualan, [
            'nomor_invoice' => 'INV-EXPORT-PIUTANG-0001',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang/export-excel');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $piutangExport = $this->piutangCollectionFromExcelResponse($response);

        $this->assertTrue(
            $piutangExport->pluck('id_piutang')->contains($piutang->id_piutang)
        );
    }

    public function test_admin_can_export_laporan_piutang_excel_with_filter(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer Export Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer Export Dua',
        ]);

        $penjualanSatu = $this->penjualanKredit($customerSatu, $user, [
            'nomor_invoice' => 'INV-EXPORT-SATU-PIUTANG',
        ]);

        $piutangSatu = $this->piutang($customerSatu, $penjualanSatu, [
            'nomor_invoice' => 'INV-EXPORT-SATU-PIUTANG',
        ]);

        $penjualanDua = $this->penjualanKredit($customerDua, $user, [
            'nomor_invoice' => 'INV-EXPORT-DUA-PIUTANG',
        ]);

        $piutangDua = $this->piutang($customerDua, $penjualanDua, [
            'nomor_invoice' => 'INV-EXPORT-DUA-PIUTANG',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang/export-excel?search=SATU');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');

        $piutangExport = $this->piutangCollectionFromExcelResponse($response);

        $this->assertTrue(
            $piutangExport->pluck('id_piutang')->contains($piutangSatu->id_piutang)
        );

        $this->assertFalse(
            $piutangExport->pluck('id_piutang')->contains($piutangDua->id_piutang)
        );
    }
}
