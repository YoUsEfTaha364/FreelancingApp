<?php

namespace App\Http\Requests\Freelancer;

use App\Models\Job;
use App\Models\Proposal;
use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePropsalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

        protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseService::Response($validator->errors(), 404, "validation errors")
        );
    }

    public function withValidator(Validator $validator){
        $validator->after(function () use ($validator) {
          $freelancer=$this->user();
          $job=Job::find($this->input("job_id"));

          $proposal=Proposal::where("freelancer_id",$freelancer->id)->where("job_id",$job->id)->first();

          if($proposal != null){
          
            $validator->errors()->add("proposal_uniqueness","You made a proposal before");
     
          }

        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job_id' => [
                'required',
                'integer',
                'exists:job_posts,id',
            ],
            "bid_amount"=>"required|integer|min:0",
            "cover_letter"=>"required|string|min:20",
            "estimated_duration"=>"nullable|integer|min:1"
        ];
    }
}
