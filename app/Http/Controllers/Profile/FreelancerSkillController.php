<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateFreelancerSkillsRequest;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreelancerSkillController extends Controller
{


    /**
     * Get freelancer skills.
     *
     * Retrieves all skills associated with the authenticated freelancer's profile.
     * @group Freelancer Profile
     * @authenticated
     *
     * @response 200 {
     *   "message": "get freelancer skills",
     *   "data": {
     *     "profile_id": 5,
     *     "skills": [
     *       {
     *         "id": 1,
     *         "name": "PHP"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Laravel"
     *       },
     *       {
     *         "id": 3,
     *         "name": "MySQL"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "message": "get freelancer skills",
     *   "data": {
     *     "profile_id": 5,
     *     "skills": []
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated",
     *   "data": []
     * }
     */

    public function index()
    {
        $profile = Auth::user()->freelancerProfile;

        return ResponseService::Response([
            'profile_id' => $profile->id,

            'skills' => $profile->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ];
            })->values(),
        ], 200, 'get freelancer skills');
    }

    /**
     * Update freelancer skills.
     *
     * Associates the authenticated freelancer's profile with the selected skills.
     * @group Freelancer Profile
     * @authenticated
     *
     * @bodyParam skill_ids array required Array of existing skill IDs. Minimum 1 item. Example: [1, 2, 3]
     * @bodyParam skill_ids.* integer required An existing ID from the skills table. Example: 1
     *
     * @response 200 {
     *   "message": "Freelancer skills updated successfully",
     *   "data": {
     *     "skills": [
     *       {
     *         "id": 1,
     *         "name": "PHP"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Laravel"
     *       },
     *       {
     *         "id": 3,
     *         "name": "MySQL"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated",
     *   "data": []
     * }
     *
     * @response 422 {
     *   "message": "The selected skill_ids.0 is invalid.",
     *   "data": []
     * }
     */
    public function update(UpdateFreelancerSkillsRequest $request)
    {

        $validated = $request->validated();

        $profile = Auth::user()->freelancerProfile;

        $profile->skills()->sync($validated['skill_ids']);

        return ResponseService::Response([
            'skills' => $profile->skills()->get(['skills.id', 'skills.name'])
                ->map(fn($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ])
                ->values(),
        ], 200, 'Freelancer skills updated successfully');
    }
}
