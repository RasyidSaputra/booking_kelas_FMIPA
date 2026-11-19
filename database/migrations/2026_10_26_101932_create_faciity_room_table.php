<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat tabel pivot awal bernama faciity_room.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Membuat tabel pivot awal bernama faciity_room.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::create('faciity_room', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel pivot awal faciity_room.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::dropIfExists('faciity_room');
    }
};
