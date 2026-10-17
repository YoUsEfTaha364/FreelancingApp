<?php

namespace App\Http\Requests\Job;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ListClientJobsRequest extends FormRequest
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
            'status'      => ['sometimes', 'in:open,closed'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'status'      => ['description' => 'Filter by job status. Allowed: open, closed.', 'example' => 'open'],
            'category_id' => ['description' => 'Filter by category ID.', 'example' => 1],
            'per_page'    => ['description' => 'Number of results per page (1–50). Default: 15.', 'example' => 10],
        ];
    }
}
