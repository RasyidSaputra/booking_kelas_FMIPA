<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat tabel awal ruangan dengan identitas dan timestamps.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Membuat tabel awal ruangan dengan identitas dan timestamps.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel awal ruangan.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
