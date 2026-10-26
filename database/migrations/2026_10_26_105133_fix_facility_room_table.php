<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pertahankan tabel lama dan perbaiki salah ketik pada namanya.
        Schema::rename('faciity_room', 'facility_room');

        Schema::table('facility_room', function (Blueprint $table) {
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->foreignId('facility_id')
                ->constrained('facilities')
                ->cascadeOnDelete();

            // Satu fasilitas tidak boleh tercatat dua kali pada ruangan sama.
            $table->unique(['room_id', 'facility_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_room', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['facility_id']);
            $table->dropUnique(['room_id', 'facility_id']);
            $table->dropColumn(['room_id', 'facility_id']);
        });

        Schema::rename('facility_room', 'faciity_room');
    }
};
