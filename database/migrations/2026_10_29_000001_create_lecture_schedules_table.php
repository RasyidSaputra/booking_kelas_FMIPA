<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecture_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('semester', 100);
            $table->string('purpose', 500);
            $table->unsignedInteger('participant_count');
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->date('cancelled_from')->nullable();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('lecture_schedule_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('lecture_date')->nullable();
            $table->boolean('is_lecture_exception')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lecture_schedule_id');
            $table->dropColumn(['lecture_date', 'is_lecture_exception']);
        });
        Schema::dropIfExists('lecture_schedules');
    }
};
