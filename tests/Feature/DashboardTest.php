<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AlokasiPembayaran;
use App\Models\JenisSimpanan;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use App\Services\LoanScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_account_member_and_management_summaries(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $this->createMember('ANG-001', 'Anggota Aktif', 'aktif');
        $this->createMember('ANG-002', 'Anggota Menunggu', 'menunggu_verifikasi');

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Total akun')
            ->assertSee('Kelola akun')
            ->assertSee('2 anggota')
            ->assertSee('Laporan koperasi')
            ->assertSee('Log aktivitas')
            ->assertSee('#ringkasan-laporan')
            ->assertDontSee('Catat setoran');
    }

    public function test_staff_dashboard_filters_cashflows_by_month_and_handles_invalid_period(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001', 'Anggota Uji', 'aktif');
        $type = JenisSimpanan::create([
            'kode' => 'WAJIB',
            'nama' => 'Simpanan Wajib',
            'is_active' => true,
        ]);
        $loan = $this->createLoan($member, $staff, '1000000.00');
        $this->createSaving($member, $staff, $type, 'SETOR-BULAN-INI', '50000.00', today());
        $this->createSaving(
            $member,
            $staff,
            $type,
            'SETOR-BULAN-LALU',
            '90000.00',
            today()->subMonth()->startOfMonth(),
        );
        $this->createPayment($loan, $staff, 'BYR-BULAN-INI', '150000.00', today());
        $this->createPayment(
            $loan,
            $staff,
            'BYR-BULAN-LALU',
            '80000.00',
            today()->subMonth()->startOfMonth(),
        );
        $period = now()->format('Y-m');

        $this->actingAs($staff)->get('/petugas/dashboard?periode='.$period)
            ->assertOk()
            ->assertSee('Setoran sah')
            ->assertSee('Pembayaran sah')
            ->assertSee('50.000')
            ->assertSee('150.000')
            ->assertSee('BYR-BULAN-INI')
            ->assertDontSee('BYR-BULAN-LALU')
            ->assertSee('table-responsive');

        $this->get('/petugas/dashboard?periode=bulan-lalu')
            ->assertOk()
            ->assertSee('Periode tidak valid')
            ->assertSee('periode');
    }

    public function test_member_dashboard_only_shows_own_balances_loans_and_history(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $memberUser = $this->createUser('anggota', 'anggota@example.test');
        $otherUser = $this->createUser('anggota', 'anggota-lain@example.test');
        $member = $this->createMember('ANG-001', 'Anggota Uji', 'aktif', $memberUser);
        $otherMember = $this->createMember('ANG-002', 'Anggota Lain', 'aktif', $otherUser);
        $type = JenisSimpanan::create([
            'kode' => 'SUKARELA',
            'nama' => 'Simpanan Sukarela',
            'is_active' => true,
        ]);
        $this->createSaving($member, $staff, $type, 'SETOR-OWN-001', '125000.00', today());
        $this->createSaving($otherMember, $staff, $type, 'SETOR-OTHER-001', '900000.00', today());
        $loan = $this->createLoan($member, $staff, '300000.00');
        $otherLoan = $this->createLoan($otherMember, $staff, '900000.00');
        $this->createPayment(
            $loan,
            $staff,
            'BYR-OWN-001',
            '103000.00',
            today(),
            true,
        );
        $this->createPayment(
            $otherLoan,
            $staff,
            'BYR-OTHER-001',
            '309000.00',
            today(),
            true,
        );

        $this->actingAs($memberUser)->get('/anggota/dashboard')
            ->assertOk()
            ->assertSee('Saldo per jenis simpanan')
            ->assertSee('Simpanan Sukarela')
            ->assertSee('Jadwal angsuran')
            ->assertSee('103.000')
            ->assertSee('BYR-OWN-001')
            ->assertDontSee('SETOR-OTHER-001')
            ->assertDontSee('BYR-OTHER-001')
            ->assertDontSee('900.000')
            ->assertSee('table-responsive');
    }

    public function test_member_dashboard_shows_empty_states_when_account_is_not_linked(): void
    {
        $member = $this->createUser('anggota', 'anggota@example.test');

        $this->actingAs($member)->get('/anggota/dashboard')
            ->assertOk()
            ->assertSee('Akun ini belum terhubung dengan data anggota.')
            ->assertSee('Belum ada saldo simpanan yang tercatat.')
            ->assertSee('Tidak ada tagihan angsuran yang belum lunas.')
            ->assertSee('Belum ada pembayaran pinjaman.');
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

    private function createMember(
        string $number,
        string $name,
        string $status,
        ?User $user = null,
    ): Anggota {
        return Anggota::create([
            'nomor_anggota' => $number,
            'nama' => $name,
            'tanggal_bergabung' => today()->toDateString(),
            'status_keanggotaan' => $status,
            'user_id' => $user?->id,
        ]);
    }

    private function createLoan(Anggota $member, User $staff, string $principal): Pinjaman
    {
        $loan = Pinjaman::create([
            'nomor_pinjaman' => 'PNJ-'.Str::upper(Str::random(12)),
            'anggota_id' => $member->id,
            'pokok' => $principal,
            'tenor_bulan' => 3,
            'tanggal_pencairan' => today()->subMonth()->toDateString(),
            'angsuran_pertama_jatuh_tempo' => today()->toDateString(),
            'referensi_persetujuan' => 'APPROVAL-TEST',
            'referensi_pencairan' => 'DISBURSE-TEST',
            'jasa_flat_bulanan_snapshot' => '1.00000',
            'snapshot_ketentuan' => [
                'metode' => 'flat',
                'jasa_flat_bulanan' => 1.0,
                'tenor_bulan' => [3, 6, 12],
            ],
            'status' => 'aktif',
            'dibuat_oleh' => $staff->id,
        ]);

        app(LoanScheduleService::class)->ensure($loan);

        return $loan;
    }

    private function createSaving(
        Anggota $member,
        User $staff,
        JenisSimpanan $type,
        string $number,
        string $amount,
        \Illuminate\Support\Carbon $date,
    ): TransaksiSimpanan {
        return TransaksiSimpanan::create([
            'nomor_transaksi' => $number,
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
            'tanggal_transaksi' => $date->toDateString(),
            'nominal' => $amount,
            'metode' => 'tunai',
            'status' => 'tercatat',
            'dibuat_oleh' => $staff->id,
            'jenis_simpanan_kode_snapshot' => $type->kode,
            'jenis_simpanan_nama_snapshot' => $type->nama,
        ]);
    }

    private function createPayment(
        Pinjaman $loan,
        User $staff,
        string $number,
        string $amount,
        \Illuminate\Support\Carbon $date,
        bool $allocateFirstInstallment = false,
    ): Pembayaran {
        $payment = Pembayaran::create([
            'nomor_pembayaran' => $number,
            'pinjaman_id' => $loan->id,
            'tanggal_pembayaran' => $date->toDateString(),
            'nominal' => $amount,
            'metode' => 'tunai',
            'status' => 'tercatat',
            'dibuat_oleh' => $staff->id,
        ]);

        if ($allocateFirstInstallment) {
            $installment = $loan->angsuran()->orderBy('urutan')->firstOrFail();
            AlokasiPembayaran::create([
                'pembayaran_id' => $payment->id,
                'angsuran_id' => $installment->id,
                'alokasi_pokok' => $installment->pokok_tagihan,
                'alokasi_jasa' => $installment->jasa_tagihan,
            ]);
        }

        return $payment;
    }
}
