<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CleanupExpiredReservationsJob;

class CleanupExpiredReservations extends Command
{
    protected $signature = 'reservations:cleanup-expired';
    protected $description = 'Cleanup expired reservations and free up seats';

    public function handle(): int
    {
        // CleanupExpiredReservationsJob::dispatch();
        // For deplyment,use dispatchSync to run the job immediately in the current process, cuz Render does not support free queue workers,so we will use dispatchSync to run the job immediately in the current process.
        CleanupExpiredReservationsJob::dispatchSync();

        $this->info('Cleanup job dispatched successfully.');

        return self::SUCCESS;
    }
}
