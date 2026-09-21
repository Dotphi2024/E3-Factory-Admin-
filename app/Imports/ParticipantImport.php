<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use \PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Participant;
use App\Models\Batch;
use App\Models\ParticipantBatch;
use Auth;

class ParticipantImport implements WithHeadingRow,ToModel,WithChunkReading
{
   public function model(array $row){
      if(empty(array_filter($row))){
        return null;
      }
      $batch = Batch::where('name',$row['batch_name'])->first();
      if(!$batch){
        return null;
      }
      if(!$row['mobile']){
        return null;
      }
      $participant = Participant::where('mobile',$row['mobile'])->first();
      if($participant){
        return null;
      }
      try{
        DB::beginTransaction();
        $participant = new Participant();
        $participant->first_name = $row['first_name'];
        $participant->last_name = $row['last_name'];
        $participant->mobile = $row['mobile'];
        $participant->batch_id = $batch->id;
        $participant->email = $row['email'];
        if (!empty($row['birth_date'])) {
            try {
                if (is_numeric($row['birth_date'])) {
                    $participant->birth_date = Date::excelToDateTimeObject($row['birth_date'])->format('Y-m-d');
                } else {
                    $participant->birth_date = date('Y-m-d', strtotime($row['birth_date']));
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse birth_date during import for row mobile " . $row['mobile'] . ": " . $e->getMessage());
            }
        }
        $participant->address = $row['address'];
        $participant->country = $row['country'];
        $participant->state = $row['state'];
        $participant->city = $row['city'];
        $participant->reference = $row['reference'] ?? null;
        if (!empty($row['reference']) && strtolower($row['reference']) === 'member' && !empty($row['reference_detail'])) {
            $referrer = Participant::where('mobile', $row['reference_detail'])
                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), $row['reference_detail'])
                ->first();
            if ($referrer) {
                $participant->reference_detail = $referrer->id;
            } else {
                $participant->reference_detail = $row['reference_detail'];
            }
        } else {
            $participant->reference_detail = $row['reference_detail'] ?? null;
        }
        $participant->type = 'student';
        $participant->added_by = Auth::user()->id;

        $register_amount = floatval($row['register_amount'] ?? $row['registration_amount'] ?? 0);

        $participant->total_amount = $batch->registration_fee + ($batch->number_of_sessions * $batch->fee_per_session);
        $participant->paid_amount = $register_amount;
        $participant->due_amount = $participant->total_amount - $register_amount;
        $participant->registration_type = 'admin';

        if ($register_amount >= $batch->registration_fee) {
            $participant->is_registration_fees_paid = true;
        }

        $participant->save();

        $participantBatch = new ParticipantBatch();
        $participantBatch->participant_id = $participant->id;
        $participantBatch->batch_id = $batch->id;
        if ($register_amount >= $batch->registration_fee) {
            $participantBatch->is_registration_fees_paid = true;
        } else {
            $participantBatch->is_registration_fees_paid = 0;
        }
        $participantBatch->save();

        if ($register_amount > 0) {
            $payment = new \App\Models\ParticipantPayment();
            $payment->participant_id = $participant->id;
            $payment->batch_id = $batch->id;
            $payment->transaction_id = 'IMPORTED_' . time() . '_' . rand(100, 999);
            $payment->amount = $register_amount;
            $payment->payment_mode = 'Other';
            $payment->payment_for = 'registration';
            $payment->save();
        }

        DB::commit();

        // Send automated WhatsApp confirmation to the imported participant
        try {
            $msgContent = "Dear " . $participant->first_name . " " . $participant->last_name . ",\n\n" .
                           "Thank you for registering. We have received your registration payment of Rs. " . $register_amount . ".\n" .
                           "Your pending amount is Rs. " . $participant->due_amount . ".\n\n" .
                           "Best regards,\nE3 Admin Team";

            $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);
            if (strlen($mobile) === 10) {
                $mobile = '91' . $mobile;
            }

            $apiUrl   = env('WHATSAPP_API_URL', 'http://api.dotphi.com/wapp/v2/api/send');
            $apiToken = env('WHATSAPP_API_TOKEN', '');

            if (!empty($apiToken) && !empty($mobile)) {
                \Illuminate\Support\Facades\Http::get($apiUrl, [
                    'apikey' => $apiToken,
                    'mobile' => $mobile,
                    'msg'    => $msgContent,
                ]);
            }
        } catch (\Exception $ex) {
            Log::error("Failed to send automatic WhatsApp message on Excel Import for " . $participant->mobile . ": " . $ex->getMessage());
        }
      }
      catch(\Exception $e){
        DB::rollBack();
        throw $e;
      }
   }

   public function chunkSize(): int
    {
        return 100;
    }
}
