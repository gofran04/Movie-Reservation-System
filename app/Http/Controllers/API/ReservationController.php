<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\CreateReservationService;
use App\Collections\ReservationsCollection;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Reservation::class);

        return ReservationResource::collection(ReservationsCollection::collection(request()))->collection;
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
        //
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        //
    }

    public function destroy(Reservation $reservation)
    {
        //
    }
}
