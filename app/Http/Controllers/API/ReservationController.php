<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\CreateReservationService;
use App\Collections\ReservationsCollection;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Reservation::class);

        return ReservationResource::collection(ReservationsCollection::collection(request(), auth()->user()))->collection;
    }

    public function store(StoreReservationRequest $request)
    {
        $this->authorize('create', Reservation::class);

        $userId = Auth::id();
        $seatIds = $request->input('seat_ids');
        $showtimeId = $request->input('showtime_id');
        $reservation = app(CreateReservationService::class)->createReservation($userId, $showtimeId, $seatIds);
       
        return new ReservationResource($reservation);
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return new ReservationResource($reservation);
    }
}
