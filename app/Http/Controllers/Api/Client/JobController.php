<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\ChangeStatusJobRequest;
use App\Http\Requests\Job\ListClientJobsRequest;
use App\Http\Requests\Job\StoreJobRequest;
use App\Http\Requests\Job\UpdateJobRequest;
use App\Http\Resources\Client\JobResource;
use App\Models\Job;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
 
{
    /**
     * List the authenticated client's jobs.
     *
     * Returns a paginated list of all jobs posted by the authenticated client.
     * Supports optional filtering by status and category.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @queryParam status string optional Filter by status. Allowed: open, closed. Example: open
     * @queryParam category_id integer optional Filter by category ID. Example: 1
     * @queryParam per_page integer optional Results per page (1–50). Default: 15. Example: 10
     *
     * @response 200 {
     *   "message": "Client jobs retrieved successfully",
     *   "data": {
     *     "jobs": {
     *       "current_page": 1,
     *       "data": [
     *         {
     *           "id": 1,
     *           "user_id": 3,
     *           "category_id": 1,
     *           "title": "Build a Laravel REST API",
     *           "description": "We need a skilled Laravel developer...",
     *           "budget_min": "500.00",
     *           "budget_max": "2000.00",
     *           "experience_level": "intermediate",
     *           "deadline": "2027-03-01",
     *           "status": "open",
     *           "created_at": "2026-10-13T10:00:00.000000Z",
     *           "updated_at": "2026-10-13T10:00:00.000000Z",
     *           "category": {"id": 1, "name": "Web Development", "slug": "web-development"},
     *           "skills": [{"id": 1, "name": "PHP"}, {"id": 2, "name": "Laravel"}]
     *         }
     *       ],
     *       "per_page": 15,
     *       "total": 1
     *     }
     *   }
     * }
     *
     * @response 401 {"message": "You are unauthenticated , must register or login", "data": []}
     */
    public function index(ListClientJobsRequest $request)
    {
        $validated = $request->validated();

        $query = Auth::user()
            ->jobs()
            ->with(['category', 'skills']);

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        $perPage = $validated['per_page'] ?? 15;

        $jobs = $query->latest()->paginate($perPage);

        $pagination = [
            'current_page'  => $jobs->currentPage(),
            'per_page'      => $jobs->perPage(),
            'total'         => $jobs->total(),
            'last_page'     => $jobs->lastPage(),
            'next_page_url' => $jobs->nextPageUrl(),
            'prev_page_url' => $jobs->previousPageUrl(),
        ];

        return ResponseService::Response(
            [
                'jobs' => JobResource::collection($jobs),
                'pagination' => $pagination,
            ],
            200,
            'Client jobs retrieved successfully'
        );
    }

    /**
     * Create a job post.
     *
     * Creates a new job post owned by the authenticated client.
     * The user_id and initial status are set server-side.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @bodyParam title string required Job title. Max 255 characters. Example: Build a Laravel REST API
     * @bodyParam description string required Detailed job description. Max 5000 characters. Example: We need a skilled Laravel developer to build a REST API.
     * @bodyParam category_id integer required Existing category ID. Example: 1
     * @bodyParam budget_min number nullable Minimum budget in USD. Example: 500
     * @bodyParam budget_max number nullable Maximum budget in USD (>= budget_min). Example: 2000
     * @bodyParam experience_level string required Required experience level. Allowed: entry, intermediate, expert. Example: intermediate
     * @bodyParam deadline date nullable Optional project deadline (must be in the future). Example: 2027-03-01
     * @bodyParam skill_ids integer[] nullable Optional array of existing skill IDs. Example: [1, 2, 3]
     *
     * @response 201 {
     *   "message": "Job created successfully",
     *   "data": {
     *     "job": {
     *       "id": 1,
     *       "user_id": 3,
     *       "category_id": 1,
     *       "title": "Build a Laravel REST API",
     *       "description": "We need a skilled Laravel developer...",
     *       "budget_min": "500.00",
     *       "budget_max": "2000.00",
     *       "experience_level": "intermediate",
     *       "deadline": "2027-03-01",
     *       "status": "open",
     *       "category": {"id": 1, "name": "Web Development", "slug": "web-development"},
     *       "skills": [{"id": 1, "name": "PHP"}, {"id": 2, "name": "Laravel"}]
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "message": "Validation errors",
     *   "data": {
     *     "title": ["The title field is required."],
     *     "budget_max": ["The budget max must be greater than or equal to budget min."]
     *   }
     * }
     *
     * @response 401 {"message": "You are unauthenticated , must register or login", "data": []}
     */
    public function store(StoreJobRequest $request)
    {
        $validated = $request->validated();

        // dd($validated);

        $job = DB::transaction(function () use ($validated) {
            $job = Auth::user()->jobs()->create([
                'category_id'      => $validated['category_id'],
                'title'            => $validated['title'],
                'description'      => $validated['description'],
                'budget_min'       => $validated['budget_min'] ?? null,
                'budget_max'       => $validated['budget_max'] ?? null,
                'experience_level' => $validated['experience_level'],
                'deadline'         => $validated['deadline'] ?? null,
                'status'           => 'open',
            ]);

            if (! empty($validated['skill_ids'])) {
                $job->skills()->sync($validated['skill_ids']);
            }

            return $job;
        });

        return ResponseService::Response(
            ['job' =>new JobResource($job->load(["category","skills"]))],
            201,
            'Job created successfully'
        );
    }

    /**
     * Show one of the client's jobs.
     *
     * Retrieves a specific job post belonging to the authenticated client.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 1
     *
     * @response 200 {
     *   "message": "Job retrieved successfully",
     *   "data": {
     *     "job": {
     *       "id": 1,
     *       "user_id": 3,
     *       "category_id": 1,
     *       "title": "Build a Laravel REST API",
     *       "description": "We need a skilled Laravel developer...",
     *       "budget_min": "500.00",
     *       "budget_max": "2000.00",
     *       "experience_level": "intermediate",
     *       "deadline": "2027-03-01",
     *       "status": "open",
     *       "category": {"id": 1, "name": "Web Development", "slug": "web-development"},
     *       "skills": [{"id": 1, "name": "PHP"}]
     *     }
     *   }
     * }
     *
     * @response 404 {"message": "Resource not found", "data": []}
     * @response 403 {"message": "This action is unauthorized.", "data": []}
     * @response 401 {"message": "You are unauthenticated , must register or login", "data": []}
     */
    public function show(Job $job)
    {
        
        $this->authorize('show', $job);

        if ($job->user_id !== Auth::id()) {
            return ResponseService::Response([], 403, 'This action is unauthorized.');
        }

        return ResponseService::Response(
           ['job' => new JobResource($job->load(["category","skills"]))],
            200,
            'Job retrieved successfully'
        );
    }

    /**
     * Update a job post.
     *
     * Partially updates an existing job. Only the supplied fields are changed.
     * When skill_ids is omitted, skills remain unchanged.
     * When an empty array is supplied for skill_ids, all skills are removed.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 1
     *
     * @bodyParam title string optional Job title. Max 255 characters. Example: Build a React Dashboard
     * @bodyParam description string optional Updated job description. Example: New description.
     * @bodyParam category_id integer optional Existing category ID. Example: 2
     * @bodyParam budget_min number nullable Minimum budget in USD. Example: 300
     * @bodyParam budget_max number nullable Maximum budget in USD. Example: 1500
     * @bodyParam experience_level string optional Allowed: entry, intermediate, expert. Example: expert
     * @bodyParam deadline date nullable Project deadline (future date). Example: 2027-06-01
     * @bodyParam skill_ids integer[] nullable Omit to leave unchanged. Empty array removes all skills. Example: [1, 4]
     *
     * @response 200 {
     *   "message": "Job updated successfully",
     *   "data": {
     *     "job": {
     *       "id": 1,
     *       "title": "Build a React Dashboard",
     *       "status": "open",
     *       "category": {"id": 2, "name": "UI/UX Design", "slug": "ui-ux-design"},
     *       "skills": [{"id": 1, "name": "PHP"}]
     *     }
     *   }
     * }
     *
     * @response 403 {"message": "This action is unauthorized.", "data": []}
     * @response 404 {"message": "Resource not found", "data": []}
     * @response 422 {"message": "Validation errors", "data": {"budget_max": ["The budget max must be greater than or equal to budget min."]}}
     */
    public function update(UpdateJobRequest $request, Job $job)
    {
       $this->authorize('update', $job);

        $validated = $request->validated();

        DB::transaction(function () use ($job, $validated, $request) {

            $jobData = collect($validated)->except('skill_ids')->toArray();

            if (! empty($jobData)) {
                $job->update($jobData);
            }

            if ($request->has('skill_ids')) {
                $job->skills()->sync($validated['skill_ids'] ?? []);
            }
        });

        return ResponseService::Response(
          ['job' => new JobResource($job->fresh()->load(["category","skills"]))],
            200,
            'Job updated successfully'
        );
    }

    /**
     * change a job post status.
     *
     * Changes the job status to 'closed'. Only the owner can close a job.
     * A job that is already closed cannot be closed again.
     * Changes the job status to 'closed'. Only the owner can close a job.
     * A job that is already closed cannot be closed again.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @urlParam job integer required The ID of the job to close. Example: 1
     *
     * @response 200 {
     *   "message": "Job closed successfully",
     *   "data": {
     *     "job": {
     *       "id": 1,
     *       "status": "closed"
     *     }
     *   }
     * }
     *
     * @response 422 {"message": "Job is already closed.", "data": []}
     * @response 403 {"message": "This action is unauthorized.", "data": []}
     * @response 404 {"message": "Resource not found", "data": []}
     */
    public function changeStatus(ChangeStatusJobRequest $request, Job $job)
    {
         $this->authorize('changeStatus', $job);

         $validated=$request->validated();


        if ($job->status === 'closed' && $validated["status"]=="closed") {
            return ResponseService::Response([], 422, 'Job is already closed.');
        }
        if ($job->status === 'opened' && $validated["status"]=="opened") {
            return ResponseService::Response([], 422, 'Job is already opened.');
        }

        $job->update(['status' => $validated["status"]]);

        return ResponseService::Response(
            ['job' => new JobResource($job->fresh())],
            200,
            'Job  '.$job->status.' successfully'
        );
    }

    /**
     * Delete a job post.
     *
     * Permanently deletes a job post owned by the authenticated client.
     * Associated skill links are removed via cascade.
     *
     * @group Client Jobs
     * @authenticated
     *
     * @urlParam job integer required The ID of the job to delete. Example: 1
     *
     * @response 200 {"message": "Job deleted successfully", "data": []}
     * @response 403 {"message": "This action is unauthorized.", "data": []}
     * @response 404 {"message": "Resource not found", "data": []}
     */
    public function destroy(Job $job)
    {
       $this->authorize("delete",$job);

        $job->delete();

        return ResponseService::Response([], 200, 'Job deleted successfully');
    }
}
