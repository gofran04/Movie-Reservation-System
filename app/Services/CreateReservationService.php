<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use App\Models\Showtime;
use App\Models\ShowtimePrice;
use App\Models\Seat;
use App\Models\ReservationSeat;
use Illuminate\Validation\ValidationException;

class CreateReservationService
{
    public function createReservation(int $userId, int $showtimeId, array $seatIds): Reservation
    {
        return DB::transaction(function () use ($userId, $showtimeId, $seatIds) {

            // 1. Cleanup expired locks (scoped)
            $this->cleanupExpiredSeats($showtimeId);

            // 2. Ensure seats belong to the hall
            $this->assertSeatsBelongToShowtimeHall($showtimeId, $seatIds);

            // 3. Check availability (FOR UPDATE)
            $this->assertSeatsAreAvailable($showtimeId, $seatIds);

            //4. Calculate total price (optional, but usually needed)
            $totalPrice = $this->calculateTotalPrice($showtimeId, $seatIds);

            // 5. Create reservation
            $reservation = Reservation::create([
                'user_id'     => $userId,
                'showtime_id' => $showtimeId,
                'status'      => 'pending',
                'total_price' => $totalPrice,
                'expires_at'  => now()->addMinutes(10),
            ]);

            // 6. Attach seats
            $this->attachSeats($reservation, $seatIds, $showtimeId);

            return $reservation->load('seats');
        });
    }

    private function cleanupExpiredSeats(int $showtimeId): void
    {
        /*
            |--------------------------------------------------------------------------
            | 1. Free seats locked by expired pending reservations (scoped cleanup)
            |--------------------------------------------------------------------------
            |
            | We only delete seat locks that:
            | - belong to this showtime
            | - are pending
            | - are expired
            |
            | This prevents UNIQUE(showtime_id, seat_id) conflicts.
            |
        */
        DB::table('reservation_seats')
            ->join('reservations', 'reservations.id', '=', 'reservation_seats.reservation_id')
            ->where('reservation_seats.showtime_id', $showtimeId)
            ->where('reservations.status', 'pending')
            ->where('reservations.expires_at', '<', now())
            ->delete();
    }

    private function assertSeatsBelongToShowtimeHall(int $showtimeId, array $seatIds): void
    {
        /*
            |--------------------------------------------------------------------------
            | 2. Ensure seats belong to the hall of this showtime
            |--------------------------------------------------------------------------
            |
            | Backend must enforce domain integrity.
            |
        */
        // $showtime = Showtime::query()
        //     ->with('hall:id')
        //     ->findOrFail($showtimeId);
        $showtime = Showtime::findOrFail($showtimeId);

        $validSeatCount = Seat::whereIn('id', $seatIds)
            ->where('hall_id', $showtime->hall_id)
            ->count();

        if ($validSeatCount !== count($seatIds)) {
            throw ValidationException::withMessages([
                'seat_ids' => ['One or more seats do not belong to this showtime hall.'],
            ]);
        }
    }

    private function assertSeatsAreAvailable(int $showtimeId, array $seatIds): void
    {
        /*
            |--------------------------------------------------------------------------
            | 3. Ensure seats are available (race-condition safe)
            |--------------------------------------------------------------------------
            |
            | We lock conflicting rows using FOR UPDATE.
            | If ANY row exists, at least one seat is already taken.
            |
        */
        $conflict = DB::table('reservation_seats')
                        ->where('showtime_id', $showtimeId)
                        ->whereIn('seat_id', $seatIds)
                        ->select('seat_id')   // <-- IMPORTANT: select real rows
                        ->limit(1)            // <-- we only need ONE
                        ->lockForUpdate()     // <-- lock that row if it exists
                        ->first(); 
        if ($conflict) {
            throw ValidationException::withMessages([
                'seat_ids' => ['One or more seats are already reserved.'],
            ]); 
        }
    }

    private function calculateTotalPrice(int $showtimeId, array $seatIds): float
    {
        /*
            |--------------------------------------------------------------------------
            | 4. Calculate total price
            |--------------------------------------------------------------------------
        */
       // Fetch seats with their type
        $seats = Seat::whereIn('id', $seatIds)->get();

        // Fetch showtime prices
        $showtimePrices = ShowtimePrice::where('showtime_id', $showtimeId)->pluck('price', 'seat_type'); // ['regular' => 25, 'vip' => 45]

        // Calculate total price
        $totalPrice = 0;

        foreach ($seats as $seat) {
            $seatType = $seat->type;           // 'regular' or 'vip'
            $price = $showtimePrices[$seatType]; // get price from showtimePrice
            $totalPrice += $price;             // add to total
        }

        return $totalPrice;
    }

    private function attachSeats(Reservation $reservation, array $seatIds, int $showtimeId): void
    {
        /*
            |--------------------------------------------------------------------------
            | 6. Attach seats to reservation
            |--------------------------------------------------------------------------
            |
            | We create seat locks by attaching seats to the reservation.
            |
        */
        $rows = [];

            foreach ($seatIds as $seatId) {
                $rows[] = [
                    'reservation_id' => $reservation->id,
                    'seat_id'        => $seatId,
                    'showtime_id'    => $showtimeId,
                    'created_at'     => now(),
                ];
            }

        ReservationSeat::insert($rows);
    }
}