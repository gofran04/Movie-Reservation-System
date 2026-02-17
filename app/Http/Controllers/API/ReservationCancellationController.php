<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Reservation;
use App\Services\RefundService;

class ReservationCancellationController extends Controller
{
    public function cancel(Reservation $reservation, RefundService $refundService)
    {
        $this->authorize('cancel', $reservation);

        DB::transaction(function () use ($reservation, $refundService) 
        {
            if ($reservation->status === 'confirmed'){
                $payment = $reservation->payments()->where('status', 'succeeded')->first();
                $refundService->refund($payment);
            } else if ($reservation->status === 'pending') {
                $reservation->update([
                    'status' => 'cancelled',
                ]);
            }
            
            $reservation->seats()->detach();// free seats
        });

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
        ]);
    }
}
