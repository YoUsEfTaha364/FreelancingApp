<?php

namespace App\Http\Resources\Freelancer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=>$this->id,
             "job-title"=>$this->job->title,
             "job-description"=>$this->job->description,
             "bid_amount"=>$this->bid_amount,
             "cover_letter"=>$this->cover_letter,
            'status' => $this->getRawOriginal('status'),
        ];
    }
}
