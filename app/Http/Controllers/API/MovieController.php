<?php

namespace App\Http\Controllers\API;

use App\Models\Movie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Movie\StoreMovieRequest;
use App\Http\Resources\MovieResource;
Use App\Collections\MoviesCollection;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny',Movie::class);
        return MovieResource::collection(MoviesCollection::collection($request))->collection;
    }

    public function store(StoreMovieRequest $request)
    {
        $this->authorize('create', Movie::class);
        $movie = Movie::create($request->validated());

        return new MovieResource($movie);
    }

    public function show(Movie $movie)
    {
        $this->authorize('view', $movie);
        return new MovieResource($movie);
    }

    public function update(Request $request, Movie $movie)
    {
        //
    }

    public function destroy(Movie $movie)
    {
        //
    }
}
