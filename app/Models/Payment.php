<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'stripe_checkout_session_id',
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
