<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);
        return UserResource::collection(User::all());
    }

    public function store(Request $request)
    {
        //
    }

    public function show(User $user)
    {
        $this->authorize('view', User::class);
        return new UserResource($user);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
