<?php

namespace App\Http\Controllers\API;

use App\Models\Movie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Movie\StoreMovieRequest;
use App\Http\Resources\MovieResource;

class MovieController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreMovieRequest $request)
    {
        $this->authorize('create', Movie::class);
        $movie = Movie::create($request->validated());

        return new MovieResource($movie);
    }

    public function show(Movie $movie)
    {
        //
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
