<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cinema\UpdateCinemaRequest;
use App\Http\Resources\CinemaResource;
use App\Models\Cinema;

class CinemaController extends Controller
{

    public function show(Cinema $cinema)
    {
        $this->authorize('view', $cinema);

        return new CinemaResource($cinema);
    }

    public function update(UpdateCinemaRequest $request, Cinema $cinema)
    {
        $this->authorize('update', $cinema);
        $cinema->update($request->validated());

        return new CinemaResource($cinema);
    }
}
