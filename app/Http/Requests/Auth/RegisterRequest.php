<?php

namespace App\Http\Requests\Auth;

use App\Services\ResponseService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Override;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:admin,client,freelancer'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
