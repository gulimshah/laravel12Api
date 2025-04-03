<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail; // Create a custom mail class

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:5']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $validatedData = $validator->validated();
        $user = User::create($validatedData);
        $token = $user->createToken($request->username);
        return response()->json([
            'success' => true,
            'message' => 'Resource created successfully!',
            'user' => $user,
            'token' => $token->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
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

    public function forgetPass(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate a 6-digit code instead of a long hashed token
        $verificationCode = rand(100000, 999999);

        // Store code in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $verificationCode, 'created_at' => now()]
        );

        try {
            // Send email synchronously (without queuing)
            Mail::to($request->email)->send(new ResetPasswordMail($verificationCode, $request->email));
            return response()->json(['message' => 'Reset link sent to your email'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unable to send reset link', 'error' => $e->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        dd($request->all());
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|digits:6', // Ensure it's a 6-digit code
            'password' => 'required|min:6|confirmed',
        ]);

        // Check if the token is valid
        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetEntry) {
            return response()->json(['message' => 'Invalid token'], 400);
        }
        // Reset password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        // Delete the token after use
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return response()->json(['message' => 'Password reset successful'], 200);
    }
}
