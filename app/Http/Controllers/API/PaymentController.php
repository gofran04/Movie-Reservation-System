<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ProcessPaymentService;
use App\Models\Reservation;

class PaymentController extends Controller
{
    public function pay(Reservation $reservation, ProcessPaymentService $service)
    {
        $this->authorize('pay', $reservation);

        return response()->json([
            'url' => $service->createCheckoutSession($reservation)
        ]);
    }
}
