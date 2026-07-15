<?php

namespace App\Collections;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class UsersCollection
{
    public static function collection(Request $request)
    {
        $defaultSort = '-created_at';

        $defaultSelect = [
            'id',
            'name',
            'email',
            'phone',
            'status',
            'role',
            'created_at',
            'updated_at',
        ];

        $allowedFilters = [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('phone'),
            AllowedFilter::exact('email'),
            'name',
            'status',
            'role',
            'created_at',
            'updated_at',
        ];

        $allowedSorts = [
            'updated_at',
            'created_at',
        ];

        $perPage = $request->limit  ? $request->limit : 50;

        return QueryBuilder::for(User::class)
            ->select($defaultSelect)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($allowedSorts)
            ->defaultSort($defaultSort)
            ->paginate($perPage);
    }

}
