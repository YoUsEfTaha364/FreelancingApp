<?php

namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\ProposalUpdateRequest;
use App\Http\Requests\Freelancer\StorePropsalRequest;
use App\Http\Resources\Freelancer\ProposalResource;
use App\Models\Proposal;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ProposalController extends Controller
{

    /**
     * Submit a proposal
     *
     * Submit a proposal for a job as the authenticated freelancer.
     *
     * @group Freelancer - Proposals
     * @authenticated
     *
     * @bodyParam job_id integer required The ID of the job. Example: 12
     * @bodyParam bid_amount number required The requested project price. Must be positive. Example: 500.00
     * @bodyParam estimated_duration integer nullable Estimated project duration. Example: 14
     * @bodyParam cover_letter string required The freelancer's cover letter. Example: I have experience building similar applications.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "proposal send successfully",
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "job_id": 12,
     *       "bid_amount": "500.00",
     *       "estimated_duration": 14,
     *       "cover_letter": "I have experience building similar applications.",
     *       "status": "pending"
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "bid_amount": ["The bid amount field is required."]
     *   }
     * }
     */
    public function store(StorePropsalRequest $request)
    {
        //job_id , bid_amount , cover_letter

        $validated = $request->validated();

        $proposal = Proposal::create([
            "freelancer_id" => Auth::user()->id,
            "job_id" => $validated["job_id"],
            "bid_amount" => $validated["bid_amount"],
            "estimated_duration" => $validated["estimated_duration"] ?? null,
            "cover_letter" => $validated["cover_letter"],
        ]);




        return ResponseService::Response(["proposal" => new ProposalResource($proposal->fresh())], 201, "proposal send successfully");
    }

    /**
     * List my proposals
     *
     * Retrieve all proposals submitted by the authenticated freelancer.
     *
     * @group Freelancer - Proposals
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Get freelancer proposals",
     *   "data": {
     *     "proposals": [
     *       {
     *         "id": 5,
     *         "job-title": "E-commerce Website",
     *         "job-description": "Build an online store.",
     *         "bid_amount": "500.00",
     *         "cover_letter": "I can build your website.",
     *         "status": "pending"
     *       }
     *     ]
     *   }
     * }
     */

    public function index()
    {
        $proposals = Auth::user()->proposals;

        if (is_null($proposals) || $proposals->count() == 0) {
            return ResponseService::Response([], 200, "Freelancer has no proposals");
        }

        return ResponseService::Response(["proposals" => ProposalResource::collection($proposals)], 200, "Get freelancer proposals");
    }

    /**
     * Get my proposal
     *
     * Retrieve details of a proposal owned by the authenticated freelancer.
     *
     * @group Freelancer - Proposals
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Get a proposal",
     *   "data": {
     *     "proposals": {
     *       "id": 5,
     *       "job-title": "E-commerce Website",
     *       "job-description": "Build an online store.",
     *       "bid_amount": "500.00",
     *       "cover_letter": "I can build your website.",
     *       "status": "pending"
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "This action is unauthorized."
     * }
     *
     * @response 404 {
     *   "message": "Not Found"
     * }
     */

    public function show(Proposal $proposal)
    {
        $this->authorize("show", $proposal);


        return ResponseService::Response(["proposals" => new ProposalResource($proposal)], 200, "Get a proposal");
    }

    /**
     * Update my proposal
     *
     * Update an existing proposal owned by the authenticated freelancer.
     * Only proposals with pending status can be updated.
     *
     * @group Freelancer - Proposals
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @bodyParam bid_amount number optional The updated requested price. Example: 600.00
     * @bodyParam estimated_duration integer nullable The updated estimated duration. Example: 20
     * @bodyParam cover_letter string optional The updated cover letter. Example: I can deliver additional features.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Proposal updated successfully.",
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "bid_amount": "600.00",
     *       "estimated_duration": 20,
     *       "cover_letter": "I can deliver additional features.",
     *       "status": "pending"
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "This action is unauthorized."
     * }
     *
     * @response 422 {
     *   "message": "Only pending proposals can be updated."
     * }
     */

    public function update(
        ProposalUpdateRequest $request,
        Proposal $proposal
    ) {

        $this->authorize('update', $proposal);

        if ($proposal->status !== 'pending') {
            return ResponseService::Response(
                [],
                422,
                'Only pending proposals can be updated.'
            );
        }

        if ($proposal->negotiation_status === 'pending') {
            return ResponseService::Response(
                [],
                422,
                'Cannot update a proposal while a negotiation is pending.'
            );
        }

        $proposal->update($request->validated());

        return ResponseService::Response(
            ['proposal' => new ProposalResource($proposal->refresh())],
            200,
            'Proposal updated successfully.'
        );
    }

    /**
     * Withdraw my proposal
     *
     * Withdraw a previously submitted proposal.
     * Only proposals with pending status can be withdrawn.
     *
     * @group Freelancer - Proposals
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Proposal withdrawn successfully.",
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "status": "withdrawn"
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "This action is unauthorized."
     * }
     *
     * @response 422 {
     *   "message": "Only pending proposals can be withdrawn."
     * }
     */

    public function withdraw(Proposal $proposal)
    {
        $this->authorize('withdraw', $proposal);

        if ($proposal->status !== 'pending') {
            return ResponseService::Response(
                [],
                422,
                'Only pending proposals can be withdrawn.'
            );
        }

        if ($proposal->negotiation_status === 'pending') {
            return ResponseService::Response(
                [],
                422,
                'Cannot withdraw a proposal while a negotiation is pending.'
            );
        }

        $proposal->update([
            'status' => 'withdrawn',
        ]);

        return ResponseService::Response(
            ['proposal' => new ProposalResource($proposal->refresh())],
            200,
            'Proposal withdrawn successfully.'
        );
    }
}
