<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Showtime\StoreShowtimeRequest;
use App\Http\Requests\Showtime\UpdateShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Collections\ShowtimesCollection;
use App\Models\Showtime;
use App\Models\Movie;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Showtime::class);

        return ShowtimeResource::collection(ShowtimesCollection::collection($request))->collection;
    }

    public function store(StoreShowtimeRequest $request)
    {
        $this->authorize('create', Showtime::class);
        $inputs = $request->validated();

        $movie = Movie::findOrFail($inputs['movie_id']);
        $inputs['end_time'] = Carbon::parse($inputs['start_time'])->addMinutes($movie->duration_minutes)->format('Y-m-d H:i:s');

        $prices = $inputs['prices'];
        unset($inputs['prices']);

        $showtime = DB::transaction(function () use ($inputs, $prices) {
            $showtime = Showtime::create($inputs);

            foreach ($prices as $type => $price) {
                $showtime->prices()->create([
                    'seat_type' => $type,
                    'price'     => $price,
                ]);
            }
            return $showtime;
        });

        return new ShowtimeResource($showtime->load('showtimePrices'));
    }

    public function show(Showtime $showtime)
    {
        $this->authorize('view', $showtime);

        return new ShowtimeResource($showtime);
    }

    public function update(UpdateShowtimeRequest $request, Showtime $showtime)
    {
        $this->authorize('update', $showtime);
        $inputs = $request->validated();

        $movie = Movie::findOrFail($inputs['movie_id']);
        $inputs['end_time'] = Carbon::parse($inputs['start_time'])->addMinutes($movie->duration_minutes)->format('Y-m-d H:i:s');

        $prices = $inputs['prices'] ?? null;
        unset($inputs['prices']);

        DB::transaction(function () use ($showtime, $inputs, $prices) {
            $showtime->update($inputs);

            if ($prices) {
                foreach ($prices as $type => $price) {
                    $showtime->prices()->updateOrCreate(
                        ['seat_type' => $type],
                        ['price' => $price]
                    );
                }
            }
        });
        
        return new ShowtimeResource($showtime->refresh()->load('showtimePrices'));
    }

    public function destroy(Showtime $showtime)
    {
        $this->authorize('delete', $showtime);
        $showtime->delete();    

        return response()->json(['message' => 'Showtime deleted successfully']);
    }
}
