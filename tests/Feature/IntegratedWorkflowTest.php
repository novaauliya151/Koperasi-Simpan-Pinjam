<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AlokasiPembayaran;
use App\Models\JenisSimpanan;
use App\Models\KetentuanSimpanan;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IntegratedWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_member_savings_loan_repayment_dashboard_and_reports_work_together(): void
    {
        $admin = $this->createUser('admin', 'admin@example.test');

        $this->actingAs($admin)->post('/admin/akun', [
            'name' => 'Petugas Uji',
            'email' => 'petugas@example.test',
            'role' => 'petugas',
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
            'is_active' => '1',
        ])->assertRedirect('/admin/akun');

        $staff = User::query()->where('email', 'petugas@example.test')->firstOrFail();
        $activeMember = $this->createMemberThroughStaff($staff, 'ANG-001', 'Anggota Aktif');
        $inactiveMember = $this->createMemberThroughStaff($staff, 'ANG-002', 'Anggota Tidak Aktif');

        $this->actingAs($staff)->patch(
            '/petugas/anggota/'.$activeMember->id.'/status',
            ['status_keanggotaan' => 'aktif'],
        )->assertRedirect('/petugas/anggota');
        $this->actingAs($staff)->patch(
            '/petugas/anggota/'.$inactiveMember->id.'/status',
            ['status_keanggotaan' => 'tidak_aktif'],
        )->assertRedirect('/petugas/anggota');

        $this->actingAs($admin)->post('/admin/akun', [
            'name' => 'Akun Anggota Uji',
            'email' => 'anggota@example.test',
            'role' => 'anggota',
            'anggota_id' => $activeMember->id,
            'password' => 'password-rahasia',
            'password_confirmation' => 'password-rahasia',
            'is_active' => '1',
        ])->assertRedirect('/admin/akun');

        $memberUser = User::query()->where('email', 'anggota@example.test')->firstOrFail();
        $activeMember->refresh();
        $this->assertSame($memberUser->id, $activeMember->user_id);

        $requiredType = $this->createSavingType($staff, 'WAJIB', 'Simpanan Wajib');
        $voluntaryType = $this->createSavingType($staff, 'SUKARELA', 'Simpanan Sukarela');
        $requiredSaving = $this->savingData($activeMember, $requiredType, '25000.00');
        $voluntarySaving = $this->savingData($activeMember, $voluntaryType, '100000.00');

        $this->actingAs($staff)->post('/petugas/simpanan', $requiredSaving)
            ->assertRedirect('/petugas/simpanan');
        $this->post('/petugas/simpanan', $voluntarySaving)
            ->assertRedirect('/petugas/simpanan');

        $this->post('/petugas/simpanan', $requiredSaving)
            ->assertSessionHasErrors('idempotency_key');

        $invalidSaving = $this->savingData($activeMember, $voluntaryType, '-1.00');
        $this->post('/petugas/simpanan', $invalidSaving)
            ->assertSessionHasErrors('nominal');

        $emptySaving = $this->savingData($activeMember, $voluntaryType, '');
        $this->post('/petugas/simpanan', $emptySaving)
            ->assertSessionHasErrors('nominal');

        $inactiveSaving = $this->savingData($inactiveMember, $voluntaryType, '50000.00');
        $this->post('/petugas/simpanan', $inactiveSaving)
            ->assertSessionHasErrors('anggota_id');
        $this->assertDatabaseCount('transaksi_simpanans', 2);

        $loanData = $this->loanData($activeMember);
        $this->actingAs($staff)->post('/petugas/pinjaman', $loanData)
            ->assertRedirect('/petugas/pinjaman');

        $this->post('/petugas/pinjaman', $loanData)
            ->assertSessionHasErrors('nomor_pinjaman');

        $inactiveLoan = $loanData;
        $inactiveLoan['nomor_pinjaman'] = 'PNJ-TEST-0002';
        $inactiveLoan['anggota_id'] = $inactiveMember->id;
        $this->post('/petugas/pinjaman', $inactiveLoan)
            ->assertSessionHasErrors('anggota_id');
        $this->assertDatabaseCount('pinjamans', 1);

        $loan = Pinjaman::query()->where('nomor_pinjaman', 'PNJ-TEST-0001')->firstOrFail();

        for ($installmentNumber = 1; $installmentNumber <= 3; $installmentNumber++) {
            $installment = $loan->angsuran()
                ->where('urutan', $installmentNumber)
                ->firstOrFail();

            $this->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
                'angsuran_id' => $installment->id,
                'tanggal_pembayaran' => today()->toDateString(),
                'metode' => 'tunai',
                'referensi' => 'FLOW-'.$installmentNumber,
            ])->assertRedirect('/petugas/pinjaman/'.$loan->id);
        }

        $firstInstallment = $loan->angsuran()->where('urutan', 1)->firstOrFail();
        $this->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
            'angsuran_id' => $firstInstallment->id,
            'tanggal_pembayaran' => today()->toDateString(),
            'metode' => 'tunai',
        ])->assertSessionHasErrors('angsuran_id');

        $loan->refresh();
        $this->assertSame('lunas', $loan->status);
        $this->assertSame(3, $loan->pembayaran()->where('status', 'tercatat')->count());
        $this->assertSame(3, $loan->angsuran()->where('status', 'dibayar')->count());
        $this->assertSame(125000.0, (float) TransaksiSimpanan::query()
            ->where('anggota_id', $activeMember->id)
            ->where('status', 'tercatat')
            ->sum('nominal'));

        $this->actingAs($memberUser)->get('/anggota/dashboard')
            ->assertOk()
            ->assertSee('125.000')
            ->assertSee('Simpanan Wajib')
            ->assertSee('Simpanan Sukarela');

        $this->actingAs($staff)->get('/petugas/dashboard?periode='.today()->format('Y-m'))
            ->assertOk()
            ->assertSee('Setoran sah')
            ->assertSee('125.000')
            ->assertSee('309.000');

        $this->actingAs($admin)->get(route('admin.reports.index', [
            'laporan' => 'simpanan',
            'dari' => today()->toDateString(),
            'sampai' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Total neto periode')
            ->assertSee('125.000');

        $this->get(route('admin.reports.index', [
            'laporan' => 'pinjaman',
            'dari' => today()->toDateString(),
            'sampai' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('309.000')
            ->assertSeeTextInOrder([
                'Sisa pokok saat ini',
                'Rp 0',
                'Sisa kewajiban saat ini',
                'Rp 0',
            ]);

        $this->get(route('admin.reports.index', [
            'laporan' => 'angsuran',
            'dari' => $loan->angsuran_pertama_jatuh_tempo->toDateString(),
            'sampai' => today()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Tagihan jatuh tempo periode')
            ->assertSee('309.000')
            ->assertSee('Lunas');

        $firstPayment = $loan->pembayaran()->where('status', 'tercatat')->orderBy('id')->firstOrFail();
        $this->actingAs($staff)->post(
            '/petugas/pinjaman/pembayaran/'.$firstPayment->id.'/pembatalan',
            ['alasan_koreksi' => 'Koreksi pengujian integrasi.'],
        )->assertRedirect('/petugas/pinjaman/'.$loan->id);

        $reversal = Pembayaran::query()
            ->where('pembalikan_dari_id', $firstPayment->id)
            ->firstOrFail();
        $this->post(
            '/petugas/pinjaman/pembayaran/'.$firstPayment->id.'/pembatalan',
            ['alasan_koreksi' => 'Koreksi berulang.'],
        )->assertSessionHasErrors('pembayaran');
        $this->post(
            '/petugas/pinjaman/pembayaran/'.$reversal->id.'/pembatalan',
            ['alasan_koreksi' => 'Koreksi pembalikan.'],
        )->assertSessionHasErrors('pembayaran');

        $this->assertSame('aktif', $loan->fresh()->status);
        $this->assertSame(4, $loan->pembayaran()->count());
        $this->assertSame(2, $loan->pembayaran()->where('status', 'tercatat')->count());
        $this->assertSame(3, AlokasiPembayaran::query()
            ->whereHas('angsuran', fn ($query) => $query->where('pinjaman_id', $loan->id))
            ->count());
        $this->assertDatabaseHas('pembayarans', [
            'id' => $firstPayment->id,
            'status' => 'dibatalkan',
            'alasan_koreksi' => 'Koreksi pengujian integrasi.',
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

    private function createMemberThroughStaff(User $staff, string $number, string $name): Anggota
    {
        $this->actingAs($staff)->post('/petugas/anggota', [
            'nomor_anggota' => $number,
            'nama' => $name,
            'tanggal_bergabung' => today()->toDateString(),
        ])->assertRedirect();

        return Anggota::query()->where('nomor_anggota', $number)->firstOrFail();
    }

    private function createSavingType(User $staff, string $code, string $name): JenisSimpanan
    {
        $type = JenisSimpanan::create([
            'kode' => $code,
            'nama' => $name,
            'is_active' => true,
        ]);
        KetentuanSimpanan::create([
            'jenis_simpanan_id' => $type->id,
            'nominal_ketentuan' => $code === 'WAJIB' ? '25000.00' : '0.00',
            'berlaku_mulai' => today()->toDateString(),
            'dibuat_oleh' => $staff->id,
        ]);

        return $type;
    }

    /**
     * @return array{
     *     idempotency_key: string,
     *     anggota_id: int,
     *     jenis_simpanan_id: int,
     *     tanggal_transaksi: string,
     *     periode: ?string,
     *     nominal: string,
     *     metode: string,
     *     keterangan: string
     * }
     */
    private function savingData(Anggota $member, JenisSimpanan $type, string $amount): array
    {
        return [
            'idempotency_key' => (string) Str::uuid(),
            'anggota_id' => $member->id,
            'jenis_simpanan_id' => $type->id,
            'tanggal_transaksi' => today()->toDateString(),
            'periode' => $type->kode === 'WAJIB' ? today()->format('Y-m') : null,
            'nominal' => $amount,
            'metode' => 'tunai',
            'keterangan' => 'Skenario integrasi Day 16',
        ];
    }

    /**
     * @return array{
     *     nomor_pinjaman: string,
     *     anggota_id: int,
     *     pokok: string,
     *     tenor_bulan: string,
     *     tanggal_pencairan: string,
     *     angsuran_pertama_jatuh_tempo: string,
     *     referensi_persetujuan: string,
     *     referensi_pencairan: string
     * }
     */
    private function loanData(Anggota $member): array
    {
        return [
            'nomor_pinjaman' => 'PNJ-TEST-0001',
            'anggota_id' => $member->id,
            'pokok' => '300000.00',
            'tenor_bulan' => '3',
            'tanggal_pencairan' => today()->subMonthsNoOverflow(3)->toDateString(),
            'angsuran_pertama_jatuh_tempo' => today()->subMonthsNoOverflow(2)->toDateString(),
            'referensi_persetujuan' => 'REF-APP-FLOW-001',
            'referensi_pencairan' => 'REF-CAIR-FLOW-001',
        ];
    }
}
