<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $fillable = [
        'name',
        'cinema_id',
        'total_rows',
        'total_columns',
        'status',
    ];

    public function cinema()
    {
        return $this->belongsTo(Cinema::class);
    }
}
