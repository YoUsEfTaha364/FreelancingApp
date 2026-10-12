<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateFreelancerLanguagesRequest;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreelancerLanguageController extends Controller
{

    /**
     * Get freelancer languages.
     *
     * Retrieves all languages associated with the authenticated freelancer,
     * including each language's name and proficiency level.
     * @group Freelancer Profile
     * @authenticated
     *
     * @response 200 {
     *   "message": "get freelancer languages",
     *   "data": {
     *     "profile_id": 5,
     *     "languages": [
     *       {
     *         "id": 1,
     *         "name": "English",
     *         "proficiency": "native"
     *       },
     *       {
     *         "id": 2,
     *         "name": "German",
     *         "proficiency": "intermediate"
     *       }
     *     ]
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
            "profile_id" => $profile->id,
            'languages' => $profile->languages->map(function ($language) {
                return [
                    'id' => $language->id,
                    'name' => $language->name,
                    'proficiency' => $language->pivot->proficiency,
                ];
            })->values(),
        ], 200, 'get freelancer languages');
    }


    /**
     * Update freelancer languages.
     *
     * Adds or updates the authenticated freelancer's languages and proficiency levels.
     * @group Freelancer Profile
     * @authenticated
     *
     * @bodyParam languages array required List of freelancer languages. Minimum 1 item.
     * @bodyParam languages.*.language_id integer required Existing language ID from the languages table. Example: 1
     * @bodyParam languages.*.proficiency string required Proficiency level. Allowed values: beginner, intermediate, fluent, native. Example: fluent
     *
     * @response 200 {
     *   "message": "Freelancer languages updated successfully",
     *   "data": {
     *     "languages": [
     *       {
     *         "language_id": 1,
     *         "name": "Egypt",
     *         "proficiency": "native"
     *       },
     *       {
     *         "language_id": 2,
     *         "name": "USA",
     *         "proficiency": "fluent"
     *       },
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
     *   "message": "The selected languages.*.proficiency is invalid.",
     *   "data": []
     * }
     */
    public function update(UpdateFreelancerLanguagesRequest $request)
    {
        $profile = Auth::user()->freelancerProfile;

        $validated = $request->validated();

        $languages = [];

        foreach ($validated['languages'] as $language) {
            $languages[$language['language_id']] = [
                'proficiency' => $language['proficiency'],
            ];
        }

        $profile->languages()->sync($languages);

        return ResponseService::Response([
            'languages' => $profile->languages->map(function ($language) {
                return [
                    'id' => $language->id,
                    'name' => $language->name,
                    'proficiency' => $language->pivot->proficiency,
                ];
            })->values(),
        ], 200, 'get freelancer languages');
    }
}
