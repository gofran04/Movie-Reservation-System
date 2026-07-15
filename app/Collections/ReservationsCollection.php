<?php

namespace App\Collections;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ReservationsCollection
{
    public static function collection(Request $request,User $user)
    {
        $defaultSort = '-created_at';

        $defaultSelect = [
            'id',
            'user_id',
            'showtime_id',
            'status',
            'total_price',
            'expires_at',
            'created_at',
            'updated_at',
        ];

        $allowedFilters = [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('user_id'),
            AllowedFilter::exact('showtime_id'),
            AllowedFilter::exact('expires_at'),
            'status',
            'total_price',
            'created_at',
            'updated_at',
        ];

        $allowedSorts = [
            'updated_at',
            'created_at',
        ];

        $perPage = $request->limit  ? $request->limit : 50;

        $query = QueryBuilder::for(Reservation::class)
            ->select($defaultSelect)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->defaultSort($defaultSort);

        // If NOT admins/general manager → limit to own reservations
        if (! $user->can('view-all-reservations')) {
            $query->where('user_id', $user->id);
        }

        return $query->paginate($perPage);
    }

}
