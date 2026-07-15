<?php

namespace App\Services\Gateways;

use App\Models\Reservation;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class FakeFailPaymentGateway implements PaymentGatewayInterface
{
    public function pay(Reservation $reservation)
    {
        return [
            'reference_id' => 'fake_fail_' . Str::uuid(),
            'redirectUrl'  => 'https://fake.test/fail'
        ];   
    }
}