<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah role peminjam lama menjadi mahasiswa lalu membatasi role ke admin, dosen, dan mahasiswa.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Mengubah role peminjam lama menjadi mahasiswa lalu membatasi role ke admin, dosen, dan mahasiswa.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Terima nilai lama dan baru sebelum memindahkan data pengguna.
            $table->enum('role', ['admin', 'peminjam', 'dosen', 'mahasiswa'])
                ->default('mahasiswa')->change();
        });

        DB::table('users')->where('role', 'peminjam')->update(['role' => 'mahasiswa']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'dosen', 'mahasiswa'])
                ->default('mahasiswa')->change();
        });
    }

    /**
     * Menggabungkan role dosen dan mahasiswa menjadi peminjam untuk mengembalikan skema role lama.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'peminjam', 'dosen', 'mahasiswa'])
                ->default('peminjam')->change();
        });

        // Skema lama hanya memiliki admin/peminjam; klasifikasi dosen dan
        // mahasiswa digabung menjadi peminjam jika migration ini di-rollback.
        DB::table('users')->whereIn('role', ['dosen', 'mahasiswa'])->update(['role' => 'peminjam']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'peminjam'])
                ->default('peminjam')->change();
        });
    }
};
