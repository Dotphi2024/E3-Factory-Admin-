<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantPayment extends Model
{
    use HasFactory;

    protected $casts = [
        'is_qr_used' => 'boolean',
        'is_transferred' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * The current beneficiary/participant entitled to this payment.
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    /**
     * The original person who actually paid for this transaction.
     */
    public function originalPayer()
    {
        return $this->belongsTo(Participant::class, 'original_payer_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function replacement()
    {
        return $this->belongsTo(CandidateReplacement::class, 'replacement_id');
    }
}
