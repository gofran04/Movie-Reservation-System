<?php
namespace App\Services;

use App\Models\Showtime;
use Illuminate\Support\Facades\DB;

class GetAvailableSeatsService
{
    public function forShowtime(Showtime $showtime): array
    {
        //1 Get ALL seats in the hall of this showtime
        $seats = $showtime->hall->seats()->get();

        // 2 Get IDs of seats that are NOT available (confirmed OR pending & not expired)
        $unavailableSeatIds = DB::table('reservation_seats')
            ->join('reservations', 'reservations.id', '=', 'reservation_seats.reservation_id')
            ->where('reservation_seats.showtime_id', $showtime->id)
            ->where(function ($query) {
                $query
                    ->where('reservations.status', 'confirmed')
                    ->orWhere(function ($q) {
                        $q->where('reservations.status', 'pending')
                          ->where('reservations.expires_at', '>', now());
                    });
            })
            ->pluck('reservation_seats.seat_id')
            ->toArray();

        //3 Build final response using FOREACH (NO COLLECTION MAGIC)
        $result = [];

        foreach ($seats as $seat) {
            $result[] = [
                'id'     => $seat->id,
                'row'    => $seat->row_number,
                'column' => $seat->column_number,
                'type'   => $seat->type,
                'status' => in_array($seat->id, $unavailableSeatIds)
                    ? 'reserved'
                    : 'available',
            ];
        }

        return $result;
    }
}
