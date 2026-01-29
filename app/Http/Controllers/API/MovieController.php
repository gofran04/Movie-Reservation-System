<?php

namespace App\Http\Controllers\API;

use App\Models\Movie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Movie\StoreMovieRequest;
use App\Http\Requests\Movie\UpdateMovieRequest;
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

        if ($request->hasFile('poster')) {
            $movie->addMediaFromRequest('poster')->toMediaCollection('poster');
        }

        return new MovieResource($movie);
    }

    public function show(Movie $movie)
    {
        $this->authorize('view', $movie);
        return new MovieResource($movie);
    }

    public function update(UpdateMovieRequest $request, Movie $movie)
    {
        $this->authorize('update', $movie);
        $movie->update($request->validated());

        return new MovieResource($movie->refresh());
    }

    public function destroy(Movie $movie)
    {
        $this->authorize('delete',$movie);
        $movie->delete();

        return response()->json([
            'message' => ('Movie successfully deleted')
        ]);   
    }
}
