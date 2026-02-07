<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowtimePrice extends Model
{
    protected $fillable = [
        'showtime_id',
        'seat_type',
        'price',
    ];

    /*
        Whenever Laravel retrieves price from DB, it will:
        Format it as a decimal and Keep 2 digits after decimal
    */ 
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function showtime()
    {
        $this->belongsTo(Showtime::class);
    }
}
