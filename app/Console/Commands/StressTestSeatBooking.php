<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CreateReservationService;
use Illuminate\Support\Facades\DB;

class StressTestSeatBooking extends Command
{
    protected $signature = 'test:stress-booking';
    protected $description = 'Stress test concurrent seat bookings'; // The command test concurrent seat booking, by simulate mulitple users attemting to reserve same seats at the same time. Only one user will reserver, other will fail.

    public function handle()
    {
        $showtimeId = 5; // Change to a valid showtime ID
        $seatIds = [3]; // Example: multiple seats per reservation

        $bar = $this->output->createProgressBar(20);
        $bar->start();

        $success = 0;
        $fail = 0;

        for ($i = 1; $i <= 20; $i++) { // Simulate 20 users
            try {
                DB::transaction(function () use ($i, $showtimeId, $seatIds) {
                    app(CreateReservationService::class)
                        ->createReservation($i, $showtimeId, $seatIds);
                });
                $success++;
            } catch (\Throwable $e) {
                $fail++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info("\nSuccess: $success");
        $this->info("Failed: $fail");
    }
}
