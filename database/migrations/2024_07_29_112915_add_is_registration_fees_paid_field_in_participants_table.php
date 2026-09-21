<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('is_registration_fees_paid')->default(0);
            $table->string('registration_type')->nullable();
        });
        DB::transaction(function () {
            $participants = Participant::where('paid_amount', '!=', 0)->get();
            foreach ($participants as $participant) {
                $participant->is_registration_fees_paid = true;
                $participant->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('is_registration_fees_paid');
            $table->dropColumn('registration_type');
        });
    }
};
