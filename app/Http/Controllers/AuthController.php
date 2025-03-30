<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = User::create($fields);

        $token = $user->createToken($request->username);
        return response()->json([
            'message' => 'Resource created successfully!',
            'user' => $user,
            'token' => $token->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }
        $token = $user->createToken($user->username);
        return response()->json([
            'message' => 'Logged in successfully!',
            'user' => $user,
            'token' => $token->plainTextToken
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'You are logout successfully!',
        ], 200);
    }
}
