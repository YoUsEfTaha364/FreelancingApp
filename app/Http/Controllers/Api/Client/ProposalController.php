<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNegotiationRequest;
use App\Http\Resources\ClientProposalResource;
use App\Http\Resources\NegotiationResource;
use App\Models\Job;
use App\Models\Proposal;
use App\Models\Negotiation;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProposalController extends Controller
{

    /**
     * Get job proposals
     *
     * Retrieve paginated proposals submitted by freelancers
     * for a specific job owned by the authenticated client.
     *
     * @group Client - Proposals
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 12
     *
     * @response 200 {
     *   "success": true,
     *   "message": "get job proposals",
     *   "data": {
     *     "proposals": [
     *       {
     *         "id": 5,
     *         "freelancer": {
     *           "id": 8,
     *           "name": "Ahmed Ali"
     *         },
     *         "bid_amount": "500.00",
     *         "cover_letter": "I can build your website.",
     *         "status": "pending",
     *         "negotiations": []
     *       }
     *     ],
     *     "pagination": {
     *       "current_page": 1,
     *       "per_page": 15,
     *       "total": 1,
     *       "last_page": 1,
     *       "next_page_url": null,
     *       "prev_page_url": null
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this job."
     * }
     *
     * @response 404 {
     *   "message": "Not Found"
     * }
     */
    public function index(Job $job)
    {
        $this->authorizeJob($job);

        $proposals = Proposal::query()
            ->where('job_id', $job->id)
            ->with(['freelancer', 'negotiations'])
            ->latest()
            ->paginate(15);

        $pagination = [
            'current_page'  => $proposals->currentPage(),
            'per_page'      => $proposals->perPage(),
            'total'         => $proposals->total(),
            'last_page'     => $proposals->lastPage(),
            'next_page_url' => $proposals->nextPageUrl(),
            'prev_page_url' => $proposals->previousPageUrl(),
        ];

        return ResponseService::Response([
            'proposals' => ClientProposalResource::collection($proposals),
            'pagination' => $pagination,
        ], 200, "get job proposals");
    }

    /**
     * Get job proposal
     *
     * Retrieve a specific proposal, including freelancer information
     * and its negotiation history.
     *
     * @group Client - Proposals
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 12
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @response 200 {
     *   "success": true,
     *   "message": "get job proposal",
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "bid_amount": "500.00",
     *       "agreed_amount": null,
     *       "status": "pending",
     *       "negotiations": []
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this job."
     * }
     *
     * @response 404 {
     *   "message": "Proposal not found for this job."
     * }
     */
    public function show(Job $job, Proposal $proposal)
    {
        $this->authorizeJob($job);
        $this->ensureProposalBelongsToJob($job, $proposal);

        $proposal->load(['freelancer', 'negotiations']);

        return ResponseService::Response([
            'proposal' => new ClientProposalResource($proposal),

        ], 200, "get job proposal");
    }

    /**
     * Accept a proposal
     *
     * Allows the client who owns the job to accept a freelancer's original bid
     * without creating a negotiation.
     *
     * The proposal must be pending, must not have a pending negotiation,
     * and no other proposal for the same job may already be accepted.
     *
     * @group Client - Proposals
     *
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 1
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @response 200 {
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "job_id": 1,
     *       "bid_amount": "500.00",
     *       "agreed_amount": "500.00",
     *       "status": "accepted"
     *     }
     *   },
     *   "message": "Proposal accepted successfully."
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this job."
     * }
     *
     * @response 404 {
     *   "message": "Proposal not found for this job."
     * }
     *
     * @response 422 {
     *   "message": "Another proposal has already been accepted for this job."
     * }
     */
    public function accept(Job $job, Proposal $proposal)
    {
        $this->authorizeJob($job);
        $this->ensureProposalBelongsToJob($job, $proposal);

        $result = DB::transaction(function () use ($job, $proposal) {
            $lockedJob = Job::query()
                ->whereKey($job->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedProposal = $lockedJob->proposals()
                ->whereKey($proposal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProposal->status !== 'pending') {
                return [
                    'error' => 'Only pending proposals can be accepted.',
                    'status' => 422,
                ];
            }

            if ($lockedProposal->negotiations()
                ->where('status', 'pending')
                ->exists()
            ) {
                return [
                    'error' => 'Resolve the pending negotiation before accepting this proposal.',
                    'status' => 422,
                ];
            }

            $anotherAcceptedProposal = Proposal::query()
                ->where('job_id', $lockedJob->id)
                ->where('status', 'accepted')
                ->exists();

            if ($anotherAcceptedProposal) {
                return [
                    'error' => 'Another proposal has already been accepted for this job.',
                    'status' => 422,
                ];
            }

            $lockedProposal->update([
                'agreed_amount' => $lockedProposal->bid_amount,
                'status' => 'accepted',
            ]);

            return [
                'proposal' => $lockedProposal->fresh(),
            ];
        });

        if (isset($result['error'])) {
            return ResponseService::Response(
                [],
                $result['status'],
                $result['error']
            );
        }

        return ResponseService::Response([
            'proposal' => new ClientProposalResource($result['proposal']),
        ], 200, 'Proposal accepted successfully.');
    }


    /**
     * Reject a proposal
     *
     * Allows the client who owns the job to reject a pending freelancer proposal.
     *
     * A proposal cannot be rejected while a negotiation is pending.
     *
     * @group Client - Proposals
     *
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 1
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @response 200 {
     *   "data": {
     *     "proposal": {
     *       "id": 5,
     *       "job_id": 1,
     *       "bid_amount": "500.00",
     *       "agreed_amount": null,
     *       "status": "rejected"
     *     }
     *   },
     *   "message": "Proposal rejected successfully."
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this job."
     * }
     *
     * @response 404 {
     *   "message": "Proposal not found for this job."
     * }
     *
     * @response 422 {
     *   "message": "Only pending proposals can be rejected."
     * }
     */


    public function reject(Job $job, Proposal $proposal)
    {
        $this->authorizeJob($job);
        $this->ensureProposalBelongsToJob($job, $proposal);

        $result = DB::transaction(function () use ($job, $proposal) {
            $lockedJob = Job::query()
                ->whereKey($job->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedProposal = $lockedJob->proposals()
                ->whereKey($proposal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProposal->status !== 'pending') {
                return [
                    'error' => 'Only pending proposals can be rejected.',
                    'status' => 422,
                ];
            }

            if ($lockedProposal->negotiations()
                ->where('status', 'pending')
                ->exists()
            ) {
                return [
                    'error' => 'Resolve the pending negotiation before rejecting this proposal.',
                    'status' => 422,
                ];
            }

            $lockedProposal->update([
                'status' => 'rejected',
            ]);

            return [
                'proposal' => $lockedProposal->fresh(),
            ];
        });

        if (isset($result['error'])) {
            return ResponseService::Response(
                [],
                $result['status'],
                $result['error']
            );
        }

        return ResponseService::Response([
            'proposal' => new ClientProposalResource($result['proposal']),
        ], 200, 'Proposal rejected successfully.');
    }


    /**
     * Create negotiation
     *
     * Create a counteroffer for a freelancer's proposal.
     * Any previous pending counteroffer for the same proposal
     * is marked as superseded.
     *
     * @group Client - Proposals
     * @authenticated
     *
     * @urlParam job integer required The ID of the job. Example: 12
     * @urlParam proposal integer required The ID of the proposal. Example: 5
     *
     * @bodyParam proposed_amount number required The counteroffer amount. Must be greater than zero and have at most two decimal places. Example: 400.00
     * @bodyParam message string nullable A message explaining the counteroffer. Maximum 2000 characters. Example: My budget for this project is 400.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nehotation Created successfully",
     *   "data": {
     *     "negotiation": {
     *       "id": 8,
     *       "proposal_id": 5,
     *       "proposed_by": 3,
     *       "proposed_amount": "400.00",
     *       "message": "My budget for this project is 400.",
     *       "status": "pending",
     *       "responded_at": null
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this job."
     * }
     *
     * @response 404 {
     *   "message": "Proposal not found for this job."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "proposed_amount": [
     *       "The proposed amount field is required."
     *     ]
     *   }
     * }
     */
    public function storeNegotiation(StoreNegotiationRequest $request, Job $job, Proposal $proposal)
    {
        $this->authorizeJob($job);
        $this->ensureProposalBelongsToJob($job, $proposal);

        if ($proposal->status !== 'pending') {
            throw ValidationException::withMessages([
                'proposal' => 'Negotiations are only allowed for pending proposals.',
            ]);
        }

        $validated = $request->validated();

        $negotiation = DB::transaction(function () use (
            $job,
            $proposal,
            $validated,
            $request
        ) {
            // Replace any previous pending counteroffer.
            Negotiation::query()
                ->where('proposal_id', $proposal->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'superseded',
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            return Negotiation::create([
                'proposal_id' => $proposal->id,
                'proposed_by' => $request->user()->id,
                'proposed_amount' => $validated['proposed_amount'],
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);
        });

        return ResponseService::Response(["negotiation" => new NegotiationResource($negotiation)], 201, "Nehotation Created successfully");
    }

    /**
     * Ensure the authenticated client owns the job.
     */
    private function authorizeJob(Job $job): void
    {
        abort_unless(
            $job->user_id === Auth::user()->id,
            403,
            'You are not authorized to access this job.'
        );
    }

    /**
     * Ensure the proposal belongs to the specified job.
     */
    private function ensureProposalBelongsToJob(
        Job $job,
        Proposal $proposal
    ): void {
        abort_unless(
            $proposal->job_id === $job->id,
            404,
            'Proposal not found for this job.'
        );
    }
}
