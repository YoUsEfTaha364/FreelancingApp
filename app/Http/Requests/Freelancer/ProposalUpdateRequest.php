<?php

namespace App\Http\Requests\Freelancer;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProposalUpdateRequest extends FormRequest
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
            'cover_letter' => ['sometimes', 'required', 'string'],
            'estimated_duration' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'bid_amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
        ];
    }
}
