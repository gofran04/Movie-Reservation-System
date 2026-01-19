<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\Auth\RegisterionRequest;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticationController extends Controller
{
    public function register(RegisterionRequest $request)
    {
        $inputs = $request->validated();
        $inputs['password'] = Hash::make($inputs['password']);

        $user = User::create($inputs);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'User successfully registered',
            'user'         => $user,
            'access_token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->safe()->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token]);
    }

    public function logOut(Request $request)
    {
        $request->user()->currentAccessToken()->delete();// logout user from the current device only
        return response()->json(['message' => 'Logged out successfully']);
    }
}
