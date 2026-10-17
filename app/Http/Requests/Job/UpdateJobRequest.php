<?php

namespace App\Http\Requests\Job;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateJobRequest extends FormRequest
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
     * All fields are optional (partial update semantics).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'            => ['sometimes', 'string', 'max:255'],
            'description'      => ['sometimes', 'string', 'max:5000'],
            'category_id'      => ['sometimes', 'integer', 'exists:categories,id'],
            'budget_min'       => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'budget_max'       => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'experience_level' => ['sometimes', 'in:entry,intermediate,expert'],
            'deadline'         => ['sometimes', 'nullable', 'date', 'after:today'],
            'skill_ids'        => ['sometimes', 'nullable', 'array'],
            'skill_ids.*'      => ['integer', 'exists:skills,id'],
        ];
    }

    /**
     * Add cross-field budget validation that respects partial updates
     * by falling back to the persisted value when a boundary is omitted.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var \App\Models\Job $job */
            $job = $this->route('job');

            if (! $job) {
                return;
            }

            $budgetMin = $this->has('budget_min')
                ? $this->input('budget_min')
                : $job->budget_min;

            $budgetMax = $this->has('budget_max')
                ? $this->input('budget_max')
                : $job->budget_max;

            if ($budgetMin !== null && $budgetMax !== null && (float) $budgetMin > (float) $budgetMax) {
                $validator->errors()->add('budget_max', 'The budget max must be greater than or equal to budget min.');
            }
        });
    }

    public function bodyParameters(): array
    {
        return [
            'title'            => ['description' => 'Job title.', 'example' => 'Build a React Dashboard'],
            'description'      => ['description' => 'Detailed job description.', 'example' => 'Updated description...'],
            'category_id'      => ['description' => 'Existing category ID.', 'example' => 2],
            'budget_min'       => ['description' => 'Minimum budget in USD.', 'example' => 300],
            'budget_max'       => ['description' => 'Maximum budget in USD.', 'example' => 1500],
            'experience_level' => ['description' => 'Required freelancer experience level. Allowed: entry, intermediate, expert.', 'example' => 'expert'],
            'deadline'         => ['description' => 'Optional project deadline date.', 'example' => '2027-06-01'],
            'skill_ids'        => ['description' => 'Array of existing skill IDs. Provide empty array to remove all skills. Omit to leave skills unchanged.', 'example' => [1, 4]],
        ];
    }
}
