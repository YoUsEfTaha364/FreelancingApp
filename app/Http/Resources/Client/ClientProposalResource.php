<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'freelancer' => [
                'id' => $this->freelancer->id,
                'name' => $this->freelancer->name,
            ],

            'bid_amount' => $this->bid_amount,
            'agreed_amount' => $this->agreed_amount,
            'estimated_duration' => $this->estimated_duration,
            'cover_letter' => $this->cover_letter,
            'status' => $this->getRawOriginal('status'),

            'created_at' => $this->created_at,

            'negotiations' => NegotiationResource::collection(
                $this->whenLoaded('negotiations')
            ),
        ];
    }
}
