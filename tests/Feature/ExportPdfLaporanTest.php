<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportPdfLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $override = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'nama_user' => 'Admin Export PDF',
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
            'nama_customer' => 'Customer Export PDF',
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => 'Alamat customer export PDF',
            'kategori_customer' => 'Retail',
            'status_aktif' => true,
        ], $override));

        return $customer;
    }

    private function supplier(array $override = []): Supplier
    {
        /** @var Supplier $supplier */
        $supplier = Supplier::factory()->create(array_merge([
            'kode_supplier' => 'SUP-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_supplier' => 'Supplier Export PDF',
            'nomor_telepon' => fake()->unique()->numerify('08##########'),
            'alamat' => 'Alamat supplier export PDF',
            'status_aktif' => true,
        ], $override));

        return $supplier;
    }

    private function barang(array $override = []): Barang
    {
        /** @var Barang $barang */
        $barang = Barang::factory()->create(array_merge([
            'kode_barang' => 'BRG-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nama_barang' => 'Barang Export PDF',
            'satuan' => 'pcs',
            'stok_saat_ini' => 10,
            'harga_beli_terakhir' => 5000,
            'harga_jual_default' => 8000,
            'status_aktif' => true,
        ], $override));

        return $barang;
    }

    private function penjualan(Customer $customer, User $user, array $override = []): Penjualan
    {
        /** @var Penjualan $penjualan */
        $penjualan = Penjualan::factory()->create(array_merge([
            'nomor_invoice' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'tanggal_penjualan' => now()->toDateString(),
            'id_customer' => $customer->id_customer,
            'subtotal' => 100000,
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => true,
            'total_akhir' => 111000,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'tanggal_jatuh_tempo' => null,
            'catatan' => 'Penjualan export PDF testing',
            'dibuat_oleh' => $user->id_user,
        ], $override));

        return $penjualan;
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
            'persentase_pajak' => 11,
            'nilai_pajak' => 11000,
            'pajak_ditambahkan' => true,
            'total_akhir' => 111000,
            'catatan' => 'Pembelian export PDF testing',
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
            'catatan' => 'Piutang export PDF testing',
        ], $override));

        return $piutang;
    }

    private function assertPdfDownloadResponse($response, string $expectedFileNamePart): void
    {
        $response->assertStatus(200);

        $contentType = $response->headers->get('Content-Type');
        $contentDisposition = $response->headers->get('Content-Disposition');

        $this->assertNotNull($contentType);
        $this->assertStringContainsString('application/pdf', $contentType);

        $this->assertNotNull($contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString($expectedFileNamePart, $contentDisposition);

        $this->assertNotEmpty($response->getContent());
    }

    public function test_guest_can_not_export_laporan_penjualan_pdf(): void
    {
        $response = $this->get('/laporan/penjualan/export-pdf');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_laporan_penjualan_pdf(): void
    {
        $user = $this->admin();

        $customer = $this->customer([
            'nama_customer' => 'Customer PDF Penjualan',
        ]);

        $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-PDF-PENJUALAN-0001',
            'tanggal_penjualan' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan/export-pdf');

        $this->assertPdfDownloadResponse($response, 'Laporan-Penjualan');
    }

    public function test_admin_can_export_laporan_penjualan_pdf_with_filter(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer PDF Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer PDF Dua',
        ]);

        $this->penjualan($customerSatu, $user, [
            'nomor_invoice' => 'INV-PDF-SATU',
        ]);

        $this->penjualan($customerDua, $user, [
            'nomor_invoice' => 'INV-PDF-DUA',
        ]);

        $response = $this->actingAs($user)->get('/laporan/penjualan/export-pdf?search=SATU');

        $this->assertPdfDownloadResponse($response, 'Laporan-Penjualan');
    }

    public function test_guest_can_not_export_laporan_pembelian_pdf(): void
    {
        $response = $this->get('/laporan/pembelian/export-pdf');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_laporan_pembelian_pdf(): void
    {
        $user = $this->admin();

        $supplier = $this->supplier([
            'nama_supplier' => 'Supplier PDF Pembelian',
        ]);

        $barang = $this->barang([
            'nama_barang' => 'Barang PDF Pembelian',
        ]);

        $pembelian = $this->pembelian($supplier, $user, [
            'nomor_pembelian' => 'PB-PDF-PEMBELIAN-0001',
            'tanggal_pembelian' => now()->toDateString(),
        ]);

        $this->detailPembelian($pembelian, $barang);

        $response = $this->actingAs($user)->get('/laporan/pembelian/export-pdf');

        $this->assertPdfDownloadResponse($response, 'Laporan-Pembelian');
    }

    public function test_admin_can_export_laporan_pembelian_pdf_with_filter(): void
    {
        $user = $this->admin();

        $supplierSatu = $this->supplier([
            'nama_supplier' => 'Supplier PDF Satu',
        ]);

        $supplierDua = $this->supplier([
            'nama_supplier' => 'Supplier PDF Dua',
        ]);

        $barang = $this->barang();

        $pembelianSatu = $this->pembelian($supplierSatu, $user, [
            'nomor_pembelian' => 'PB-PDF-SATU',
        ]);

        $this->detailPembelian($pembelianSatu, $barang);

        $pembelianDua = $this->pembelian($supplierDua, $user, [
            'nomor_pembelian' => 'PB-PDF-DUA',
        ]);

        $this->detailPembelian($pembelianDua, $barang);

        $response = $this->actingAs($user)->get('/laporan/pembelian/export-pdf?search=SATU');

        $this->assertPdfDownloadResponse($response, 'Laporan-Pembelian');
    }

    public function test_guest_can_not_export_laporan_piutang_pdf(): void
    {
        $response = $this->get('/laporan/piutang/export-pdf');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_laporan_piutang_pdf(): void
    {
        $user = $this->admin();

        $customer = $this->customer([
            'nama_customer' => 'Customer PDF Piutang',
        ]);

        $penjualan = $this->penjualan($customer, $user, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-0001',
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $this->piutang($customer, $penjualan, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-0001',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
            'status_piutang' => 'belum_lunas',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang/export-pdf');

        $this->assertPdfDownloadResponse($response, 'Laporan-Piutang');
    }

    public function test_admin_can_export_laporan_piutang_pdf_with_filter(): void
    {
        $user = $this->admin();

        $customerSatu = $this->customer([
            'nama_customer' => 'Customer PDF Piutang Satu',
        ]);

        $customerDua = $this->customer([
            'nama_customer' => 'Customer PDF Piutang Dua',
        ]);

        $penjualanSatu = $this->penjualan($customerSatu, $user, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-SATU',
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $this->piutang($customerSatu, $penjualanSatu, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-SATU',
        ]);

        $penjualanDua = $this->penjualan($customerDua, $user, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-DUA',
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
            'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
        ]);

        $this->piutang($customerDua, $penjualanDua, [
            'nomor_invoice' => 'INV-PDF-PIUTANG-DUA',
        ]);

        $response = $this->actingAs($user)->get('/laporan/piutang/export-pdf?search=SATU');

        $this->assertPdfDownloadResponse($response, 'Laporan-Piutang');
    }

    public function test_guest_can_not_export_laporan_stok_barang_pdf(): void
    {
        $response = $this->get('/laporan/stok-barang/export-pdf');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_laporan_stok_barang_pdf(): void
    {
        $user = $this->admin();

        $this->barang([
            'kode_barang' => 'BRG-PDF-0001',
            'nama_barang' => 'Barang PDF Stok',
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang/export-pdf');

        $this->assertPdfDownloadResponse($response, 'Laporan-Stok-Barang');
    }

    public function test_admin_can_export_laporan_stok_barang_pdf_with_filter(): void
    {
        $user = $this->admin();

        $this->barang([
            'kode_barang' => 'BRG-PDF-0001',
            'nama_barang' => 'Semen PDF Stok',
            'stok_saat_ini' => 10,
        ]);

        $this->barang([
            'kode_barang' => 'BRG-PDF-0002',
            'nama_barang' => 'Pasir PDF Stok',
            'stok_saat_ini' => 10,
        ]);

        $response = $this->actingAs($user)->get('/laporan/stok-barang/export-pdf?search=Semen');

        $this->assertPdfDownloadResponse($response, 'Laporan-Stok-Barang');
    }
}
