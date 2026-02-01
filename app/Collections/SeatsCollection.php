<?php

namespace App\Collections;

use App\Models\Seat;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SeatsCollection
{
    public static function collection(Request $request)
    {
        $defaultSort = '-created_at';

        $defaultSelect = [
            'id',
            'hall_id',
            'row_number',
            'column_number',
            'type',
            'status',
            'created_at',
            'updated_at',
        ];

        $allowedFilters = [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('row_number'),
            AllowedFilter::exact('column_number'),
            'type',
            'status',
            'created_at',
            'updated_at',
        ];

        $allowedSorts = [
            'updated_at',
            'created_at',
        ];

        $perPage = $request->limit  ? $request->limit : 50;

        return QueryBuilder::for(Seat::class)
            ->select($defaultSelect)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->defaultSort($defaultSort)
            ->paginate($perPage);
    }

}
