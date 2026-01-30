<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Get email and password from the Postman request
        $credentials = $request->only(['email', 'password']);

        // 2. Attempt to verify credentials and generate a token
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 3. If successful, return the token
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // TTL is usually set to 60 minutes in your config
            'expires_in' => Auth::factory()->getTTL() * 60 
        ]);
    }

//     public function login(Request $request)
// {
//     $credentials = $request->only(['email', 'password']);

//     // DEBUG 1: Check if the user even exists in the DB
//     $user = \App\Models\User::where('email', $request->email)->first();
//     if (!$user) {
//         return response()->json(['debug' => 'User not found in database for this email'], 404);
//     }

//     // DEBUG 2: Check if the password matches using manual check
//     if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
//         return response()->json([
//             'debug' => 'Password hash mismatch',
//             'sent_password' => $request->password,
//             'db_hash' => $user->password
//         ], 401);
//     }

//     // Attempt JWT login
//     if (!$token = \Illuminate\Support\Facades\Auth::attempt($credentials)) {
//         return response()->json(['debug' => 'Credentials match, but JWT failed to create token. Check JWT_SECRET or config/auth.php'], 500);
//     }

//     return $this->respondWithToken($token);
// }
}