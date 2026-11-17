<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\JenisSimpanan;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SavingsPaginationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_savings_list_filters_and_paginates_ten_thousand_rows(): void
    {
        $staff = $this->createUser();
        $member = Anggota::create([
            'nomor_anggota' => 'ANG-LOAD-001',
            'nama' => 'Anggota Beban Uji',
            'tanggal_bergabung' => today()->toDateString(),
            'status_keanggotaan' => 'aktif',
        ]);
        $type = JenisSimpanan::create([
            'kode' => 'LOAD',
            'nama' => 'Jenis Beban Uji',
            'is_active' => true,
        ]);
        $now = now();

        foreach (array_chunk(range(1, 10000), 90) as $batch) {
            $rows = array_map(fn (int $number): array => [
                'nomor_transaksi' => 'LOAD-'.str_pad((string) $number, 8, '0', STR_PAD_LEFT),
                'anggota_id' => $member->id,
                'jenis_simpanan_id' => $type->id,
                'tanggal_transaksi' => today()->toDateString(),
                'nominal' => '100.00',
                'metode' => 'tunai',
                'status' => 'tercatat',
                'dibuat_oleh' => $staff->id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $batch);

            DB::table('transaksi_simpanans')->insert($rows);
        }

        $queryCount = 0;
        DB::listen(function (QueryExecuted $query) use (&$queryCount): void {
            $queryCount++;
        });
        $startedAt = hrtime(true);

        $this->actingAs($staff)->get('/petugas/simpanan?anggota_id='.$member->id)
            ->assertOk()
            ->assertSee('pagination')
            ->assertSee('table-responsive')
            ->assertSee('col-12 col-md-6')
            ->assertSee('LOAD-00010000')
            ->assertDontSee('LOAD-00009985');

        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;
        $this->assertLessThan(
            3000,
            $elapsedMilliseconds,
            'The filtered 10,000-row listing exceeded the PRD single-request threshold.',
        );
        $this->assertLessThan(20, $queryCount);

        $this->get('/petugas/simpanan?anggota_id='.$member->id.'&page=2')
            ->assertOk()
            ->assertSee('LOAD-00009985')
            ->assertDontSee('LOAD-00009986');
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Petugas Beban Uji',
            'email' => 'petugas-load@example.test',
            'password' => 'password-rahasia',
            'role' => 'petugas',
            'is_active' => true,
        ]);
    }
}
