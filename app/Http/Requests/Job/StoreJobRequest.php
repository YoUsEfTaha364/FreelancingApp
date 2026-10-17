<?php

namespace App\Http\Requests\Job;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreJobRequest extends FormRequest
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
            ResponseService::Response($validator->errors(), 422, 'Validation errors')
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
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:5000'],
            'category_id'      => ['required', 'integer', 'exists:categories,id'],
            'budget_min'       => ['nullable', 'numeric', 'min:0'],
            'budget_max'       => ['nullable', 'numeric', 'gte:budget_min'],
            'experience_level' => ['required', 'in:entry,intermediate,expert'],
            'deadline'         => ['nullable', 'date', 'after:today'],
            'skill_ids'        => ['nullable', 'array', 'min:1'],
            'skill_ids.*'      => ['integer', 'exists:skills,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title'            => ['description' => 'Job title.', 'example' => 'Build a Laravel REST API'],
            'description'      => ['description' => 'Detailed job description.', 'example' => 'We need a skilled Laravel developer...'],
            'category_id'      => ['description' => 'Existing category ID.', 'example' => 1],
            'budget_min'       => ['description' => 'Minimum budget in USD.', 'example' => 500],
            'budget_max'       => ['description' => 'Maximum budget in USD. Must be >= budget_min.', 'example' => 2000],
            'experience_level' => ['description' => 'Required freelancer experience level. Allowed: entry, intermediate, expert.', 'example' => 'intermediate'],
            'deadline'         => ['description' => 'Optional project deadline date (must be in the future).', 'example' => '2027-03-01'],
            'skill_ids'        => ['description' => 'Optional array of existing skill IDs.', 'example' => [1, 2, 3]],
        ];
    }
}
