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
   protected $sendWhatsapp;
   protected $importedCount = 0;

   public function __construct(bool $sendWhatsapp = false)
   {
       $this->sendWhatsapp = $sendWhatsapp;
   }

   public function getImportedCount(): int
   {
       return $this->importedCount;
   }

   public function model(array $row){
      // Keep execution limit fresh during large imports
      set_time_limit(60);

      if(empty(array_filter($row))){
        return null;
      }

      $batchName = trim($row['batch_name'] ?? '');
      if(empty($batchName)){
        return null;
      }

      $batch = Batch::where('name', $batchName)
                    ->orWhereRaw('TRIM(LOWER(name)) = ?', [strtolower($batchName)])
                    ->first();
      if(!$batch){
        Log::warning("Batch not found during participant import: " . $batchName);
        return null;
      }

      $rawMobile = trim((string)($row['mobile'] ?? ''));
      $cleanMobile = preg_replace('/[^0-9]/', '', $rawMobile);
      if(empty($cleanMobile)){
        return null;
      }
      // If starts with 91 and has 12 digits, strip country code for DB matching
      if(strlen($cleanMobile) === 12 && substr($cleanMobile, 0, 2) === '91'){
        $cleanMobile = substr($cleanMobile, 2);
      }

      $participant = Participant::where('mobile', $cleanMobile)
                                ->orWhere('mobile', $rawMobile)
                                ->first();
      if($participant){
        return null;
      }

      try{
        DB::beginTransaction();
        $participant = new Participant();
        $participant->first_name = trim($row['first_name'] ?? '');
        $participant->last_name = trim($row['last_name'] ?? '');
        $participant->mobile = $cleanMobile;
        $participant->batch_id = $batch->id;
        $participant->email = trim($row['email'] ?? '');
        if (!empty($row['birth_date'])) {
            try {
                if (is_numeric($row['birth_date'])) {
                    $participant->birth_date = Date::excelToDateTimeObject($row['birth_date'])->format('Y-m-d');
                } else {
                    $participant->birth_date = date('Y-m-d', strtotime($row['birth_date']));
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse birth_date during import for row mobile " . $cleanMobile . ": " . $e->getMessage());
            }
        }
        $participant->address = $row['address'] ?? null;
        $participant->country = $row['country'] ?? null;
        $participant->state = $row['state'] ?? null;
        $participant->city = $row['city'] ?? null;
        $participant->reference = $row['reference'] ?? null;
        if (!empty($row['reference']) && strtolower(trim($row['reference'])) === 'member' && !empty($row['reference_detail'])) {
            $refDetail = trim($row['reference_detail']);
            $referrer = Participant::where('mobile', $refDetail)
                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), $refDetail)
                ->first();
            if ($referrer) {
                $participant->reference_detail = $referrer->id;
            } else {
                $participant->reference_detail = $refDetail;
            }
        } else {
            $participant->reference_detail = $row['reference_detail'] ?? null;
        }
        $participant->type = 'student';
        $participant->added_by = Auth::id() ?? 1;

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
        $this->importedCount++;

        // Send automated WhatsApp confirmation only if enabled
        if ($this->sendWhatsapp) {
            try {
                $batchRegFee = floatval($batch->registration_fee ?? 0);
                if ($register_amount >= $batchRegFee) {
                    $msgContent = "Dear {$participant->first_name} {$participant->last_name},\n\n" .
                                  "Thank you for registering for {$batch->name} with E3!\n\n" .
                                  "We have successfully received your registration payment of ₹" . number_format($register_amount) . ".\n\n" .
                                  "Status: Registration Confirmed.\n\n" .
                                  "Warm regards,\nE3 Admin Team";
                } else {
                    $pendingRegFee = max(0, $batchRegFee - $register_amount);
                    $msgContent = "Dear {$participant->first_name} {$participant->last_name},\n\n" .
                                  "Thank you for registering for {$batch->name} with E3!\n\n" .
                                  "We have received your partial registration payment of ₹" . number_format($register_amount) . ".\n\n" .
                                  "Pending Registration Fee: ₹" . number_format($pendingRegFee) . ".\n\n" .
                                  "Kindly clear your pending registration fee to confirm your seat.\n\n" .
                                  "Warm regards,\nE3 Admin Team";
                }

                $mobile = preg_replace('/[^0-9]/', '', $participant->mobile);
                if (strlen($mobile) === 10) {
                    $mobile = '91' . $mobile;
                }

                $apiUrl   = env('WHATSAPP_API_URL', 'https://api.dotphi.com/wapp/v2/api/send');
                $apiToken = env('WHATSAPP_API_TOKEN', '');

                if (!empty($apiToken) && !empty($mobile)) {
                    \Illuminate\Support\Facades\Http::timeout(5)->connectTimeout(3)->get($apiUrl, [
                        'apikey' => $apiToken,
                        'mobile' => $mobile,
                        'msg'    => $msgContent,
                    ]);
                }
            } catch (\Exception $ex) {
                Log::error("Failed to send automatic WhatsApp message on Excel Import for " . $participant->mobile . ": " . $ex->getMessage());
            }
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
