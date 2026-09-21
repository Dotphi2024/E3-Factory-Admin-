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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coach_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('batch_group_id');
            $table->unsignedBigInteger('batch_schedule_id');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('type')->nullable();
            $table->string('link')->nullable();
            $table->string('venue')->nullable();
            $table->string('status')->default('active');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('batch_group_id')->references('id')->on('batch_groups')->onDelete('cascade');
            $table->foreign('batch_schedule_id')->references('id')->on('batch_schedules')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
