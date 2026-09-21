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
        Schema::table('group_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('participant_id')->nullable()->after('coach_id');
            $table->unsignedBigInteger('batch_group_id')->nullable()->change();
            $table->boolean('is_replied')->default(0);
            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_messages', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
            $table->dropColumn('participant_id');
            $table->unsignedBigInteger('batch_group_id')->change();
            $table->dropColumn('is_replied');
        });
    }
};
