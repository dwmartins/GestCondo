<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvatarRequest extends FormRequest
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
            'avatar' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048'
            ]
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Erro na validação da foto',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'É necessário enviar uma imagem.',
            'avatar.image' => 'O arquivo deve ser uma imagem válida.',
            'avatar.mimes' => 'A imagem deve ser nos formatos: jpeg, jpg, png ou webp.',
            'avatar.max' => 'A imagem não pode ter mais que 2MB.'
        ];
    }
}
