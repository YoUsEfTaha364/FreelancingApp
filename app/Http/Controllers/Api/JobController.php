<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\ListJobsRequest;
use App\Http\Resources\Client\JobResource;
use App\Models\Job;
use App\Services\ResponseService;

class JobController extends Controller
{
    /**
     * Browse open jobs.
     *
     * Returns a paginated list of all open job posts available for discovery.
     * Supports filtering by category, experience level, budget range, and required skills.
     * Draft and closed jobs are never returned.
     *
     * @group Job Discovery
     * @authenticated
     *
     * @queryParam category_id integer optional Filter by category ID. Example: 1
     * @queryParam experience_level string optional Filter by experience level. Allowed: entry, intermediate, expert. Example: intermediate
     * @queryParam budget_min number optional Show jobs whose budget_max is at least this value. Example: 500
     * @queryParam budget_max number optional Show jobs whose budget_min is at most this value. Example: 3000
     * @queryParam skill_ids integer[] optional Filter jobs requiring all given skill IDs. Example: [1, 2]
     * @queryParam per_page integer optional Results per page (1–50). Default: 15. Example: 10
     *
     * @response 200 {
     *   "message": "Open jobs retrieved successfully",
     *   "data": {
     *     "jobs": {
     *       "current_page": 1,
     *       "data": [
     *         {
     *           "id": 1,
     *           "category_id": 1,
     *           "title": "Build a Laravel REST API",
     *           "description": "We need a skilled Laravel developer...",
     *           "budget_min": "500.00",
     *           "budget_max": "2000.00",
     *           "experience_level": "intermediate",
     *           "deadline": "2027-03-01",
     *           "status": "open",
     *           "created_at": "2026-10-13T10:00:00.000000Z",
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
    public function index(ListJobsRequest $request)
    {
        $validated = $request->validated();

        $query = Job::where('status', 'opened')
            ->with(['category', 'skills']);

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['experience_level'])) {
            $query->where('experience_level', $validated['experience_level']);
        }

        // budget_min filter: show jobs whose budget_max >= requested budget_min
        if (isset($validated['budget_min'])) {
            $query->where(function ($q) use ($validated) {
                $q->whereNull('budget_max')
                  ->orWhere('budget_max', '>=', $validated['budget_min']);
            });
        }

        // budget_max filter: show jobs whose budget_min <= requested budget_max
        if (isset($validated['budget_max'])) {
            $query->where(function ($q) use ($validated) {
                $q->whereNull('budget_min')
                  ->orWhere('budget_min', '<=', $validated['budget_max']);
            });
        }

        if (! empty($validated['skill_ids'])) {
            foreach ($validated['skill_ids'] as $skillId) {
                $query->whereHas('skills', fn ($q) => $q->where('skills.id', $skillId));
            }
        }

        $perPage = $validated['per_page'] ?? 15;

        $jobs = $query->latest()->paginate($perPage);

        return ResponseService::Response(
            ['jobs' => JobResource::collection($jobs)],
            200,
            'Opened jobs retrieved successfully'
        );
    }

    /**
     * Show a single open job.
     *
     * Retrieves a specific job post. Only open jobs are publicly visible.
     * Closed or non-existent jobs return 404.
     *
     * @group Job Discovery
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 1
     *
     * @response 200 {
     *   "message": "Job retrieved successfully",
     *   "data": {
     *     "job": {
     *       "id": 1,
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
     * @response 404 {"message": "Resource not found", "data": []}
     * @response 401 {"message": "You are unauthenticated , must register or login", "data": []}
     */
    public function show(Job $job)
    {
        if ($job->status !== 'opened') {
            return ResponseService::Response([], 404, 'Resource not found');
        }

        return ResponseService::Response(
            ['job' => new JobResource($job->load(["category","skills"]))],
            200,
            'Job retrieved successfully'
        );
    }
}
