<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHallRequest;
use App\Http\Requests\UpdateHallRequest;
use App\Http\Resources\HallResource;
use App\Services\CreateSeatService;
use App\Collections\HallsCollection;
use App\Models\Hall;

class HallController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny',Hall::class);
        return HallResource::collection(HallsCollection::collection($request))->collection;
    }

    public function store(StoreHallRequest $request)
    {
        $this->authorize('create', Hall::class);
        $hall = Hall::create($request->validated());
        CreateSeatService::createSeatsForHall($hall);
        
        return new HallResource($hall);
    }

    public function show(Hall $hall)
    {
        //
    }

    public function update(UpdateHallRequest $request, Hall $hall)
    {
        //
    }

    public function destroy(Hall $hall)
    {
        //
    }
}
