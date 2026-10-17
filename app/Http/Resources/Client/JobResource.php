<?php

namespace App\Http\Resources\Client;

use App\Http\Resources\SkillResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
   
    public function toArray(Request $request): array
    {
       
           return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'experience_level' => $this->experience_level,
            'deadline' => $this->deadline,
            'status' => $this->status,
            "category"=>CategoryResource::make($this->whenLoaded('category')),
            'skills' => SkillResource::collection(
                $this->whenLoaded('skills')
            ),
            'created_at' => $this->created_at
        ];
    }
}
