<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NegotiationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'proposal_id' => $this->proposal_id,

            'proposed_by' => $this->proposed_by,

            'proposed_amount' => $this->proposed_amount,

            'message' => $this->message,

            'status' => $this->getRawOriginal('status'),

            'responded_at' => $this->responded_at,

            'created_at' => $this->created_at,
        ];
    }
}
