<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Services\GetAvailableSeatsService;

class ShowtimeSeatController extends Controller
{
    public function availableSeats(Showtime $showtime)
    {
        return response()->json([
            'showtime_id' => $showtime->id,
            'seats'       => app(GetAvailableSeatsService::class)->forShowtime($showtime),
        ]);
    }
}
