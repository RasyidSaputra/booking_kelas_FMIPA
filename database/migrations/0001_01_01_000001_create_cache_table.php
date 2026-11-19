<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuat tabel penyimpanan cache dan cache lock.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Membuat tabel penyimpanan cache dan cache lock.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Menghapus tabel cache beserta tabel lock.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
