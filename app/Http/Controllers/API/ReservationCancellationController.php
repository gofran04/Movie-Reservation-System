<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;

class ReservationCancellationController extends Controller
{
    public function cancel(Reservation $reservation)
    {
        $this->authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation) {
            // free seats
            $reservation->seats()->detach();

            // update reservation status
            $reservation->update([
                'status' => 'cancelled',
            ]);
        });

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
        ]);
    }
}
