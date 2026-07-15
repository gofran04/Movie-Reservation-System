<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ProcessPaymentService;
use App\Exceptions\PaymentFailedException;
use App\Models\Reservation;

class PaymentController extends Controller
{
    public function pay(Reservation $reservation, ProcessPaymentService $service)
    {
        $this->authorize('pay', $reservation);

        try {
            $redirectUrl = $service->payment($reservation);

            return response()->json([
                'redirect_url' => $redirectUrl
            ]);
        } catch (PaymentFailedException $e) {
            return response()->json([
                'message' => 'Payment gateway unavailable'
            ], 503); 
        }
    }
}
