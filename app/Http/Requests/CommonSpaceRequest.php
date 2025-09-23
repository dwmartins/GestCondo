<?php

namespace App\Http\Requests;

use App\Rules\NoMaliciousContent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommonSpaceRequest extends FormRequest
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
        $baseRules = [
            'id' => ['nullable'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'condominium_id' => ['required'],
            'rules' => ['nullable'],
            'manual_approval' => ['required', 'boolean'],
            'status' => ['required', 'boolean']
        ];

        foreach ($baseRules as $key => &$rules) {
            if(is_array($rules)) {
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
}
