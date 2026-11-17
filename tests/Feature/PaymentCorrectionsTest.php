<?php

namespace Tests\Feature;

use App\Models\AlokasiPembayaran;
use App\Models\Anggota;
use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\User;
use App\Services\LoanScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentCorrectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_reverse_a_payment_atomically_and_restore_loan_balance(): void
    {
        [$staff, $member, $loan, $payments] = $this->preparePaidLoan();
        $original = $payments[0];

        $this->actingAs($staff)->post(
            '/petugas/pinjaman/pembayaran/'.$original->id.'/pembatalan',
            ['alasan_koreksi' => 'Pembayaran tercatat dua kali.'],
        )->assertRedirect('/petugas/pinjaman/'.$loan->id);

        $reversal = Pembayaran::query()
            ->where('pembalikan_dari_id', $original->id)
            ->firstOrFail();

        $this->assertDatabaseHas('pembayarans', [
            'id' => $original->id,
            'status' => 'dibatalkan',
            'alasan_koreksi' => 'Pembayaran tercatat dua kali.',
        ]);
        $this->assertDatabaseHas('pembayarans', [
            'id' => $reversal->id,
            'status' => 'pembalikan',
            'nominal' => $original->nominal,
            'alasan_koreksi' => 'Pembayaran tercatat dua kali.',
        ]);
        $this->assertSame(3, $loan->pembayaran()->count());
        $this->assertSame(1, $original->alokasi()->count());
        $this->assertSame(0, $reversal->alokasi()->count());
        $this->assertSame('aktif', $loan->fresh()->status);
        $this->assertSame('terlambat', $loan->angsuran()->where('urutan', 1)->first()->status);
        $this->assertDatabaseHas('log_aktivitas', [
            'aksi' => 'loan.payment.reversed',
            'subjek_id' => $original->id,
            'alasan' => 'Pembayaran tercatat dua kali.',
        ]);

        $this->actingAs($member->user)->get('/anggota/pinjaman/'.$loan->id)
            ->assertOk()
            ->assertSee('343.333')
            ->assertSee('Dibatalkan')
            ->assertSee('Pembalikan');

        $this->actingAs($member->user)
            ->get('/anggota/pembayaran/'.$reversal->id.'/bukti')
            ->assertOk()
            ->assertSee('Bukti pembalikan pembayaran')
            ->assertSee('Pembalikan dari pembayaran');
    }

    public function test_reversal_requires_reason_and_cannot_be_repeated(): void
    {
        [$staff, , , $payments] = $this->preparePaidLoan();
        $payment = $payments[0];
        $url = '/petugas/pinjaman/pembayaran/'.$payment->id.'/pembatalan';

        $this->actingAs($staff)->post($url, ['alasan_koreksi' => ''])
            ->assertSessionHasErrors('alasan_koreksi');
        $this->assertSame('tercatat', $payment->fresh()->status);
        $this->assertDatabaseCount('pembayarans', 3);

        $this->post($url, ['alasan_koreksi' => 'Koreksi pembayaran.'])
            ->assertRedirect();
        $this->post($url, ['alasan_koreksi' => 'Percobaan ulang.'])
            ->assertSessionHasErrors('pembayaran');

        $reversal = Pembayaran::query()
            ->where('pembalikan_dari_id', $payment->id)
            ->firstOrFail();
        $this->post(
            '/petugas/pinjaman/pembayaran/'.$reversal->id.'/pembatalan',
            ['alasan_koreksi' => 'Percobaan pembalikan balik.'],
        )->assertSessionHasErrors('pembayaran');

        $this->assertDatabaseCount('pembayarans', 4);
        $this->assertDatabaseCount('log_aktivitas', 1);
    }

    public function test_receipt_is_printable_and_member_access_is_limited_to_own_loan(): void
    {
        [$staff, $member, , $payments] = $this->preparePaidLoan();
        $payment = $payments[0];
        $otherMemberUser = $this->createUser('anggota', 'anggota-lain@example.test');
        $this->createMember($otherMemberUser, 'ANG-002');

        $this->actingAs($staff)
            ->get('/petugas/pembayaran/'.$payment->id.'/bukti')
            ->assertOk()
            ->assertSee('Bukti pembayaran angsuran')
            ->assertSee($payment->nomor_pembayaran)
            ->assertSee('Alokasi pokok')
            ->assertSee('Sisa kewajiban saat ini')
            ->assertSee('Cetak bukti');

        $this->actingAs($member->user)
            ->get('/anggota/pembayaran/'.$payment->id.'/bukti')
            ->assertOk()
            ->assertSee($payment->nomor_pembayaran);

        $this->actingAs($otherMemberUser)
            ->get('/anggota/pembayaran/'.$payment->id.'/bukti')
            ->assertNotFound();
    }

    public function test_member_cannot_reverse_a_payment(): void
    {
        [, $member, , $payments] = $this->preparePaidLoan();

        $this->actingAs($member->user)->post(
            '/petugas/pinjaman/pembayaran/'.$payments[0]->id.'/pembatalan',
            ['alasan_koreksi' => 'Tidak diizinkan.'],
        )->assertForbidden();

        $this->assertSame('tercatat', $payments[0]->fresh()->status);
        $this->assertDatabaseCount('pembayarans', 3);
    }

    private function preparePaidLoan(): array
    {
        $staff = $this->createUser('petugas', 'petugas@example.test');
        $memberUser = $this->createUser('anggota', 'anggota@example.test');
        $member = $this->createMember($memberUser, 'ANG-001');
        $loan = Pinjaman::create([
            'nomor_pinjaman' => 'PNJ-'.Str::upper(Str::random(12)),
            'anggota_id' => $member->id,
            'pokok' => '1000000.00',
            'tenor_bulan' => 3,
            'tanggal_pencairan' => now()->subMonths(4)->toDateString(),
            'angsuran_pertama_jatuh_tempo' => now()->subMonths(3)->toDateString(),
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
        $payments = [];

        foreach ($loan->angsuran()->orderBy('urutan')->get() as $installment) {
            $payment = Pembayaran::create([
                'nomor_pembayaran' => 'BYR-'.Str::upper(Str::random(12)),
                'pinjaman_id' => $loan->id,
                'tanggal_pembayaran' => now()->toDateString(),
                'nominal' => number_format(
                    (float) $installment->pokok_tagihan
                    + (float) $installment->jasa_tagihan,
                    2,
                    '.',
                    '',
                ),
                'metode' => 'tunai',
                'referensi' => 'REF-TEST',
                'status' => 'tercatat',
                'dibuat_oleh' => $staff->id,
            ]);
            AlokasiPembayaran::create([
                'pembayaran_id' => $payment->id,
                'angsuran_id' => $installment->id,
                'alokasi_pokok' => $installment->pokok_tagihan,
                'alokasi_jasa' => $installment->jasa_tagihan,
            ]);
            $payments[] = $payment;
        }

        $loan->update(['status' => 'lunas']);

        return [$staff, $member, $loan, $payments];
    }

    private function createMember(User $user, string $number): Anggota
    {
        return Anggota::create([
            'nomor_anggota' => $number,
            'nama' => 'Anggota Uji',
            'tanggal_bergabung' => today()->toDateString(),
            'status_keanggotaan' => 'aktif',
            'user_id' => $user->id,
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
