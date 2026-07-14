<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SeatResource;
use App\Collections\SeatsCollection;
use App\Http\Requests\Seat\UpdateSeatRequest;
use App\Models\Seat;
use App\Models\Hall;

class SeatController extends Controller
{
    public function index(Request $request,Hall $hall)
    {
        return SeatResource::collection( SeatsCollection::collection($request)->where('hall_id', $hall->id))->collection;
    }

    public function show(Seat $seat)
    {
        return new SeatResource($seat);
    }

    public function update(UpdateSeatRequest $request, Seat $seat)
    {
        $this->authorize('update', $seat);
        $seat->update($request->validated());

        return new SeatResource($seat->refresh());
    }
}
