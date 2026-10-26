<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration awal mungkin sudah tercatat selesai ketika masih kosong.
        // Periksa per kolom untuk mengakomodasi database yang sudah menerapkannya.
        if (! Schema::hasColumn('users', 'identity_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('identity_number', 30)->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'peminjam'])->default('peminjam');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identity_number']);
            $table->dropColumn(['identity_number', 'role']);
        });
    }
};
