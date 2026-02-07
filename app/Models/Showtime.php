<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Showtime extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'movie_id',
        'hall_id',
        'start_time',
        'end_time',
    ];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function showtimePrices()
    {
        return $this->hasMany(ShowtimePrice::class);
    }
}
