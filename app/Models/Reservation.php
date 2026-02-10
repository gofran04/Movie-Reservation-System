<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'showtime_id',
        'status',
        'total_price',
        'expires_at',
    ];

    /*
        Whenever Laravel retrieves price from DB, it will:
        Format it as a decimal and Keep 2 digits after decimal
    */ 
    protected $casts = [
        'total_price' => 'decimal:2',
        'expires_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function reservationSeats()
    {
        return $this->hasMany(ReservationSeat::class);
    }

    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'reservation_seats')
            ->withPivot('showtime_id')
            ->withTimestamps();
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at > now();
    }

    public function canBePaid(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }
}
