<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Services\ProcessRefundService;

class ReservationCancellationController extends Controller
{
    public function cancel(Reservation $reservation, ProcessRefundService $processRefundService)
    {
        $this->authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation, $processRefundService) 
        {
            if ($reservation->status === 'confirmed'){
                $payment = $reservation->payments()->where('status', 'succeeded')->first();
                $processRefundService->refund($payment);
            } else if ($reservation->status === 'pending') {
                $reservation->update([
                    'status' => 'cancelled',
                ]);

                $reservation->seats()->detach();// free seats
            }
        });

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
        ]);
    }
}
