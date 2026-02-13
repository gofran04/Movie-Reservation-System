<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class CleanupExpiredReservationsJob implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {

            $expiredReservations = Reservation::where('status', 'pending')
                ->where('expires_at', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($expiredReservations as $reservation) {
                // mark expired
                $reservation->update([
                    'status' => 'cancelled',
                ]);

                // free seats
                $reservation->seats()->detach();
            }
        });
    }
}
