<?php

namespace App\Http\Controllers\Profile;
// freelancer ->   6|dvsJXwRThOMGwRdVTwL8L3oVLhBvVCC9ZJrcrbHBd2067a4d
// client ->       7|oQpUeRPlldaWKcmNvJqTC1WZz0pHTmdciS4pxLBd3cc00bb5
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateFreelancerProfileRequest;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreelancerProfileController extends Controller
{
    /**
     * Update freelancer profile data.
     *
     * @group Freelancer Profile
     * @authenticated
     * @response 200 {
     *   "message": "Updated user profile data",
     *   "data": {
     *     "profile": {
     *       "id": 5,
     *       "user_id": 8,
     *       "gender": "male",
     *       "title": "Full Stack Developer",
     *       "biography": "Passionate developer specializing in building scalable web applications using Laravel, PHP, MySQL, and RESTful APIs. I enjoy solving complex problems and writing clean, maintainable code.",
     *       "phone": "+201012345678",
     *       "country": "Egypt",
     *       "city": "Cairo",
     *       "hourly_rate": null,
     *       "created_at": "2026-10-10T17:44:22.000000Z",
     *       "updated_at": "2026-10-10T18:28:49.000000Z"
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Validation errors",
     *   "data": {
     *     "gender": [
     *       "The gender field is required."
     *     ],
     *     "title": [
     *       "The title field is required."
     *     ]
     *   }
     * }
     *
     * @response 401 {
     *   "message": "You are unauthorized",
     *   "data": []
     * }
     */
    public function update(UpdateFreelancerProfileRequest $request)
    {
        $validated = $request->validated();
        $profile = Auth::user()->freelancerProfile;

        $profile->update([
            'gender' => $request->gender,
            'title' => $request->title,
            'biography' => $request->biography,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
        ]);


        return ResponseService::Response(
            ["profile" => $profile],
            200,
            'updated user profile data'
        );
    }
}
