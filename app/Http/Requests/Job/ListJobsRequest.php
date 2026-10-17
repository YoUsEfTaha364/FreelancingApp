<?php

namespace App\Http\Requests\Job;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListJobsRequest extends FormRequest
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
            'category_id'      => ['sometimes', 'integer', 'exists:categories,id'],
            'experience_level' => ['sometimes', 'in:entry,intermediate,expert'],
            'budget_min'       => ['sometimes', 'numeric', 'min:0'],
            'budget_max'       => ['sometimes', 'numeric', 'min:0'],
            'skill_ids'        => ['sometimes', 'array'],
            'skill_ids.*'      => ['integer', 'exists:skills,id'],
            'per_page'         => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'category_id'      => ['description' => 'Filter by category ID.', 'example' => 1],
            'experience_level' => ['description' => 'Filter by experience level. Allowed: entry, intermediate, expert.', 'example' => 'intermediate'],
            'budget_min'       => ['description' => 'Filter jobs with budget_max >= this value.', 'example' => 500],
            'budget_max'       => ['description' => 'Filter jobs with budget_min <= this value.', 'example' => 3000],
            'skill_ids'        => ['description' => 'Filter jobs that require all given skill IDs.', 'example' => [1, 2]],
            'per_page'         => ['description' => 'Results per page (1–50). Default: 15.', 'example' => 10],
        ];
    }
}
