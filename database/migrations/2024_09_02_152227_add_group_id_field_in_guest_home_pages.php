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
        Schema::table('guest_home_pages', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_group_id')->nullable();
            $table->foreign('batch_group_id')->references('id')->on('batch_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_home_pages', function (Blueprint $table) {
            $table->dropForeign(['batch_group_id']);
            $table->dropColumn('batch_group_id');
        });
    }
};
