<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials = $request->only(['email', 'password']);

    // Senior Dev Tip: Use the auth() helper directly to avoid Facade overhead
    if (!$token = auth('api')->attempt($credentials)) {
        return response()->json([
            'status' => false,
            'message' => 'Authentication failed. Please check credentials.'
        ], 401);
    }

    return $this->respondWithToken($token);
}




    // public function login(Request $request)
    // {
    //     $this->validate($request, [
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     // --- DEBUG START ---
    // $user = \App\Models\User::where('email', $request->email)->first();
    // if (!$user) {
    //     return response()->json(['debug' => 'User not found in DB', 'checked_email' => $request->email], 404);
    // }
    // if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
    //     return response()->json(['debug' => 'Password hash check failed'], 401);
    // }

    //     $credentials = $request->only(['email', 'password']);

    //     // Corrected variable name with $
    //     // if (!$token = Auth::attempt($credentials)) 
    //     if (!$token = Auth::guard('api')->attempt($credentials))
            
            
    //         {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized: Invalid email or password.'
    //         ], 401);
    //     }

    //     return $this->respondWithToken($token);
    // }



    
    public function refresh()
    {
        try {
            return $this->respondWithToken(Auth::refresh());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token could not be refreshed.'
            ], 401);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60 
        ]);
    }
}