<?php

namespace App\Http\Controllers\ApI;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreReservationRequest $request)
    {
        //
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
