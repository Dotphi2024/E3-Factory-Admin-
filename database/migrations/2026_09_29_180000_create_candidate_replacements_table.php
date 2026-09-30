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
        // 1. Create candidate_replacements table
        if (!Schema::hasTable('candidate_replacements')) {
            Schema::create('candidate_replacements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedBigInteger('original_participant_id');
                $table->string('candidate_first_name');
                $table->string('candidate_last_name');
                $table->string('candidate_mobile');
                $table->string('candidate_email')->nullable();
                $table->json('candidate_data')->nullable();
                $table->unsignedBigInteger('replacement_participant_id')->nullable();
                $table->unsignedBigInteger('original_participant_batch_id')->nullable();
                $table->unsignedBigInteger('new_participant_batch_id')->nullable();
                $table->decimal('total_amount_transferred', 10, 2)->default(0.00);
                $table->decimal('registration_fee_transferred', 10, 2)->default(0.00);
                $table->decimal('session_fees_transferred', 10, 2)->default(0.00);
                $table->json('paid_sessions_transferred')->nullable();
                $table->json('transferred_payment_ids')->nullable();
                $table->text('reason');
                $table->boolean('policy_accepted')->default(1);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
                $table->foreign('original_participant_id')->references('id')->on('participants')->onDelete('cascade');
                $table->foreign('replacement_participant_id')->references('id')->on('participants')->nullOnDelete();
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        // 2. Add audit fields to participant_payments
        Schema::table('participant_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('participant_payments', 'original_payer_id')) {
                $table->unsignedBigInteger('original_payer_id')->nullable()->after('participant_id');
                $table->foreign('original_payer_id')->references('id')->on('participants')->nullOnDelete();
            }
            if (!Schema::hasColumn('participant_payments', 'is_transferred')) {
                $table->boolean('is_transferred')->default(0)->after('is_qr_used');
            }
            if (!Schema::hasColumn('participant_payments', 'replacement_id')) {
                $table->unsignedBigInteger('replacement_id')->nullable()->after('is_transferred');
                $table->foreign('replacement_id')->references('id')->on('candidate_replacements')->nullOnDelete();
            }
        });

        // Backfill original_payer_id = participant_id for existing payments
        DB::statement('UPDATE participant_payments SET original_payer_id = participant_id WHERE original_payer_id IS NULL');

        // 3. Add enrollment tracking fields to participant_batches
        Schema::table('participant_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('participant_batches', 'enrollment_status')) {
                $table->string('enrollment_status')->default('active')->after('is_active');
            }
            if (!Schema::hasColumn('participant_batches', 'replaced_by_participant_id')) {
                $table->unsignedBigInteger('replaced_by_participant_id')->nullable()->after('enrollment_status');
                $table->foreign('replaced_by_participant_id')->references('id')->on('participants')->nullOnDelete();
            }
            if (!Schema::hasColumn('participant_batches', 'replacement_id')) {
                $table->unsignedBigInteger('replacement_id')->nullable()->after('replaced_by_participant_id');
                $table->foreign('replacement_id')->references('id')->on('candidate_replacements')->nullOnDelete();
            }
        });

        // 4. Add status field to participants
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('participant_batches', function (Blueprint $table) {
            if (Schema::hasColumn('participant_batches', 'replacement_id')) {
                $table->dropForeign(['replacement_id']);
                $table->dropColumn('replacement_id');
            }
            if (Schema::hasColumn('participant_batches', 'replaced_by_participant_id')) {
                $table->dropForeign(['replaced_by_participant_id']);
                $table->dropColumn('replaced_by_participant_id');
            }
            if (Schema::hasColumn('participant_batches', 'enrollment_status')) {
                $table->dropColumn('enrollment_status');
            }
        });

        Schema::table('participant_payments', function (Blueprint $table) {
            if (Schema::hasColumn('participant_payments', 'replacement_id')) {
                $table->dropForeign(['replacement_id']);
                $table->dropColumn('replacement_id');
            }
            if (Schema::hasColumn('participant_payments', 'original_payer_id')) {
                $table->dropForeign(['original_payer_id']);
                $table->dropColumn('original_payer_id');
            }
            if (Schema::hasColumn('participant_payments', 'is_transferred')) {
                $table->dropColumn('is_transferred');
            }
        });

        Schema::dropIfExists('candidate_replacements');
    }
};
