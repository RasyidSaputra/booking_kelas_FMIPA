<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan detail ruangan setelah memastikan tabel terkait belum berisi data yang memerlukan backfill.
 * Konsep Laravel: Database Migration (anonymous class), Schema Builder.
 */
return new class extends Migration
{
    /**
     * Menambahkan detail ruangan setelah memastikan tabel terkait belum berisi data yang memerlukan backfill.
     * Konsep Laravel: Database Migration, Schema Builder / perubahan data pada up().
     */
    public function up(): void
    {
        // Tabel awal hanya memiliki id/timestamps. Data lama memerlukan
        // pemetaan nilai wajib yang tidak boleh diisi dengan tebakan.
        foreach (['rooms', 'facilities', 'faciity_room', 'bookings'] as $table) {
            if (DB::table($table)->exists()) {
                throw new RuntimeException(
                    "Tabel {$table} sudah berisi data. Siapkan backfill kolom wajib sebelum melanjutkan migration."
                );
            }
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->unsignedInteger('capacity');
            $table->string('location', 255);

            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();

            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Menghapus indeks kode dan kolom detail ruangan yang ditambahkan migration ini.
     * Konsep Laravel: Database Migration, Rollback melalui down().
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code', 'name', 'capacity', 'location',
                'description', 'photo_path', 'is_active',
            ]);
        });
    }
};
