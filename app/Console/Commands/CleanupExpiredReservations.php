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
        CleanupExpiredReservationsJob::dispatch();

        $this->info('Cleanup job dispatched successfully.');

        return self::SUCCESS;
    }
}
