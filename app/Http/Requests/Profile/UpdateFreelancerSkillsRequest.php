<?php

namespace App\Http\Requests\Profile;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFreelancerSkillsRequest extends FormRequest
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

    public function rules(): array
    {
        return [
            'skill_ids' => ['required', 'array', 'min:1'],

            'skill_ids.*' => [
                'required',
                'integer',
                'exists:skills,id',
            ],
        ];
    }
}
