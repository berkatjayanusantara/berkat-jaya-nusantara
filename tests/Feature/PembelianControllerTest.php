<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembelianControllerTest extends TestCase
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

    private function payloadPembelian(array $overrides = []): array
    {
        return array_merge([
            'nomor_pembelian'             => 'PO-TEST-001',
            'nomor_delivery_order'        => null,
            'nomor_surat_jalan'           => null,
            'tanggal_pembelian'           => '2024-01-10',
            'id_supplier'                 => null,   // diisi caller
            'nilai_pajak'                 => 0,
            'biaya_lain'                  => 0,
            'potongan_diskon'             => 0,
            'keterangan_penyesuaian_total' => null,
            'id_barang'                   => [],
            'jumlah_dipesan'              => [],
            'jumlah'                      => [],
            'harga_beli'                  => [],
        ], $overrides);
    }

    // =========================================================================
    // 1. Halaman listing & form
    // =========================================================================

    public function test_index_displays_pembelian_list(): void
    {
        $this->actingAs($this->user)
            ->get(route('pembelian.index'))
            ->assertOk()
            ->assertViewIs('pembelian.index')
            ->assertViewHas('pembelian');
    }

    public function test_create_displays_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('pembelian.create'))
            ->assertOk()
            ->assertViewIs('pembelian.create')
            ->assertViewHas('suppliers')
            ->assertViewHas('barang');
    }

    // =========================================================================
    // 2. Store LENGKAP — semua barang diterima
    // 2 item: 10 pcs @ Rp 40.000 dan 5 pcs @ Rp 80.000
    // subtotal = (10×40.000) + (5×80.000) = 400.000 + 400.000 = 800.000
    // nilai_pajak = 0, biaya_lain = 0, potongan = 0 → total_akhir = 800.000
    // status_penerimaan = 'lengkap'
    // =========================================================================

    public function test_store_pembelian_lengkap_tambah_stok_dan_simpan_total(): void
    {
        $supplier = Supplier::factory()->create();
        $barang1  = Barang::factory()->create(['stok_saat_ini' => 5]);
        $barang2  = Barang::factory()->create(['stok_saat_ini' => 0]);

        $payload = $this->payloadPembelian([
            'id_supplier'    => $supplier->id_supplier,
            'id_barang'      => [$barang1->id_barang, $barang2->id_barang],
            'jumlah_dipesan' => [10, 5],
            'jumlah'         => [10, 5],        // diterima = dipesan → lengkap
            'harga_beli'     => [40000, 80000],
        ]);

        $this->actingAs($this->user)
            ->post(route('pembelian.store'), $payload)
            ->assertRedirect(route('pembelian.index'))
            ->assertSessionHas('success');

        // -- Header pembelian tersimpan --
        $this->assertDatabaseHas('pembelian', [
            'id_supplier'       => $supplier->id_supplier,
            'nomor_dokumen_asli' => 'PO-TEST-001',
            'status_penerimaan' => 'lengkap',
            'subtotal'          => 800000.00,
            'nilai_pajak'       => 0.00,
            'total_akhir'       => 800000.00,
            'is_historical'     => 0,
            'affect_stock'      => 1,
        ]);

        // -- Detail barang tersimpan --
        $this->assertDatabaseHas('detail_pembelian', [
            'id_barang'      => $barang1->id_barang,
            'jumlah_dipesan' => 10,
            'jumlah'         => 10,
            'harga_beli'     => 40000,
            'subtotal'       => 400000,
        ]);

        // -- Stok BERTAMBAH sesuai jumlah diterima --
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang1->id_barang,
            'stok_saat_ini' => 15,  // 5 + 10
        ]);
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang2->id_barang,
            'stok_saat_ini' => 5,   // 0 + 5
        ]);

        // -- harga_beli_terakhir diupdate --
        $this->assertDatabaseHas('barang', [
            'id_barang'          => $barang1->id_barang,
            'harga_beli_terakhir' => 40000,
        ]);

        // -- Riwayat stok masuk tercatat --
        $this->assertDatabaseHas('riwayat_stok', [
            'id_barang'        => $barang1->id_barang,
            'jenis_pergerakan' => 'masuk',
            'jumlah'           => 10,
            'stok_sebelum'     => 5,
            'stok_sesudah'     => 15,
        ]);
    }

    // =========================================================================
    // 3. Store SEBAGIAN — diterima < dipesan → status 'sebagian'
    // Stok hanya bertambah sesuai jumlah diterima, bukan yang dipesan
    // =========================================================================

    public function test_store_pembelian_sebagian_status_dan_stok_sesuai_diterima(): void
    {
        $supplier = Supplier::factory()->create();
        $barang   = Barang::factory()->create(['stok_saat_ini' => 0]);

        $payload = $this->payloadPembelian([
            'id_supplier'    => $supplier->id_supplier,
            'id_barang'      => [$barang->id_barang],
            'jumlah_dipesan' => [20],
            'jumlah'         => [7],    // hanya 7 dari 20 yang datang
            'harga_beli'     => [10000],
        ]);

        $this->actingAs($this->user)
            ->post(route('pembelian.store'), $payload)
            ->assertRedirect(route('pembelian.index'));

        $this->assertDatabaseHas('pembelian', [
            'status_penerimaan' => 'sebagian',
            'subtotal'          => 70000.00,    // 7 × 10.000
            'total_akhir'       => 70000.00,
        ]);

        // Stok hanya bertambah 7, bukan 20
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang->id_barang,
            'stok_saat_ini' => 7,
        ]);
    }

    // =========================================================================
    // 4. PPN + biaya lain + potongan diskon tersimpan dan dihitung benar
    // subtotal = 500.000, nilai_pajak = 55.000, biaya_lain = 25.000, potongan = 30.000
    // totalSebelumPotongan = 500.000 + 55.000 + 25.000 = 580.000
    // total_akhir = 580.000 - 30.000 = 550.000
    // =========================================================================

    public function test_store_dengan_pajak_biaya_lain_dan_potongan(): void
    {
        $supplier = Supplier::factory()->create();
        $barang   = Barang::factory()->create(['stok_saat_ini' => 100]);

        $payload = $this->payloadPembelian([
            'id_supplier'    => $supplier->id_supplier,
            'nilai_pajak'    => 55000,
            'biaya_lain'     => 25000,
            'potongan_diskon' => 30000,
            'id_barang'      => [$barang->id_barang],
            'jumlah_dipesan' => [10],
            'jumlah'         => [10],
            'harga_beli'     => [50000],
        ]);

        $this->actingAs($this->user)
            ->post(route('pembelian.store'), $payload)
            ->assertRedirect(route('pembelian.index'));

        $this->assertDatabaseHas('pembelian', [
            'subtotal'       => 500000.00,
            'nilai_pajak'    => 55000.00,
            'biaya_lain'     => 25000.00,
            'potongan_diskon' => 30000.00,
            'total_akhir'    => 550000.00,
        ]);
    }

    // =========================================================================
    // 5. Potongan > totalSebelumPotongan → ditolak, rollback
    // =========================================================================

    public function test_store_ditolak_jika_potongan_melebihi_total(): void
    {
        $supplier = Supplier::factory()->create();
        $barang   = Barang::factory()->create(['stok_saat_ini' => 10]);

        $payload = $this->payloadPembelian([
            'id_supplier'    => $supplier->id_supplier,
            'potongan_diskon' => 999999,    // lebih besar dari subtotal 100.000
            'id_barang'      => [$barang->id_barang],
            'jumlah_dipesan' => [2],
            'jumlah'         => [2],
            'harga_beli'     => [50000],
        ]);

        $this->actingAs($this->user)
            ->post(route('pembelian.store'), $payload)
            ->assertSessionHasErrors('potongan_diskon');

        $this->assertDatabaseCount('pembelian', 0);
        $this->assertDatabaseCount('riwayat_stok', 0);

        // Stok tidak berubah
        $this->assertDatabaseHas('barang', [
            'id_barang'    => $barang->id_barang,
            'stok_saat_ini' => 10,
        ]);
    }

    // =========================================================================
    // 6. Jumlah diterima > jumlah dipesan → ditolak
    // =========================================================================

    public function test_store_ditolak_jika_diterima_lebih_dari_dipesan(): void
    {
        $supplier = Supplier::factory()->create();
        $barang   = Barang::factory()->create(['stok_saat_ini' => 10]);

        $payload = $this->payloadPembelian([
            'id_supplier'    => $supplier->id_supplier,
            'id_barang'      => [$barang->id_barang],
            'jumlah_dipesan' => [5],
            'jumlah'         => [10],   // diterima > dipesan
            'harga_beli'     => [10000],
        ]);

        $this->actingAs($this->user)
            ->post(route('pembelian.store'), $payload)
            ->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('pembelian', 0);
    }

    // =========================================================================
    // 7. Validasi field wajib
    // =========================================================================

    public function test_store_gagal_validasi_tanpa_field_wajib(): void
    {
        $this->actingAs($this->user)
            ->post(route('pembelian.store'), [])
            ->assertSessionHasErrors([
                'tanggal_pembelian',
                'id_supplier',
                'id_barang',
            ]);

        $this->assertDatabaseCount('pembelian', 0);
    }

    // =========================================================================
    // 8. Show detail
    // =========================================================================

    public function test_show_displays_pembelian_details(): void
    {
        $pembelian = Pembelian::factory()->create();

        $this->actingAs($this->user)
            ->get(route('pembelian.show', $pembelian->id_pembelian))
            ->assertOk()
            ->assertViewIs('pembelian.show')
            ->assertViewHas('pembelian');
    }

    // =========================================================================
    // 9. Guest tidak bisa akses
    // =========================================================================

    public function test_guest_diredirect_ke_login(): void
    {
        $this->get(route('pembelian.index'))->assertRedirect(route('login'));
        $this->get(route('pembelian.create'))->assertRedirect(route('login'));
        $this->post(route('pembelian.store'), [])->assertRedirect(route('login'));
    }
}
