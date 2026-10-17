<?php

namespace App\Http\Requests\Job;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangeStatusJobRequest extends FormRequest
{
     
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
     * No input fields are expected for the close action.
     * Ownership and status checks are performed in the controller.
     */
    public function rules(): array
    {
        return [
            "status"=>"required|in:closed,opened"
        ];
    }

    public function bodyParameters(): array
    {
        return [
           
            'status'=> ['description' => 'status , must be string closed or opened ',"example"=>"opened"],
        ];
    }
}
