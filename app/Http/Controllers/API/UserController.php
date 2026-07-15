<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Collections\UsersCollection;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
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
        $this->authorize('view', $user);
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
    
        $user->update($request->validated());

        return (new UserResource($user->refresh()));    
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json([
            'message' => ('User successfully deleted')
        ]);    
    }

    public function suspend(User $user)
    {
        $this->authorize('suspend', $user);

        $user->update(['status' => 'suspended']);

        return response()->json(['message' => 'User suspended']);
    }

    public function activate(User $user)
    {
        $this->authorize('activate', $user);

        $user->update(['status' => 'active']);

        return response()->json(['message' => 'User activated']);
    }
}
