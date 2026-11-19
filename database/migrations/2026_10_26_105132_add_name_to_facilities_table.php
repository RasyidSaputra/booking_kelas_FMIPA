<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom nama fasilitas.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Menambahkan kolom nama fasilitas.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('name', 100)->unique();
        });
    }

    /**
     * Menghapus kolom nama fasilitas.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn('name');
        });
    }
};
