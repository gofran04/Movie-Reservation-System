<?php

namespace App\Services;

use App\Models\Reservation;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Models\Payment;

class ProcessPaymentService
{
    protected PaymentGatewayInterface $gateWay;

    public function __construct(PaymentGatewayInterface $gateWay) {
        $this->gateWay = $gateWay;
    }

    public function payment(Reservation $reservation)
    {
        $result = $this->gateWay->pay($reservation);

        Payment::create([
            'reservation_id'             => $reservation->id,
            'gateway_reference'          => $result['reference_id'],
            'amount'                     => $reservation->total_price,
            'currency'                   => 'usd',
            'status'                     => 'pending',
        ]);

        return $result['redirectUrl'];    
    }
}
