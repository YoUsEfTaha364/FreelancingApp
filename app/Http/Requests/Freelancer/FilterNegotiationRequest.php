<?php

namespace App\Http\Requests\Freelancer;

use App\Services\ResponseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class FilterNegotiationRequest extends FormRequest
{
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
            'status' => [
                'sometimes',
                'required',
                'in:pending,accepted,rejected,superseded,withdrawn',
            ],
        ];
    }
}
