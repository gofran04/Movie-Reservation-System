<?php

namespace App\Services\Gateways;

use App\Models\Reservation;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class FakeSuccessPaymentGateway implements PaymentGatewayInterface
{
    public function pay(Reservation $reservation): array
    {
        return [
                'reference_id' => 'fake_session_' . Str::uuid(),
                'redirectUrl'  => 'https://fake.test/checkout'
            ];
    }
}