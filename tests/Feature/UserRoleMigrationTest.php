<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserRoleMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_borrowers_are_converted_and_other_accounts_are_preserved(): void
    {
        // Simulasikan database transisi yang masih memiliki nilai peminjam.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'peminjam', 'dosen', 'mahasiswa'])
                ->default('peminjam')->change();
        });

        $borrower = User::factory()->create(['role' => 'peminjam']);
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);

        $migration = require database_path('migrations/2026_10_26_172908_update_roles_on_users_table.php');
        $migration->up();

        $this->assertSame('mahasiswa', $borrower->fresh()->role);
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame('dosen', $lecturer->fresh()->role);
        foreach ([$borrower, $admin, $lecturer] as $account) {
            $this->assertSame($account->email, $account->fresh()->email);
            $this->assertSame($account->password, $account->fresh()->password);
        }
        $this->assertDatabaseCount('users', 3);

        $id = DB::table('users')->insertGetId([
            'name' => 'Akun tanpa role eksplisit',
            'email' => 'default-role@example.com',
            'password' => $admin->password,
        ]);
        $this->assertSame('mahasiswa', DB::table('users')->where('id', $id)->value('role'));
    }
}
