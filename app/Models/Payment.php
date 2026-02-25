<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'gateway_reference',// was 'stripe_checkout_session_id' before, renamed to a more generic name to accommodate different gateways
        'refund_reference',// External refund identifier from payment gateway (used for webhook mapping, idempotency, and multi-refund support)
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

     public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
