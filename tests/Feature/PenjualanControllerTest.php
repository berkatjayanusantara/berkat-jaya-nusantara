<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Buat payload POST penjualan yang valid.
     * Semua nilai kalkulasi (subtotal, PPN, total) dikontrol lewat parameter ini
     * sehingga assertion bisa memeriksa output DB tanpa menyentuh rumus controller.
     */
    private function payloadPenjualan(array $overrides = []): array
    {
        return array_merge([
            'tanggal_penjualan'        => '2024-01-15',
            'nomor_invoice'            => 'INV-TEST-001',
            'id_customer'              => null,          // diisi caller
            'metode_pembayaran'        => 'tunai',
            'mode_ppn'                 => 'tanpa_ppn',
            'jenis_penyesuaian_total'  => 'tidak_ada',
            'nominal_penyesuaian_total' => 0,
            'id_barang'                => [],            // diisi caller
            'jumlah'                   => [],
            'harga_jual'               => [],
            'diskon_nominal'           => [],
        ], $overrides);
    }

    // =========================================================================
    // 1. Halaman listing & form
    // =========================================================================

    public function test_index_displays_penjualan_list(): void
    {
        $this->actingAs($this->user)
            ->get(route('penjualan.index'))
            ->assertOk()
            ->assertViewIs('penjualan.index')
            ->assertViewHas('penjualan');
    }

    public function test_create_displays_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('penjualan.create'))
            ->assertOk()
            ->assertViewIs('penjualan.create')
            ->assertViewHas('customers')
            ->assertViewHas('barang');
    }

    // =========================================================================
    // 2. Penjualan TUNAI — non-PPN
    // Dua item: masing-masing 2 pcs @ Rp 50.000 dan 3 pcs @ Rp 30.000
    // Subtotal = (2×50.000) + (3×30.000) = 100.000 + 90.000 = 190.000
    // Mode tanpa_ppn → total_akhir = 190.000
    // =========================================================================

    public function test_store_penjualan_tunai_kurangi_stok_dan_simpan_total(): void
    {
        $customer = Customer::factory()->create();

        $barang1 = Barang::factory()->create([
            'stok_saat_ini' => 10,
            'jenis_ppn'     => 'non_ppn',
        ]);
        $barang2 = Barang::factory()->create([
            'stok_saat_ini' => 20,
            'jenis_ppn'     => 'non_ppn',
        ]);

        $payload = $this->payloadPenjualan([
            'id_customer'   => $customer->id_customer,
            'id_barang'     => [$barang1->id_barang, $barang2->id_barang],
            'jumlah'        => [2, 3],
            'harga_jual'    => [50000, 30000],
            'diskon_nominal' => [0, 0],
        ]);

        $this->actingAs($this->user)
            ->post(route('penjualan.store'), $payload)
            ->assertRedirect(route('penjualan.index'))
            ->assertSessionHas('success');

        // -- Penjualan tersimpan dengan total yang benar --
        $this->assertDatabaseHas('penjualan', [
            'id_customer'       => $customer->id_customer,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'lunas',
            'subtotal'          => 190000.00,
            'nilai_pajak'       => 0.00,
            'total_akhir'       => 190000.00,
        ]);

        // -- Detail tersimpan --
        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang'  => $barang1->id_barang,
            'jumlah'     => 2,
            'harga_jual' => 50000,
            'subtotal'   => 100000,
        ]);
        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang'  => $barang2->id_barang,
            'jumlah'     => 3,
            'harga_jual' => 30000,
            'subtotal'   => 90000,
        ]);

        // -- Stok BERKURANG sesuai jumlah terjual --
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang1->id_barang,
            'stok_saat_ini' => 8,   // 10 - 2
        ]);
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang2->id_barang,
            'stok_saat_ini' => 17,  // 20 - 3
        ]);

        // -- Riwayat stok keluar tercatat --
        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang'       => $barang1->id_barang,
            'jenis_pergerakan' => 'keluar',
            'jumlah'          => 2,
        ]);

        // -- Tunai tidak membuat piutang --
        $this->assertDatabaseCount('piutang', 0);
    }

    // =========================================================================
    // 3. Penjualan TUNAI — PPN 11% EXCLUDE (harga belum termasuk PPN)
    // 1 item: 10 pcs @ Rp 100.000, subtotal = 1.000.000
    // Mode exclude, jenis_ppn = ppn_normal
    // DPP = 1.000.000, PPN = 1.000.000 × 0.11 = 110.000
    // total_akhir = 1.000.000 + 110.000 = 1.110.000
    // =========================================================================

    public function test_store_penjualan_ppn_exclude_hitung_total_dengan_benar(): void
    {
        $customer = Customer::factory()->create();
        $barang   = Barang::factory()->create([
            'stok_saat_ini' => 50,
            'jenis_ppn'     => 'ppn_normal',
        ]);

        $payload = $this->payloadPenjualan([
            'id_customer'  => $customer->id_customer,
            'mode_ppn'     => 'exclude',
            'id_barang'    => [$barang->id_barang],
            'jumlah'       => [10],
            'harga_jual'   => [100000],
            'diskon_nominal' => [0],
        ]);

        $this->actingAs($this->user)
            ->post(route('penjualan.store'), $payload)
            ->assertRedirect(route('penjualan.index'))
            ->assertSessionHas('success');

        $penjualan = Penjualan::latest('id_penjualan')->first();

        $this->assertEquals(1000000.00, (float) $penjualan->subtotal);
        $this->assertEquals(110000.00,  (float) $penjualan->nilai_pajak);
        $this->assertEquals(1110000.00, (float) $penjualan->total_akhir);

        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang->id_barang,
            'stok_saat_ini' => 40, // 50 - 10
        ]);
    }

    // =========================================================================
    // 4. Penjualan KREDIT — wajib buat record Piutang
    // =========================================================================

    public function test_store_penjualan_kredit_buat_piutang(): void
    {
        $customer = Customer::factory()->create();
        $barang   = Barang::factory()->create([
            'stok_saat_ini' => 10,
            'jenis_ppn'     => 'non_ppn',
        ]);

        $payload = $this->payloadPenjualan([
            'id_customer'         => $customer->id_customer,
            'metode_pembayaran'   => 'kredit',
            'tanggal_jatuh_tempo' => '2024-02-15',
            'id_barang'           => [$barang->id_barang],
            'jumlah'              => [5],
            'harga_jual'          => [200000],
            'diskon_nominal'      => [0],
        ]);

        $this->actingAs($this->user)
            ->post(route('penjualan.store'), $payload)
            ->assertRedirect(route('penjualan.index'));

        // -- Status penjualan = belum_lunas --
        $this->assertDatabaseHas('penjualan', [
            'id_customer'       => $customer->id_customer,
            'metode_pembayaran' => 'kredit',
            'status_pembayaran' => 'belum_lunas',
        ]);

        // -- Piutang dibuat dengan nilai = total_akhir penjualan --
        $penjualan = Penjualan::latest('id_penjualan')->first();
        $this->assertDatabaseHas('piutang', [
            'id_penjualan'  => $penjualan->id_penjualan,
            'id_customer'   => $customer->id_customer,
            'total_piutang' => 1000000.00,
            'total_dibayar' => 0.00,
            'sisa_piutang'  => 1000000.00,
            'status_piutang' => 'belum_lunas',
        ]);
    }

    // =========================================================================
    // 5. Stok tidak cukup → tolak, rollback, stok tidak berubah
    // =========================================================================

    public function test_store_ditolak_dan_rollback_jika_stok_tidak_cukup(): void
    {
        $customer = Customer::factory()->create();
        $barang   = Barang::factory()->create([
            'stok_saat_ini' => 3,       // hanya 3
            'jenis_ppn'     => 'non_ppn',
        ]);

        $payload = $this->payloadPenjualan([
            'id_customer'   => $customer->id_customer,
            'id_barang'     => [$barang->id_barang],
            'jumlah'        => [10],    // minta 10 → harus gagal
            'harga_jual'    => [50000],
            'diskon_nominal' => [0],
        ]);

        $this->actingAs($this->user)
            ->post(route('penjualan.store'), $payload)
            ->assertSessionHasErrors();

        // -- DB tidak berubah sama sekali (rollback) --
        $this->assertDatabaseCount('penjualan', 0);
        $this->assertDatabaseCount('detail_penjualan', 0);
        $this->assertDatabaseCount('riwayat_stok', 0);

        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang->id_barang,
            'stok_saat_ini' => 3,   // tidak berubah
        ]);
    }

    // =========================================================================
    // 6. Validasi field wajib — request kosong harus ditolak
    // =========================================================================

    public function test_store_gagal_validasi_tanpa_field_wajib(): void
    {
        $this->actingAs($this->user)
            ->post(route('penjualan.store'), [])
            ->assertSessionHasErrors([
                'tanggal_penjualan',
                'id_customer',
                'metode_pembayaran',
                'id_barang',
            ]);

        $this->assertDatabaseCount('penjualan', 0);
    }

    // =========================================================================
    // 7. Diskon per-item dikurangi dengan benar dari subtotal
    // 1 item: 4 pcs @ Rp 25.000, diskon Rp 5.000
    // subtotal_detail = (4 × 25.000) - 5.000 = 95.000
    // =========================================================================

    public function test_store_diskon_dikurangi_dari_subtotal_item(): void
    {
        $customer = Customer::factory()->create();
        $barang   = Barang::factory()->create([
            'stok_saat_ini'          => 20,
            'jenis_ppn'              => 'non_ppn',
            'tipe_perhitungan_harga' => 'normal',
        ]);

        $payload = $this->payloadPenjualan([
            'id_customer'    => $customer->id_customer,
            'id_barang'      => [$barang->id_barang],
            'jumlah'         => [4],
            'harga_jual'     => [25000],
            'diskon_nominal' => [5000],
        ]);

        $this->actingAs($this->user)
            ->post(route('penjualan.store'), $payload)
            ->assertRedirect(route('penjualan.index'));

        $this->assertDatabaseHas('detail_penjualan', [
            'id_barang'      => $barang->id_barang,
            'diskon_nominal' => 5000,
            'subtotal'       => 95000.00,
        ]);

        $this->assertDatabaseHas('penjualan', [
            'subtotal'   => 95000.00,
            'total_akhir' => 95000.00,
        ]);
    }

    // =========================================================================
    // 8. Show detail penjualan
    // =========================================================================

    public function test_show_displays_penjualan_details(): void
    {
        $penjualan = Penjualan::factory()->create();

        $this->actingAs($this->user)
            ->get(route('penjualan.show', $penjualan->id_penjualan))
            ->assertOk()
            ->assertViewIs('penjualan.show')
            ->assertViewHas('penjualan');
    }

    // =========================================================================
    // 9. Guest tidak bisa akses
    // =========================================================================

    public function test_guest_diredirect_ke_login(): void
    {
        $this->get(route('penjualan.index'))->assertRedirect(route('login'));
        $this->get(route('penjualan.create'))->assertRedirect(route('login'));
        $this->post(route('penjualan.store'), [])->assertRedirect(route('login'));
    }
}
