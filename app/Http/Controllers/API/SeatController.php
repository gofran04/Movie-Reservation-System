<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SeatResource;
use App\Collections\SeatsCollection;
use App\Models\Seat;
use App\Models\Hall;

class SeatController extends Controller
{
    public function index(Request $request,Hall $hall)
    {
        $this->authorize('viewAny',Seat::class);

        return SeatResource::collection( SeatsCollection::collection($request)->where('hall_id', $hall->id))->collection;
    }

    public function show(Seat $seat)
    {
        $this->authorize('view', $seat);

        return new SeatResource($seat);
    }
}
