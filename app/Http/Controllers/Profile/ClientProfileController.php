<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateClientProfileRequest;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class ClientProfileController extends Controller
{

    /**
     * Update client profile
     *
     * Update the authenticated client's profile information.
     *
     * @group Client Profile
     * @authenticated
     *
     * @bodyParam company_name string nullable The company name. Example: Tech Solutions
     * @bodyParam phone string nullable Egyptian mobile number. Example: 01012345678
     * @bodyParam country string nullable The country. Example: Egypt
     * @bodyParam city string nullable The city. Example: Cairo
     *
     * @response 200 {
     *   "message": "Client profile updated successfully",
     *   "data": {
     *     "profile": {
     *       "id": 1,
     *       "user_id": 5,
     *       "company_name": "Tech Solutions",
     *       "phone": "01012345678",
     *       "country": "Egypt",
     *       "city": "Cairo"
     *     }
     *   }
     * }
     *
     * @response 401 {"message": "You are unauthorized", "data": []}
     *
     * @response 404 {"message": "Client profile not found", "data": []}
     *
     * @response 422 {"message": "validation errors", "data": {"phone": ["The phone format is invalid."]}}
     */
    public function update(UpdateClientProfileRequest $request)
    {
        $profile = $request->user()->clientProfile;
        if (!$profile) {
            return ResponseService::Response(
                [],
                404,
                'Client profile not found'
            );
        }

        $profile->update($request->validated());

        return ResponseService::Response(
            ['profile' => $profile->fresh()],
            200,
            'Client profile updated successfully'
        );
    }

    /**
     * Get client profile
     *
     * Retrieve the authenticated client's profile information.
     *
     * @group Client Profile
     * @authenticated
     *
     * @response 200 {
     *   "message": "Client profile retrieved successfully",
     *   "data": {
     *     "profile": {
     *       "id": 1,
     *       "user_id": 5,
     *       "company_name": "Tech Solutions",
     *       "phone": "01012345678",
     *       "country": "Egypt",
     *       "city": "Cairo"
     *     }
     *   }
     * }
     *
     * @response 401 {"message": "Unauthenticated.", "data": []}
     * @response 404 {"message": "Client profile not found", "data": []}
     */

    public function show()
    {
        $profile = request()->user()->clientProfile;

        if (!$profile) {
            return ResponseService::Response(
                [],
                404,
                'Client profile not found'
            );
        }

        return ResponseService::Response(
            ['profile' => $profile],
            200,
            'Client profile retrieved successfully'
        );
    }
}
