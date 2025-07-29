<?php

namespace App\Http\Requests;

use App\Rules\NoMaliciousContent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('id');

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:suporte,sindico,morador'],

            'account_status' => ['boolean'],
            'description' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'complement' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'accepts_emails' => ['boolean']
        ];

        if ($this->hasFile('avatar')) {
            $baseRules['avatar'] = ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'];
        } else {
            $baseRules['avatar'] = ['nullable', 'string', 'max:100'];
        }

        foreach ($baseRules as $field => &$rules) {
            if (is_array($rules)) {
                $rules[] = new NoMaliciousContent();
            }
        }

        return $baseRules;
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

    public function messages(): array
    {
        return [
            'avatar.max' => 'A imagem não pode ter mais que 2 MB.',
            'avatar.image' => 'O arquivo enviado deve ser uma imagem.',
            'avatar.mimes' => 'A imagem deve estar no formato JPG, JPEG ou PNG.',
        ];
    }

}
