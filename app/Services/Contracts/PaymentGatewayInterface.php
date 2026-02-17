<?php

namespace App\Services\Contracts;

use App\Models\Reservation;

interface PaymentGatewayInterface
{
    public function pay(Reservation $reservation);
}