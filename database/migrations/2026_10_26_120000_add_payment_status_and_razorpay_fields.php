<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('participants', 'payment_status')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->string('payment_status', 20)->default('unpaid')->after('is_registration_fees_paid');
            });

            // Backfill existing records based on is_registration_fees_paid
            DB::table('participants')
                ->where('is_registration_fees_paid', 1)
                ->update(['payment_status' => 'paid']);

            DB::table('participants')
                ->where('is_registration_fees_paid', 0)
                ->update(['payment_status' => 'unpaid']);
        }

        if (!Schema::hasColumn('participant_payments', 'razorpay_order_id')) {
            Schema::table('participant_payments', function (Blueprint $table) {
                $table->string('razorpay_order_id')->nullable()->after('payment_mode');
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
                $table->text('razorpay_signature')->nullable()->after('razorpay_payment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('participants', 'payment_status')) {
            Schema::table('participants', function (Blueprint $table) {
                $table->dropColumn('payment_status');
            });
        }

        if (Schema::hasColumn('participant_payments', 'razorpay_order_id')) {
            Schema::table('participant_payments', function (Blueprint $table) {
                $table->dropColumn(['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
            });
        }
    }
};
