<?php

namespace App\Services;

use App\Models\Reservation;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class ProcessPaymentService
{
    protected PaymentGatewayInterface $gateWay;

    public function __construct(PaymentGatewayInterface $gateWay) {
        $this->gateWay = $gateWay;
    }

    public function payment(Reservation $reservation)
    {
        return DB::transaction(function () use ($reservation) {
            $payment = Payment::create([
                'reservation_id'             => $reservation->id,
                'amount'                     => $reservation->total_price,
                'currency'                   => 'usd',
                'status'                     => 'pending',
            ]);  
        
            try {
                $result = $this->gateWay->pay($reservation);
                $payment->update([
                    'gateway_reference' => $result['reference_id'],
                ]);     
                
                return $result['redirectUrl'];    
            } catch (PaymentFailedException $e) {
                throw $e;
            }
        });
    }
}
