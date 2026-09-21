<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'batch_id' => $this->batch_id,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'is_coach'=> $this->is_coach ? 1 : 0,
            'reference'=> $this->reference,
            'reference_detail'=> $this->reference_detail,
            'profile_photo'=> $this->profile_photo ? asset('uploads/participants/profile-photos/'.$this->profile_photo) : '',
            'is_multiple_batch'=>$this->participantBatches->count() > 1 ? 'yes' : 'no',
            'is_registration_fees_paid'=> $this->is_registration_fees_paid ? 1 : 0,
            'batch'=> $this->whenLoaded('batch'),
        ];
    }
}
