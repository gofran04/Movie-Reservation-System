<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cinema extends Model
{
    protected $fillable = [
        'name',
        'location',
    ];

    public function halls()
    {
        return $this->hasMany(Hall::class);
    }
}
