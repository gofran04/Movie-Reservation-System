<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationSeat extends Model
{
    protected $fillable = [
        'reservation_id',
        'showtime_id',
        'seat_id',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
