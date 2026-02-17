<?php

namespace App\Services;

use App\Models\Reservation;
use App\Services\Contracts\PaymentGatewayInterface;

class ProcessPaymentService
{
    protected PaymentGatewayInterface $gateWay;

    public function __construct(PaymentGatewayInterface $gateWay) {
        $this->gateWay = $gateWay;
    }

    public function payment(Reservation $reservation)
    {
        return $this->gateWay->pay($reservation);
    }
}
