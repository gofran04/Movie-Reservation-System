<?php

namespace App\Collections;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ReservationsCollection
{
    public static function collection(Request $request)
    {
        $defaultSort = '-created_at';

        $defaultSelect = [
            'id',
            'user_id',
            'showtime_id',
            'status',
            'expires_at',
            'created_at',
            'updated_at',
        ];

        $allowedFilters = [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('showtime_id'),
            AllowedFilter::exact('expires_at'),
            'status',
            'created_at',
            'updated_at',
        ];

        $allowedSorts = [
            'updated_at',
            'created_at',
        ];

        $perPage = $request->limit  ? $request->limit : 50;

        return QueryBuilder::for(Reservation::class)
            ->select($defaultSelect)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->defaultSort($defaultSort)
            ->paginate($perPage);
    }

}
