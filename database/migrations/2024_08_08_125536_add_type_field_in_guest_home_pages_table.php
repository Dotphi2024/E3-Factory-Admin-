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
            $table->string('type')->default('guest');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_home_pages', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
