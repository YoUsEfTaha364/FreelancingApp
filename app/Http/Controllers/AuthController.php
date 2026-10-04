<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token,
        ];

        return ResponseService::Response(
            $data,
            201,
            'User registered successfully'
        );
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return ResponseService::Response(
                [],
                401,
                'Invalid credentials'
            );
        }
        

        $token = $user->createToken('auth-token')->plainTextToken;

        

        return ResponseService::Response(
            [
                'user' => $user,
                'token' => $token,
            ],
            200,
            'Login successful'
        );
    }

    public function logout(Request $request)
    {
        
        $request->user()->tokens()->delete();

        return ResponseService::Response([],200,
            "Loged out successfully"
        );
    }

    public function me(Request $request)
    {
            return ResponseService::Response([
                "user"=>$request->user()
            ],200,
            "current user"
        ); 
    }
}
