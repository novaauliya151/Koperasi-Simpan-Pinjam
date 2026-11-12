<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\KetentuanPinjaman;
use App\Models\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTermsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_the_initial_assumption_and_save_a_version(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)->get('/admin/ketentuan-pinjaman')
            ->assertOk()
            ->assertSee('Asumsi awal belum disimpan')
            ->assertSee('1,00%')
            ->assertSee('3, 6, 12 bulan');

        $this->post('/admin/ketentuan-pinjaman', [
            'nama' => 'Simulasi Flat 1,25%',
            'jasa_flat_bulanan' => '1.25000',
            'tenor_bulan' => '3, 6, 12',
            'berlaku_mulai' => today()->toDateString(),
            'catatan' => 'Asumsi untuk demonstrasi.',
        ])->assertRedirect('/admin/ketentuan-pinjaman');

        $rule = KetentuanPinjaman::firstOrFail();
        $this->assertSame([3, 6, 12], $rule->tenor_bulan);
        $this->assertSame('flat', $rule->metode_perhitungan);
        $this->assertSame('Asumsi untuk demonstrasi.', $rule->keterangan);
    }

    public function test_new_loans_use_the_effective_rule_and_store_its_snapshot(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember($staff);
        $rule = KetentuanPinjaman::create([
            'nama' => 'Aturan uji',
            'jasa_flat_bulanan' => '1.50000',
            'berlaku_mulai' => today()->toDateString(),
            'is_active' => true,
            'metode_perhitungan' => 'flat',
            'tenor_bulan' => [4, 8],
            'pembulatan' => 'Rupiah terdekat.',
            'dasar' => 'Asumsi simulasi.',
            'sumber' => 'Sumber uji.',
        ]);

        $this->actingAs($staff)->post('/petugas/pinjaman', [
            'nomor_pinjaman' => 'PNJ-RULE-0001',
            'anggota_id' => $member->id,
            'pokok' => '2000000',
            'tenor_bulan' => '4',
            'tanggal_pencairan' => today()->toDateString(),
            'angsuran_pertama_jatuh_tempo' => today()->addMonth()->toDateString(),
            'referensi_persetujuan' => 'REF-APP-01',
            'referensi_pencairan' => 'REF-CAIR-01',
        ])->assertRedirect('/petugas/pinjaman');

        $loan = Pinjaman::firstOrFail();
        $this->assertSame($rule->id, $loan->ketentuan_pinjaman_id);
        $this->assertSame('Aturan uji', $loan->snapshot_ketentuan['nama']);
        $this->assertSame([4, 8], $loan->snapshot_ketentuan['tenor_bulan']);
        $this->assertSame('1.50000', $loan->jasa_flat_bulanan_snapshot);
    }

    public function test_only_admin_can_manage_loan_terms(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createUser('anggota', 'anggota@example.test');

        $this->actingAs($staff)->get('/admin/ketentuan-pinjaman')->assertForbidden();
        $this->actingAs($member)->post('/admin/ketentuan-pinjaman')->assertForbidden();
    }

    private function createMember(User $staff): Anggota
    {
        $user = $this->createUser('anggota', 'linked-member@example.test');

        return Anggota::create([
            'nomor_anggota' => 'ANG-001',
            'nama' => 'Anggota Uji',
            'tanggal_bergabung' => today()->toDateString(),
            'status_keanggotaan' => 'aktif',
            'user_id' => $user->id,
            'diverifikasi_oleh' => $staff->id,
        ]);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => 'Pengguna Uji',
            'email' => $email,
            'password' => 'password-rahasia',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
