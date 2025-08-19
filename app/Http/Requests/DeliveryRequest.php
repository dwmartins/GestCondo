<?php

namespace App\Http\Requests;

use App\Rules\NoMaliciousContent;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeliveryRequest extends FormRequest
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
            'id'                 => ['nullable'],
            'condominium_id'     => ['required'],
            'user_id'            => ['nullable'],
            'employee_id'        => ['nullable'],
            'item_description'   => ['required', 'string', 'max:255'],
            'status'             => ['required', 'in:pendente,entregue,devolvido'],
            'received_at'        => ['required', 'date', 'before_or_equal:today'],
            'delivered_at'       => ['nullable', 'date'],
            'delivered_to_name'  => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
        ];

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
            'condominium_id.required'       => 'O condomínio é obrigatório.',
            'package_description.required'  => 'A descrição do pacote é obrigatória.',
            'status.required'               => 'O status é obrigatório.',
            'status.in'                     => 'O status selecionado é inválido. Escolha entre pendente, entregue ou devolvido.',
            'received_at.required'          => 'A data de recebimento é obrigatória.',
            'received_at.before_or_equal' => 'A data de recebimento não pode ser no futuro.',
        ];
    }
}
