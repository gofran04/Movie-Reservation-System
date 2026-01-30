<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHallRequest;
use App\Http\Requests\UpdateHallRequest;
use App\Http\Resources\HallResource;
use App\Models\Hall;

class HallController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreHallRequest $request)
    {
        $hall = Hall::create($request->validated());
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
