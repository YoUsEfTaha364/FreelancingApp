<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreelancerExperienceController extends Controller
{

     /**
      * Get all freelancer experiences.
      *
      * Retrieves all experiences belonging to the authenticated freelancer.
      * @group Freelancer Profile
      * @authenticated
      *
      * @response 200 {
      *   "message": "get freelancer experiences",
      *   "data": {
      *     "experiences": [
      *       {
      *         "id": 1,
      *         "freelancer_profile_id": 1,
      *         "job_title": "Backend Developer",
      *         "company_name": "Tech Company",
      *         "description": "Developed RESTful APIs using Laravel.",
      *         "start_date": "2024-01-01",
      *         "end_date": "2025-01-01",
      *         "is_current": false,
      *         "created_at": "2026-10-01T10:00:00.000000Z",
      *         "updated_at": "2026-10-01T10:00:00.000000Z"
      *       }
      *     ]
      *   }
      * }
      *
      * @response 200 {
      *   "message": "get freelancer experiences",
      *   "data": {
      *     "experiences": []
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
          $experiences = Auth::user()->freelancerProfile->experiences->toArray();


          return ResponseService::Response(
               ["experiences" => $experiences ?? []],
               200,
               "get freelancer experiences"
          );
     }


     /**
      * Add freelancer work experience.
      *
      * Create a new work experience record for the authenticated freelancer.
      *
      * @group Freelancer Profile
      * @authenticated
      *
      * @bodyParam job_title string required The job title. Maximum 255 characters. Example: Backend Laravel Developer
      * @bodyParam company_name string required The company name. Maximum 255 characters. Example: Tech Solutions
      * @bodyParam description string optional A description of the job responsibilities. Maximum 2000 characters. Example: Developed RESTful APIs using Laravel and MySQL.
      * @bodyParam start_date date required The employment start date. Example: 2023-01-01
      * @bodyParam is_current boolean required Whether the freelancer currently works at this company. Example: false
      * @bodyParam end_date date optional The employment end date. Required when is_current is false and prohibited when is_current is true. Must be on or after start_date. Example: 2025-06-30
      *
      * @response 201 {
      *   "message": "Experience added successfully.",
      *   "data": {
      *     "experience": {
      *       "id": 1,
      *       "job_title": "Backend Laravel Developer",
      *       "company_name": "Tech Solutions",
      *       "description": "Developed RESTful APIs using Laravel and MySQL.",
      *       "start_date": "2023-01-01",
      *       "is_current": false,
      *       "end_date": "2025-06-30"
      *     }
      *   }
      * }
      *
      * @response 422 {
      *   "message": "The given data was invalid.",
      *   "errors": {
      *     "job_title": [
      *       "The job title field is required."
      *     ],
      *     "end_date": [
      *       "The end date field is required when is current is false."
      *     ]
      *   }
      * }
      *
      * @response 401 {
      *   "message": "Unauthenticated."
      * }
      */

     public function store(CreateExperienceRequest $request)
     {
          $validated = $request->validated();


          $experience = Experience::create([
               'freelancer_profile_id' => $request->user()->freelancerProfile->id,
               'job_title' => $validated['job_title'],
               'company_name' => $validated['company_name'],
               'description' => $validated['description'] ?? null,
               'start_date' => $validated['start_date'],
               'end_date' => $validated['end_date'] ?? null,
               'is_current' => $validated['is_current'],
          ]);


          return ResponseService::Response(["experience" => $experience], 201, "freelancer experience created successfuly");
     }


     /**
      * Update freelancer work experience.
      *
      * Updates an existing experience record. Only the supplied fields are updated.
      *
      * @group Freelancer Profile
      * @authenticated
      *
      * @urlParam experience integer required The ID of the experience to update. Example: 1
      *
      * @bodyParam job_title string optional The job title. Maximum 255 characters. Example: Senior Laravel Developer
      * @bodyParam company_name string optional The company name. Maximum 255 characters. Example: ABC Technologies
      * @bodyParam description string nullable optional A description of job responsibilities. Maximum 2000 characters. Example: Developed RESTful APIs using Laravel and MySQL.
      * @bodyParam start_date date optional The employment start date. Example: 2023-01-01
      * @bodyParam is_current boolean optional Whether the freelancer currently works at this company. Example: false
      * @bodyParam end_date date nullable optional The employment end date. Must be on or after start_date when both dates are provided. Example: 2026-09-30
      *
      * @response 200 {
      *   "message": "Experience updated successfully.",
      *   "data": {
      *     "experience": {
      *       "id": 1,
      *       "job_title": "Senior Laravel Developer",
      *       "company_name": "ABC Technologies",
      *       "description": "Built RESTful APIs using Laravel and MySQL.",
      *       "start_date": "2023-01-01",
      *       "is_current": false,
      *       "end_date": "2026-09-30"
      *     }
      *   }
      * }
      *
      * @response 401 {
      *   "message": "Unauthenticated."
      * }
      *
      * @response 403 {
      *   "message": "This action is unauthorized."
      * }
      *
      * @response 404 {
      * "message": "Resource not found",
      * "data": []
      *}
      *
      * @response 422 {
      *   "message": "The given data was invalid.",
      *   "errors": {
      *     "job_title": [
      *       "The job title must not be greater than 255 characters."
      *     ]
      *   }
      * }
      */

     public function update(UpdateExperienceRequest $request, Experience $experience)
     {
          $validated = $request->validated();

          $experience->update($validated);


          return ResponseService::Response(["experience" => $experience], 200, "freelancer experience updated successfuly");
     }



     /**
      * Get freelancer experience.
      *
      * Retrieves a specific experience by its ID.
      *
      * @group Freelancer Profile
      * @authenticated
      *
      * @urlParam experience integer required The ID of the experience. Example: 1
      *
      * @response 200 {
      *   "message": "get freelancer experience",
      *   "data": {
      *     "experience": {
      *       "id": 1,
      *       "freelancer_profile_id": 1,
      *       "job_title": "Backend Developer",
      *       "company_name": "Tech Company",
      *       "description": "Developed RESTful APIs using Laravel.",
      *       "start_date": "2024-01-01",
      *       "end_date": "2025-01-01",
      *       "is_current": false,
      *       "created_at": "2026-10-01T10:00:00.000000Z",
      *       "updated_at": "2026-10-01T10:00:00.000000Z"
      *     }
      *   }
      * }
      *
      * @response 404 {
      *   "message": "Resource not found",
      *   "data": []
      * }
      */
     public function show(Experience $experience)
     {
          return ResponseService::Response(
               ["experience" => $experience],
               200,
               "get freelancer experience"
          );
     }

     /**
      * Delete freelancer experience.
      *
      * Deletes a specific experience by its ID.
      * @group Freelancer Profile
      * @authenticated
      *
      * @urlParam experience integer required The ID of the experience. Example: 1
      *
      * @response 200 {
      *   "message": "experience deleted successfully",
      *   "data": []
      * }
      *
      * @response 404 {
      *   "message": "Resource not found",
      *   "data": []
      * }
      */
     public function destroy(Experience $experience)
     {
          $experience->delete();

          return ResponseService::Response(
               [],
               200,
               "experience deleted successfully"
          );
     }
}
