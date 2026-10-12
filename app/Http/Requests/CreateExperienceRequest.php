<?php

namespace App\Http\Requests;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateExperienceRequest extends FormRequest
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


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */


    public function rules(): array
    {
        return [
            'job_title' => ['required', 'string', 'max:255'],

            'company_name' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string', 'max:2000'],

            'start_date' => ['required', 'date'],

            'is_current' => ['required', 'boolean'],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
                'required_if:is_current,false',
                'prohibited_if:is_current,true',
            ],
        ];
    }
}
