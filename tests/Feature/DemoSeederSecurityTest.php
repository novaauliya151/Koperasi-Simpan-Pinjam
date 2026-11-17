<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoSeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_accounts_are_not_seeded_without_private_passwords(): void
    {
        config(['app.demo_seed_passwords' => [
            'admin' => null,
            'petugas' => null,
            'anggota' => null,
        ]]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('anggotas', 0);
    }

    public function test_demo_accounts_use_passwords_from_private_configuration(): void
    {
        $passwords = [
            'admin' => Str::random(32),
            'petugas' => Str::random(32),
            'anggota' => Str::random(32),
        ];
        config(['app.demo_seed_passwords' => $passwords]);

        $this->seed(DatabaseSeeder::class);

        foreach ([
            'admin@gmail.com' => $passwords['admin'],
            'petugas@gmail.com' => $passwords['petugas'],
            'anggota@gmail.com' => $passwords['anggota'],
        ] as $email => $password) {
            $user = \App\Models\User::query()->where('email', $email)->firstOrFail();
            $this->assertTrue(Hash::check($password, $user->password));
        }

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('anggotas', [
            'nomor_anggota' => 'DEMO-001',
            'status_keanggotaan' => 'aktif',
        ]);
    }
}
