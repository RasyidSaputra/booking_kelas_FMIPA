<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat tabel token akses personal untuk autentikasi Laravel Sanctum.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Membuat tabel token akses personal untuk autentikasi Laravel Sanctum.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel token akses personal Sanctum.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
