<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Participant;
use App\Models\ParticipantBatch;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participant_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('batch_id');
            $table->boolean('is_registration_fees_paid');
            $table->boolean('is_active')->default(1);
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->timestamps();
        });
        $participants = Participant::all();
        foreach ($participants as $participant) {
            $participant_batch = new ParticipantBatch();
            $participant_batch->participant_id  = $participant->id;
            $participant_batch->batch_id  = $participant->batch_id;
            $participant_batch->is_registration_fees_paid = $participant->is_registration_fees_paid;
            $participant_batch->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_batches');
    }
};
