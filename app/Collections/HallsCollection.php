<?php

namespace App\Collections;

use App\Models\Hall;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class HallsCollection
{
    public static function collection(Request $request)
    {
        $defaultSort = '-created_at';

        $defaultSelect = [
            'id',
            'name',
            'cinema_id',
            'total_rows',
            'total_columns',
            'status',
            'created_at',
            'updated_at',
        ];

        $allowedFilters = [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('total_rows'),
            AllowedFilter::exact('total_columns'),
            'name',
            'cinema_id',
            'status',
            'created_at',
            'updated_at',
        ];

        $allowedSorts = [
            'updated_at',
            'created_at',
        ];

        $perPage = $request->limit  ? $request->limit : 50;

        return QueryBuilder::for(Hall::class)
            ->select($defaultSelect)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->defaultSort($defaultSort)
            ->paginate($perPage);
    }

}
