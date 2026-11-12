<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AlokasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_an_approved_loan_with_a_terms_snapshot(): void
    {
        [$staff, $member] = $this->prepareLoan();

        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member))
            ->assertRedirect('/petugas/pinjaman');

        $loan = Pinjaman::firstOrFail();
        $this->assertSame('PNJ-TEST-0001', $loan->nomor_pinjaman);
        $this->assertSame('1.00000', $loan->jasa_flat_bulanan_snapshot);
        $this->assertSame('flat', $loan->snapshot_ketentuan['metode']);
        $this->assertSame([3, 6, 12], $loan->snapshot_ketentuan['tenor_bulan']);
        $this->assertSame('REF-CAIR-001', $loan->referensi_pencairan);
        $this->assertDatabaseCount('angsurans', 3);
        $this->assertSame(1000000.0, (float) $loan->angsuran()->sum('pokok_tagihan'));
        $this->assertSame('333334.00', $loan->angsuran()->orderByDesc('urutan')->first()->pokok_tagihan);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'loan.recorded',
            'subjek_id' => $loan->id,
        ]);
    }

    public function test_incomplete_references_inactive_member_and_invalid_terms_are_rejected(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $data = $this->loanData($member);
        unset($data['referensi_pencairan']);

        $this->actingAs($staff)->post('/petugas/pinjaman', $data)
            ->assertSessionHasErrors('referensi_pencairan');

        $data = $this->loanData($member);
        $data['pokok'] = '0';
        $data['tenor_bulan'] = '9';
        $this->post('/petugas/pinjaman', $data)
            ->assertSessionHasErrors(['pokok', 'tenor_bulan']);

        $member->update(['status_keanggotaan' => 'keluar']);
        $this->post('/petugas/pinjaman', $this->loanData($member))
            ->assertSessionHasErrors('anggota_id');

        $this->assertDatabaseCount('pinjamans', 0);
    }

    public function test_duplicate_loan_number_is_rejected(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $data = $this->loanData($member);

        $this->actingAs($staff)->post('/petugas/pinjaman', $data)
            ->assertRedirect('/petugas/pinjaman');
        $this->post('/petugas/pinjaman', $data)
            ->assertSessionHasErrors('nomor_pinjaman');

        $this->assertDatabaseCount('pinjamans', 1);
        $this->assertDatabaseCount('log_aktivitas', 1);
    }

    public function test_member_only_sees_their_own_loan_records(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $otherMember = $this->createMember('ANG-002', 'Anggota Lain', true);
        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member));

        $otherData = $this->loanData($otherMember);
        $otherData['nomor_pinjaman'] = 'PNJ-TEST-0002';
        $otherData['referensi_persetujuan'] = 'REF-APP-002';
        $otherData['referensi_pencairan'] = 'REF-CAIR-002';
        $this->post('/petugas/pinjaman', $otherData);

        $this->actingAs($member->user)->get('/anggota/pinjaman')
            ->assertOk()
            ->assertSee('PNJ-TEST-0001')
            ->assertDontSee('PNJ-TEST-0002')
            ->assertDontSee($otherMember->nomor_anggota);
    }

    public function test_routes_are_limited_to_staff_and_members(): void
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createUser('anggota', 'anggota@example.test');

        $this->actingAs($member)->get('/petugas/pinjaman')->assertForbidden();
        $this->actingAs($member)->post('/petugas/pinjaman')->assertForbidden();
        $this->actingAs($staff)->get('/anggota/pinjaman')->assertForbidden();
    }

    public function test_saved_terms_remain_unchanged_after_the_simulation_parameter_changes(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member));

        config(['pinjaman.aturan.jasa_flat_bulanan' => 2.0]);

        $this->assertSame(
            1.0,
            (float) Pinjaman::firstOrFail()->fresh()->snapshot_ketentuan['jasa_flat_bulanan'],
        );
    }

    public function test_member_can_view_own_installment_schedule_and_balance(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member));
        $loan = Pinjaman::firstOrFail();
        $firstInstallment = $loan->angsuran()->orderBy('urutan')->firstOrFail();
        $payment = Pembayaran::create([
            'nomor_pembayaran' => 'BYR-TEST-0001',
            'pinjaman_id' => $loan->id,
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal' => '10000.00',
            'metode' => 'tunai',
            'status' => 'tercatat',
            'dibuat_oleh' => $staff->id,
        ]);
        AlokasiPembayaran::create([
            'pembayaran_id' => $payment->id,
            'angsuran_id' => $firstInstallment->id,
            'alokasi_pokok' => '10000.00',
            'alokasi_jasa' => '0.00',
        ]);
        $reversedPayment = Pembayaran::create([
            'nomor_pembayaran' => 'BYR-TEST-0002',
            'pinjaman_id' => $loan->id,
            'tanggal_pembayaran' => now()->toDateString(),
            'nominal' => '50000.00',
            'metode' => 'tunai',
            'status' => 'dibatalkan',
            'dibuat_oleh' => $staff->id,
        ]);
        AlokasiPembayaran::create([
            'pembayaran_id' => $reversedPayment->id,
            'angsuran_id' => $firstInstallment->id,
            'alokasi_pokok' => '50000.00',
            'alokasi_jasa' => '0.00',
        ]);

        $this->actingAs($member->user)->get('/anggota/pinjaman/'.$loan->id)
            ->assertOk()
            ->assertSee('Jadwal angsuran')
            ->assertSee('Sisa pokok')
            ->assertSee('990.000');
    }

    public function test_long_tenor_schedule_handles_month_end_and_exact_principal_rounding(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $data = $this->loanData($member);
        $data['pokok'] = '1000000.03';
        $data['tenor_bulan'] = '12';
        $data['tanggal_pencairan'] = '2024-12-30';
        $data['angsuran_pertama_jatuh_tempo'] = '2025-01-31';

        $this->actingAs($staff)->post('/petugas/pinjaman', $data)
            ->assertRedirect('/petugas/pinjaman');

        $loan = Pinjaman::firstOrFail();
        $schedule = $loan->angsuran()->orderBy('urutan')->get();

        $this->assertCount(12, $schedule);
        $this->assertSame('2025-01-31', $schedule[0]->jatuh_tempo->toDateString());
        $this->assertSame('2025-02-28', $schedule[1]->jatuh_tempo->toDateString());
        $this->assertSame('2025-03-31', $schedule[2]->jatuh_tempo->toDateString());
        $this->assertSame(1000000.03, (float) $schedule->sum('pokok_tagihan'));
        $this->assertSame('83337.03', $schedule->last()->pokok_tagihan);
    }

    public function test_member_cannot_open_another_members_loan_detail(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $otherUser = $this->createUser('anggota', 'anggota-lain@example.test');
        $otherMember = Anggota::create([
            'nomor_anggota' => 'ANG-002',
            'nama' => 'Anggota Lain',
            'tanggal_bergabung' => now()->toDateString(),
            'status_keanggotaan' => 'aktif',
            'user_id' => $otherUser->id,
        ]);
        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member));
        $loan = Pinjaman::firstOrFail();

        $this->actingAs($otherUser)->get('/anggota/pinjaman/'.$loan->id)
            ->assertNotFound();
    }

    public function test_staff_records_full_installments_and_loan_becomes_paid_when_all_obligations_are_settled(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $data = $this->loanData($member);
        $data['tanggal_pencairan'] = now()->subMonths(3)->toDateString();
        $data['angsuran_pertama_jatuh_tempo'] = now()->subMonths(2)->toDateString();
        $this->actingAs($staff)->post('/petugas/pinjaman', $data);
        $loan = Pinjaman::firstOrFail();

        for ($installmentNumber = 1; $installmentNumber <= 3; $installmentNumber++) {
            $installment = $loan->angsuran()->where('urutan', $installmentNumber)->firstOrFail();

            $this->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
                'angsuran_id' => $installment->id,
                'tanggal_pembayaran' => today()->toDateString(),
                'metode' => 'tunai',
                'referensi' => 'BUKTI-'.$installmentNumber,
            ])->assertRedirect('/petugas/pinjaman/'.$loan->id);
        }

        $firstInstallment = $loan->angsuran()->where('urutan', 1)->firstOrFail();
        $this->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
            'angsuran_id' => $firstInstallment->id,
            'tanggal_pembayaran' => today()->toDateString(),
            'metode' => 'tunai',
        ])->assertSessionHasErrors('angsuran_id');

        $this->assertDatabaseCount('pembayarans', 3);
        $this->assertDatabaseCount('alokasi_pembayarans', 3);
        $this->assertSame(1030000.0, (float) $loan->pembayaran()->sum('nominal'));
        $this->assertSame('lunas', $loan->fresh()->status);
        $this->assertSame(0, $loan->angsuran()->where('status', '!=', 'dibayar')->count());
        $this->assertSame(3, $loan->pembayaran()->where('status', 'tercatat')->count());

        $this->actingAs($member->user)->get('/anggota/pinjaman/'.$loan->id)
            ->assertOk()
            ->assertSee('Riwayat pembayaran')
            ->assertSee('BYR-');
    }

    public function test_payment_cannot_be_recorded_for_a_future_installment_or_by_a_member(): void
    {
        [$staff, $member] = $this->prepareLoan();
        $this->actingAs($staff)->post('/petugas/pinjaman', $this->loanData($member));
        $loan = Pinjaman::firstOrFail();
        $installment = $loan->angsuran()->firstOrFail();

        $this->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
            'angsuran_id' => $installment->id,
            'tanggal_pembayaran' => today()->toDateString(),
            'metode' => 'tunai',
        ])->assertSessionHasErrors('angsuran_id');
        $this->assertDatabaseCount('pembayarans', 0);
        $this->assertDatabaseCount('alokasi_pembayarans', 0);

        $this->actingAs($member->user)->post('/petugas/pinjaman/'.$loan->id.'/pembayaran', [
            'angsuran_id' => $installment->id,
            'tanggal_pembayaran' => today()->toDateString(),
            'metode' => 'tunai',
        ])->assertForbidden();
    }

    private function prepareLoan(): array
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $member = $this->createMember('ANG-001', 'Anggota Uji', true);

        return [$staff, $member];
    }

    private function loanData(Anggota $member): array
    {
        return [
            'nomor_pinjaman' => 'PNJ-TEST-0001',
            'anggota_id' => $member->id,
            'pokok' => '1000000.00',
            'tenor_bulan' => '3',
            'tanggal_pencairan' => now()->toDateString(),
            'angsuran_pertama_jatuh_tempo' => now()->addMonth()->toDateString(),
            'referensi_persetujuan' => 'REF-APP-001',
            'referensi_pencairan' => 'REF-CAIR-001',
        ];
    }

    private function createMember(string $number, string $name, bool $linked = false): Anggota
    {
        $user = $linked ? $this->createUser('anggota', 'anggota@example.test') : null;

        return Anggota::create([
            'nomor_anggota' => $number,
            'nama' => $name,
            'tanggal_bergabung' => now()->toDateString(),
            'status_keanggotaan' => 'aktif',
            'user_id' => $user?->id,
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
