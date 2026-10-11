<?php

namespace App\Http\Requests\Profile;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFreelancerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function bodyParameters(): array
{
    return [
        'gender' => [
            'description' => 'The freelancer gender. Allowed values: male, female.',
            'example' => 'male',
        ],

        'title' => [
            'description' => 'The freelancer professional title. Maximum 255 characters.',
            'example' => 'Backend Laravel Developer',
        ],

        'biography' => [
            'description' => 'A description of the freelancer skills, experience, and professional background. Maximum 2000 characters.',
            'example' => 'I am a backend developer specializing in Laravel, RESTful APIs, and MySQL.',
        ],

        'phone' => [
            'description' => 'The freelancer contact phone number. Maximum 20 characters.',
            'example' => '+201001234567',
        ],

        'country' => [
            'description' => 'The country where the freelancer is located. Maximum 100 characters.',
            'example' => 'Egypt',
        ],

        'city' => [
            'description' => 'The city where the freelancer is located. Maximum 100 characters.',
            'example' => 'Zagazig',
        ],
    ];
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
            'gender' => ['required', 'string', 'in:male,female'],

            'title' => ['required', 'string', 'max:255'],

            'biography' => ['required', 'string', 'max:2000'],

            'phone' => ['required', 'string', 'max:20'],

            'country' => ['required', 'string', 'max:100'],

            'city' => ['required', 'string', 'max:100'],
        ];
    }
}
