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
        Schema::table('bookings', function (Blueprint $table) {
            // Pengguna yang memiliki riwayat peminjaman tidak dihapus permanen.
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Ruangan yang memiliki riwayat cukup dinonaktifkan.
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->restrictOnDelete();

            $table->text('purpose');
            $table->unsignedInteger('participant_count');

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            // Terisi setelah pengajuan diperiksa admin.
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Mendukung pencarian jadwal dan pengecekan bentrok.
            $table->index(
                ['room_id', 'status', 'starts_at', 'ends_at'],
                'bookings_room_schedule_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['room_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex('bookings_room_schedule_index');
            $table->dropColumn([
                'user_id', 'room_id', 'purpose', 'participant_count',
                'starts_at', 'ends_at', 'status', 'reviewed_by',
                'reviewed_at', 'rejection_reason',
            ]);
        });
    }
};
