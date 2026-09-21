<?php
namespace App\Traits;
use Carbon\Carbon;
trait FormatDates{
    public function formatDate($value, $format = 'd-m-Y')
    {
        // Check if the date is not null or empty, then try formatting it
        if (!empty($value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->format($format);
            } catch (\Exception $e) {
                // Handle any parsing errors and return the original value or null
                return $value;
            }
        }

        // Return null if the date is null or empty
        return null;
    }

    // Optional: Add a method for time formatting
    public function formatTime($value, $format = 'h:i A')
    {
        // Check if the time is not null or empty, then try formatting it
        if (!empty($value)) {
            try {
                return Carbon::createFromFormat('H:i:s', $value)->format($format);
            } catch (\Exception $e) {
                // Handle parsing errors
                return $value;
            }
        }

        return null;
    }
}
