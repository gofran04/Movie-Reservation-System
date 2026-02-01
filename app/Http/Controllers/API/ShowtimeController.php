<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Showtime\StoreShowtimeRequest;
use App\Http\Requests\Showtime\UpdateShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Models\Showtime;
use App\Models\Movie;
use Carbon\Carbon;

class ShowtimeController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreShowtimeRequest $request)
    {
        $this->authorize('create', Showtime::class);
        $inputs = $request->validated();

        $movie = Movie::findOrFail($inputs['movie_id']);
        $inputs['end_time'] = Carbon::parse($inputs['start_time'])->addMinutes($movie->duration_minutes)->format('Y-m-d H:i:s');

        $showtime = Showtime::create($inputs);

        return new ShowtimeResource($showtime);
    }

    public function show(Showtime $showtime)
    {
        //
    }

    public function update(UpdateShowtimeRequest $request, Showtime $showtime)
    {
        //
    }

    public function destroy(Showtime $showtime)
    {
        //
    }
}
