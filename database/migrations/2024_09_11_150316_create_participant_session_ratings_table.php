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
        Schema::create('participant_session_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('batch_schedule_id');
            $table->unsignedBigInteger('coach_id');
            $table->integer('rating');
            $table->string('rating_type')->default('coach_to_participant');
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('batch_schedule_id')->references('id')->on('batch_schedules')->onDelete('cascade');
            $table->foreign('coach_id')->references('id')->on('participants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_session_ratings');
    }
};
