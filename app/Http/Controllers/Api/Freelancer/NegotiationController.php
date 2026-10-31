<?php

namespace App\Http\Controllers\Api\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Resources\NegotiationResource;
use App\Models\Negotiation;
use App\Models\Proposal;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NegotiationController extends Controller
{

    /**
     * List proposal negotiations
     *
     * Retrieve paginated negotiations associated with a proposal owned
     * by the authenticated freelancer. Results can be filtered by status.
     *
     * @group Freelancer - Negotiations
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the freelancer's proposal. Example: 12
     *
     * @queryParam status string Filter negotiations by status. Allowed values: pending, accepted, rejected, superseded, withdrawn. Example: pending
     * @queryParam page integer The pagination page number. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Negotiations retrieved successfully.",
     *   "data": {
     *     "negotiations": [
     *       {
     *         "id": 8,
     *         "proposal_id": 12,
     *         "proposed_by": 3,
     *         "proposed_amount": "400.00",
     *         "message": "My budget is 400.",
     *         "status": "pending",
     *         "responded_at": null
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
     *   "message": "You are not authorized to access this proposal."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "status": [
     *       "The selected status is invalid."
     *     ]
     *   }
     * }
     */

    public function index(Request $request, Proposal $proposal)
    {
        $this->authorizeProposal($proposal);

        $validated = $request->validate([
            'status' => [
                'sometimes',
                'required',
                'in:pending,accepted,rejected,superseded,withdrawn',
            ],
        ]);

        $query = $proposal->negotiations()
            ->with('proposer')
            ->latest();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $negotiations = $query->paginate(15);

        $pagination = [
            'current_page' => $negotiations->currentPage(),
            'per_page' => $negotiations->perPage(),
            'total' => $negotiations->total(),
            'last_page' => $negotiations->lastPage(),
            'next_page_url' => $negotiations->nextPageUrl(),
            'prev_page_url' => $negotiations->previousPageUrl(),
        ];

        return ResponseService::Response(
            ['negotiations' => NegotiationResource::collection(
                $negotiations
            ), "pagination" => $pagination],
            200,
            'Negotiations retrieved successfully.'
        );
    }


    /**
     * Show a proposal negotiation
     *
     * Retrieve a specific negotiation belonging to a proposal owned
     * by the authenticated freelancer.
     *
     * @group Freelancer - Negotiations
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the freelancer's proposal. Example: 12
     * @urlParam negotiation integer required The ID of the negotiation. Example: 8
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Negotiation retrieved successfully.",
     *   "data": {
     *     "negotiation": {
     *       "id": 8,
     *       "proposal_id": 12,
     *       "proposed_by": 3,
     *       "proposed_amount": "400.00",
     *       "message": "My budget is 400.",
     *       "status": "pending",
     *       "responded_at": null
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this proposal."
     * }
     *
     * @response 404 {
     *   "message": "Negotiation not found for this proposal."
     * }
     */

    public function show(Proposal $proposal, Negotiation $negotiation)
    {
        $this->authorizeProposal($proposal);
        $this->ensureNegotiationBelongsToProposal($proposal, $negotiation);

        $negotiation->load('proposer');

        return ResponseService::Response([
            'negotiation' => new NegotiationResource($negotiation),
        ], 200, 'Negotiation retrieved successfully.');
    }

    /**
     * Accept a client's counteroffer.
     */


    /**
     * Accept a counteroffer
     *
     * Accept a pending counteroffer created by the client.
     * The negotiation is marked as accepted, the agreed amount is saved
     * on the proposal, and the proposal status becomes accepted.
     * Other pending negotiations for the same proposal are superseded.
     *
     * @group Freelancer - Negotiations
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the freelancer's proposal. Example: 12
     * @urlParam negotiation integer required The ID of the negotiation. Example: 8
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Counteroffer accepted successfully.",
     *   "data": {
     *     "negotiation": {
     *       "id": 8,
     *       "proposal_id": 12,
     *       "proposed_amount": "400.00",
     *       "status": "accepted"
     *     },
     *     "proposal": {
     *       "id": 12,
     *       "agreed_amount": "400.00",
     *       "status": "accepted"
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this proposal."
     * }
     *
     * @response 404 {
     *   "message": "Negotiation not found for this proposal."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "negotiation": [
     *       "This negotiation is no longer available for acceptance."
     *     ]
     *   }
     * }
     */


    public function accept(Proposal $proposal, Negotiation $negotiation)
    {
        $this->authorizeProposal($proposal);
        $this->ensureNegotiationBelongsToProposal($proposal, $negotiation);

        $updatedNegotiation = DB::transaction(function () use (
            $proposal,
            $negotiation
        ) {
            // Lock the proposal to serialize competing acceptance requests.
            $lockedProposal = Proposal::query()
                ->whereKey($proposal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedNegotiation = $lockedProposal->negotiations()
                ->whereKey($negotiation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedProposal->status !== 'pending' ||
                $lockedNegotiation->status !== 'pending'
            ) return ResponseService::Response([], 404, "Can not accept pending negothiation");


            $lockedNegotiation->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Close any other pending counteroffers for this proposal.
            $lockedProposal->negotiations()
                ->where('id', '!=', $lockedNegotiation->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'superseded',
                    'responded_at' => now(),
                    'updated_at' => now(),
                ]);

            $lockedProposal->update([
                'agreed_amount' => $lockedNegotiation->proposed_amount,
                'status' => 'accepted',
            ]);

            return $lockedNegotiation->fresh();
        });

        return ResponseService::Response([
            'negotiation' => new NegotiationResource($updatedNegotiation),
            'proposal' => [
                'id' => $proposal->id,
                'agreed_amount' => $updatedNegotiation->proposed_amount,
                'status' => 'accepted',
            ],
        ], 200, 'Counteroffer accepted successfully.');
    }


    /**
     * Reject a counteroffer
     *
     * Reject a pending counteroffer created by the client.
     * The negotiation status becomes rejected. The proposal remains pending,
     * allowing the client to make another counteroffer.
     *
     * @group Freelancer - Negotiations
     * @authenticated
     *
     * @urlParam proposal integer required The ID of the freelancer's proposal. Example: 12
     * @urlParam negotiation integer required The ID of the negotiation. Example: 8
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Counteroffer rejected successfully.",
     *   "data": {
     *     "negotiation": {
     *       "id": 8,
     *       "proposal_id": 12,
     *       "proposed_amount": "400.00",
     *       "status": "rejected"
     *     }
     *   }
     * }
     *
     * @response 403 {
     *   "message": "You are not authorized to access this proposal."
     * }
     *
     * @response 404 {
     *   "message": "Negotiation not found for this proposal."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "negotiation": [
     *       "This negotiation is no longer available for rejection."
     *     ]
     *   }
     * }
     */

    public function reject(Proposal $proposal, Negotiation $negotiation)
    {
        $this->authorizeProposal($proposal);
        $this->ensureNegotiationBelongsToProposal($proposal, $negotiation);

        $updatedNegotiation = DB::transaction(function () use (
            $proposal,
            $negotiation
        ) {
            $lockedProposal = Proposal::query()
                ->whereKey($proposal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedNegotiation = $lockedProposal->negotiations()
                ->whereKey($negotiation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedProposal->status !== 'pending' ||
                $lockedNegotiation->status !== 'pending'
            ) {
                throw ValidationException::withMessages([
                    'negotiation' =>
                    'This negotiation is no longer available for rejection.',
                ]);
            }

            $lockedNegotiation->update([
                'status' => 'rejected',
                'responded_at' => now(),
            ]);

            return $lockedNegotiation->fresh();
        });

        return ResponseService::Response([
            'negotiation' => new NegotiationResource($updatedNegotiation),
        ], 200, 'Counteroffer rejected successfully.');
    }

    /**
     * Ensure the authenticated freelancer owns the proposal.
     */
    private function authorizeProposal(Proposal $proposal): void
    {
        abort_unless(
            $proposal->freelancer_id === Auth::id(),
            403,
            'You are not authorized to access this proposal.'
        );
    }

    /**
     * Ensure the negotiation belongs to the given proposal.
     */
    private function ensureNegotiationBelongsToProposal(
        Proposal $proposal,
        Negotiation $negotiation
    ): void {
        abort_unless(
            $negotiation->proposal_id === $proposal->id,
            404,
            'Negotiation not found for this proposal.'
        );
    }
}
