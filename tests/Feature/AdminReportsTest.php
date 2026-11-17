<?php

namespace Tests\Feature;

use App\Models\AlokasiPembayaran;
use App\Models\Anggota;
use App\Models\JenisSimpanan;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use App\Services\LoanScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_all_four_printable_reports(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $this->createMember('ANG-001');

        foreach (['anggota', 'simpanan', 'pinjaman', 'angsuran'] as $report) {
            $response = $this->actingAs($admin)
                ->get(route('admin.reports.index', ['laporan' => $report]));

            $response->assertOk()
                ->assertSee('KOPERASI SIMPAN PINJAM')
                ->assertSee('Cetak laporan')
                ->assertSee($report === 'anggota'
                    ? 'Laporan Anggota'
                    : 'Laporan '.ucfirst($report));
        }
    }

    public function test_savings_report_applies_filters_and_nets_reversals_by_transaction_date(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001');
        $type = JenisSimpanan::create([
            'kode' => 'WAJIB',
            'nama' => 'Simpanan Wajib',
            'is_active' => true,
        ]);
        $this->createSaving($member, $staff, $type, 'SS-VALID', '50000.00', today());
        $cancelled = $this->createSaving(
            $member,
            $staff,
            $type,
            'SS-CANCELLED',
            '30000.00',
            today()->subDay(),
        );
        $cancelled->update(['status' => 'dibatalkan']);
        $this->createSaving(
            $member,
            $staff,
            $type,
            'SS-REVERSAL',
            '30000.00',
            today(),
            $cancelled->id,
            'pembalikan',
        );

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'laporan' => 'simpanan',
            'dari' => today()->toDateString(),
            'sampai' => today()->toDateString(),
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
        ]))
            ->assertOk()
            ->assertSee('Total neto periode')
            ->assertSee('20.000')
            ->assertSee('Saldo posisi akhir')
            ->assertSee('50.000')
            ->assertSee('SS-REVERSAL');

        $response = $this->get(route('admin.reports.index', [
            'laporan' => 'simpanan',
            'dari' => today()->subDay()->toDateString(),
            'sampai' => today()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('SS-CANCELLED')
            ->assertSeeTextInOrder(['Total neto periode', 'Rp 50.000']);
    }

    public function test_loan_and_installment_reports_separate_period_flows_from_current_balances(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001');
        $loan = $this->createLoan($member, '300000.00');
        $installment = $loan->angsuran()->orderBy('urutan')->firstOrFail();
        $payment = Pembayaran::create([
            'nomor_pembayaran' => 'BYR-VALID',
            'pinjaman_id' => $loan->id,
            'tanggal_pembayaran' => today()->toDateString(),
            'nominal' => '103000.00',
            'metode' => 'tunai',
            'status' => 'tercatat',
            'dibuat_oleh' => $staff->id,
        ]);
        AlokasiPembayaran::create([
            'pembayaran_id' => $payment->id,
            'angsuran_id' => $installment->id,
            'alokasi_pokok' => $installment->pokok_tagihan,
            'alokasi_jasa' => $installment->jasa_tagihan,
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'laporan' => 'pinjaman',
            'dari' => today()->toDateString(),
            'sampai' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Pembayaran neto periode')
            ->assertSee('103.000')
            ->assertSee('Sisa pokok saat ini')
            ->assertSee('200.000')
            ->assertSee('Sisa kewajiban saat ini')
            ->assertSee('206.000');

        $this->get(route('admin.reports.index', [
            'laporan' => 'angsuran',
            'dari' => today()->toDateString(),
            'sampai' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Tagihan jatuh tempo periode')
            ->assertSee('Pembayaran neto periode')
            ->assertSee('Sisa tagihan saat ini')
            ->assertSee('Lunas');
    }

    public function test_reports_are_only_available_to_admins_and_validate_date_ranges(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');

        $this->actingAs($staff)
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $admin = $this->createUser('admin', 'admin@example.test');
        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'laporan' => 'simpanan',
                'dari' => today()->toDateString(),
                'sampai' => today()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('sampai');
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

    private function createMember(string $number): Anggota
    {
        return Anggota::create([
            'nomor_anggota' => $number,
            'nama' => 'Anggota Uji',
            'tanggal_bergabung' => today()->toDateString(),
            'status_keanggotaan' => 'aktif',
        ]);
    }

    private function createSaving(
        Anggota $member,
        User $staff,
        JenisSimpanan $type,
        string $number,
        string $amount,
        \Illuminate\Support\Carbon $date,
        ?int $reversedTransactionId = null,
        string $status = 'tercatat',
    ): TransaksiSimpanan {
        return TransaksiSimpanan::create([
            'nomor_transaksi' => $number,
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
            'tanggal_transaksi' => $date->toDateString(),
            'nominal' => $amount,
            'metode' => 'tunai',
            'status' => $status,
            'dibuat_oleh' => $staff->id,
            'pembalikan_dari_id' => $reversedTransactionId,
            'jenis_simpanan_kode_snapshot' => $type->kode,
            'jenis_simpanan_nama_snapshot' => $type->nama,
        ]);
    }

    private function createLoan(Anggota $member, string $principal): Pinjaman
    {
        $staff = User::query()->where('role', 'petugas')->firstOrFail();
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
}
