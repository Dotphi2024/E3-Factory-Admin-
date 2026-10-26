<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssignmentService;
use App\Services\FcmNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoReleaseSessionAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:auto-release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically start/release session assignments at 6:00 PM on the session date';

    protected $fcmNotificationService;

    public function __construct(FcmNotificationService $fcmNotificationService)
    {
        parent::__construct();
        $this->fcmNotificationService = $fcmNotificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Checking session assignments for auto-release at " . $now->format('Y-m-d H:i:s') . " (Policy: 6:00 PM on session day)...");

        $totalReleased = AssignmentService::autoReleaseAllDue($this->fcmNotificationService);

        $this->info("Auto-release completed. Total assignment(s) released: {$totalReleased}");
        Log::info("assignments:auto-release executed. Total assignment(s) released: {$totalReleased}");

        return 0;
    }
}
