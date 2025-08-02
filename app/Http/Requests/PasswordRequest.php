<?php

namespace App\Http\Requests;

use App\Rules\NoMaliciousContent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'newPassword' => ['required', 'string', 'min:8', new NoMaliciousContent()],
            'confirmPassword' => ['required', 'same:newPassword', new NoMaliciousContent()]
        ];
    }

    /**
     * Customize the validation error response.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Erros de validação',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    public function messages(): array
{
    return [
        'newPassword.required' => 'Por favor, insira uma nova senha',
        'newPassword.min' => 'A senha deve ter no mínimo 8 caracteres',
        'confirmPassword.same' => 'As senhas não coincidem'
    ];
}
}
