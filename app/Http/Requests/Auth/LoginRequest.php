<?php

namespace App\Http\Requests\Auth;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class LoginRequest extends FormRequest
{
   public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
      throw new HttpResponseException(
          ResponseService::Response($validator->errors(),404,"validation errors")
      );
    }
     
    public function rules(): array
    {
        return [
          
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
