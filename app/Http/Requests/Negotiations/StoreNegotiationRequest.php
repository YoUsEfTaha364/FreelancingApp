<?php

namespace App\Http\Requests;

use App\Services\ResponseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreNegotiationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by the controller/policies.
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseService::Response($validator->errors(), 422, 'Validation errors')
        );
    }

    public function rules(): array
    {
        return [
            'proposed_amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],
            'message' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
