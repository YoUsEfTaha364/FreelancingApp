<?php

namespace App\Http\Requests\Profile;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateFreelancerLanguagesRequest extends FormRequest
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
        'languages' => ['required', 'array', 'min:1'],

        'languages.*.language_id' => [
            'required',
            'integer',
            'exists:languages,id',
        ],

        'languages.*.proficiency' => [
            'required',
            'string',
            'in:beginner,intermediate,fluent,native',
        ],
    ];
}
}
