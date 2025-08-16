<?php

namespace App\Http\Requests;

use App\Models\UserPermission;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeStatusRequest extends FormRequest
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
        $rules = [
            'account_status' => ['required', 'boolean'],
            'employee.status' => ['required', 'string'],
            'permissions' => ['required', 'array'],
        ];

        foreach (UserPermission::defaultPermissions() as $module => $actions) {
            $rules["permissions.$module"] = ['required', 'array'];

            foreach ($actions as $action => $value) {
                $rules["permissions.$module.$action"] = ['required', 'boolean'];
            }
        }

        return $rules;
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
            'employee.status.required' => 'O status do trabalhador é obrigatório.',
            'account_status.required' => 'O status da conta é obrigatório.',
            'permissions.required' => 'As permissões são obrigatórias.'
        ];
    }
}
