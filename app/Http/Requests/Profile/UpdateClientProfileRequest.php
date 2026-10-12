<?php

namespace App\Http\Requests\Profile;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateClientProfileRequest extends FormRequest
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

    
public function bodyParameters(): array
{
    return [
        'company_name' => [
            'description' => 'The client company name.',
            'example' => 'Tech Solutions',
        ],
        'phone' => [
            'description' => 'Egyptian mobile number starting with 010, 011, 012, or 015.',
            'example' => '01012345678',
        ],
        'country' => [
            'description' => 'The client country.',
            'example' => 'Egypt',
        ],
        'city' => [
            'description' => 'The client city.',
            'example' => 'Cairo',
        ],
    ];
}



   
    public function rules(): array
    {
         return [
            'company_name' => ['nullable', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'regex:/^(010|011|012|015)[0-9]{8}$/',
            ],

            'country' => ['required', 'string', 'max:100'],

            'city' => ['required', 'string', 'max:100'],
        ];
    }
}
