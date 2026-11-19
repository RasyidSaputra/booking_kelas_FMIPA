<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyediakan slot migration identitas dan role yang masih kosong; kolom ditambahkan oleh migration lanjutan.
 * Konsep Laravel: Database Migration; kerangka tanpa perubahan skema.
 */
return new class extends Migration
{
    /**
     * Menyediakan slot migration identitas dan role yang masih kosong; kolom ditambahkan oleh migration lanjutan.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Migration awal dipertahankan sesuai riwayatnya.
            // Kolom ditambahkan melalui migration 2026_10_26_105135.
        });
    }

    /**
     * Menyediakan slot rollback kosong karena migration ini tidak mengubah skema.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
