<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan identitas unik dan role hanya jika kolomnya belum ada pada tabel users.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Menambahkan identitas unik dan role hanya jika kolomnya belum ada pada tabel users.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
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
                $table->enum('role', ['admin', 'dosen', 'mahasiswa'])->default('mahasiswa');
            });
        }
    }

    /**
     * Menghapus indeks identitas dan kolom identitas serta role.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identity_number']);
            $table->dropColumn(['identity_number', 'role']);
        });
    }
};
