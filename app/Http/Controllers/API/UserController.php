<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Collections\UsersCollection;
use App\Http\Requests\User\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        return UserResource::collection(UsersCollection::collection($request))->collection;
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        $inputs = $request->validated();
        $inputs['password'] = Hash::make($inputs['password']);
        $inputs['role'] = "admin";

        $user = User::create($inputs);
        $user->assignRole($inputs['role']);

        return new UserResource($user);
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
