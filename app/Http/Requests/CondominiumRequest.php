<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use App\Rules\NoMaliciousContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CondominiumRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255', new NoMaliciousContent()],
            'cnpj'          => ['required', 'string', 'regex:/^\d{14}$/', 'unique:condominiums', new NoMaliciousContent()],
            'company_type'  => ['required', 'string', 'max:100', new NoMaliciousContent()],
            'phone'         => ['required', 'max:20', new NoMaliciousContent()],
            'email'         => ['nullable', 'email', 'max:255', 'unique:condominiums', new NoMaliciousContent()],
            'postal_code'   => ['nullable', 'string', 'max:10', new NoMaliciousContent()],
            'street'        => ['nullable', 'string', 'max:255', new NoMaliciousContent()],
            'number'        => ['nullable', 'string', 'max:20', new NoMaliciousContent()],
            'neighborhood'  => ['nullable', 'string', 'max:100', new NoMaliciousContent()],
            'city'          => ['nullable', 'string', 'max:100', new NoMaliciousContent()],
            'state'         => ['nullable', 'string', 'max:2', new NoMaliciousContent()],
        ];
    }

    /**
     * Customize the validation error response.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        throw new HttpResponseException(
            response()->json([
                'message' => 'Erros de validação encontrados',
                'errors' => $errors,
            ], 422)
        );
    }
}
