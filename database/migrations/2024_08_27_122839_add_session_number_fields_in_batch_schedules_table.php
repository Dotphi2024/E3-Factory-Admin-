<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Batch;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batch_schedules', function (Blueprint $table) {
            $table->integer('session_number')->nullable();
        });
        $batches = Batch::with('schedules')->get();
        foreach ($batches as $batch) {
            $session_number = 1;
            foreach ($batch->schedules as $schedule) {
                $schedule->session_number = $session_number;
                $session_number++;
                $schedule->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_schedules', function (Blueprint $table) {
            $table->dropColumn('session_number');
        });
    }
};
