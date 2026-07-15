<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = 
    [
        'hall_id',
        'row_number',
        'column_number',
        'type',
        'status',
    ];

    public function hall()
    {
        return $this->belongsTo(Hall::class);
    }
}
