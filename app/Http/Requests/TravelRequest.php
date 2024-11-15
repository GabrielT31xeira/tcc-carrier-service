<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelRequest extends FormRequest
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
            'saida.cidade' => 'required|string|max:255',
            'saida.estado' => 'required|string|max:2',
            'saida.endereco' => 'required|string|max:255',
            'saida.latitude' => 'required|numeric',
            'saida.longitude' => 'required|numeric',
            'chegada.cidade' => 'required|string|max:255',
            'chegada.estado' => 'required|string|max:2',
            'chegada.endereco' => 'required|string|max:255',
            'chegada.latitude' => 'required|numeric',
            'chegada.longitude' => 'required|numeric',
            'veiculo.placa' => 'required|string|max:10|unique:vehicles,plate',
            'veiculo.tipo_veiculo' => 'required|string|max:50',
            'veiculo.marca' => 'required|string|max:50',
            'veiculo.modelo' => 'required|string|max:50',
            'veiculo.ano_modelo' => 'required|integer|min:1900|max:' . date('Y'),
            'veiculo.ano_fabricacao' => 'required|integer|min:1900|max:' . date('Y'),
        ];
    }

    public function messages(): array
    {
        return [
            'saida.cidade.required' => 'A cidade de saída é obrigatória.',
            'saida.estado.required' => 'O estado de saída é obrigatório.',
            'saida.endereco.required' => 'O endereço de saída é obrigatório.',
            'saida.latitude.required' => 'A latitude de saída é obrigatória.',
            'saida.longitude.required' => 'A longitude de saída é obrigatória.',
            'chegada.cidade.required' => 'A cidade de chegada é obrigatória.',
            'chegada.estado.required' => 'O estado de chegada é obrigatório.',
            'chegada.endereco.required' => 'O endereço de chegada é obrigatório.',
            'chegada.latitude.required' => 'A latitude de chegada é obrigatória.',
            'chegada.longitude.required' => 'A longitude de chegada é obrigatória.',
            'veiculo.placa.required' => 'A placa é obrigatória.',
            'veiculo.placa.string' => 'A placa deve ser uma string.',
            'veiculo.placa.max' => 'A placa não pode ter mais de 10 caracteres.',
            'veiculo.placa.unique' => 'Esta placa já está registrada.',
            'veiculo.tipo_veiculo.required' => 'O tipo de veículo é obrigatório.',
            'veiculo.tipo_veiculo.string' => 'O tipo de veículo deve ser uma string.',
            'veiculo.tipo_veiculo.max' => 'O tipo de veículo não pode ter mais de 50 caracteres.',
            'veiculo.marca.required' => 'A marca é obrigatória.',
            'veiculo.marca.string' => 'A marca deve ser uma string.',
            'veiculo.marca.max' => 'A marca não pode ter mais de 50 caracteres.',
            'veiculo.modelo.required' => 'O modelo é obrigatório.',
            'veiculo.modelo.string' => 'O modelo deve ser uma string.',
            'veiculo.modelo.max' => 'O modelo não pode ter mais de 50 caracteres.',
            'veiculo.ano_modelo.required' => 'O ano do modelo é obrigatório.',
            'veiculo.ano_modelo.integer' => 'O ano do modelo deve ser um número inteiro.',
            'veiculo.ano_modelo.min' => 'O ano do modelo deve ser no mínimo 1900.',
            'veiculo.ano_modelo.max' => 'O ano do modelo não pode ser maior que o ano atual.',
            'veiculo.ano_fabricacao.required' => 'O ano de fabricação é obrigatório.',
            'veiculo.ano_fabricacao.integer' => 'O ano de fabricação deve ser um número inteiro.',
            'veiculo.ano_fabricacao.min' => 'O ano de fabricação deve ser no mínimo 1900.',
            'veiculo.ano_fabricacao.max' => 'O ano de fabricação não pode ser maior que o ano atual.',
        ];
    }
}
