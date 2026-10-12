<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\FreelancerProfile;
use App\Models\User;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Register a new account.
     *
     * Creates a new user account and initializes the
     * appropriate profile for the selected role.
     *
     * @group Authentication
     *
     * @bodyParam first_name string required The user's first name.
     * Example: Ahmed
     *
     * @bodyParam last_name string required The user's last name.
     * Example: Mohamed
     *
     * @bodyParam email string required A unique email address.
     * Example: ahmed@example.com
     *
     * @bodyParam role string required The account role.
     * Allowed values: client, freelancer.
     * Example: freelancer
     *
     * @bodyParam password string required The account password.
     * Example: StrongPassword123!
     *
     * @response 201 {
     *   "message": "Account created successfully.",
     *   "data": {
     *     "id": 1,
     *     "first_name": "Ahmed",
     *     "last_name": "Mohamed",
     *     "email": "ahmed@example.com",
     *     "role": "freelancer"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email field must be a valid email address."
     *     ],
     *     "role": [
     *       "The selected role is invalid."
     *     ]
     *   }
     * }
     */
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



        FreelancerProfile::create(
            [
                "user_id" => $user->id
            ]
        );

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

    /**
     * Log in to an account.
     *
     * Authenticate a user using their email and password.
     * Returns an authentication token upon successful login.
     *
     * @group Authentication
     *
     * @bodyParam email string required The registered email address. Example: ahmed@example.com
     * @bodyParam password string required The account password. Example: StrongPassword123!
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "data": {
     *     "user": {
     *       "id": 8,
     *       "first_name": "Ahmed",
     *       "last_name": "Mohamed",
     *       "email": "ahmed@example.com",
     *       "role": "freelancer"
     *     },
     *     "token": "1|example-token"
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Invalid credentials",
     *   "data": []
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": [
     *       "The email field is required."
     *     ],
     *     "password": [
     *       "The password field is required."
     *     ]
     *   }
     * }
     */

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
    /**
     * Make logout.
     *
     * @group Authentication
     * @authenticated
     */


    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        return ResponseService::Response(
            [],
            200,
            "Loged out successfully"
        );
    }
    /**
     * Get the authenticated user.
     *
     * @group Authentication
     * @authenticated
     */

    public function me(Request $request)
    {
        return ResponseService::Response(
            [
                "user" => $request->user()
            ],
            200,
            "current user"
        );
    }
}
