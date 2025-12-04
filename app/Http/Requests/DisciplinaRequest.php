<?php

namespace App\Http\Requests;

use App\Service\Utils;
use Illuminate\Foundation\Http\FormRequest;

class DisciplinaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $data = [
            'tipo' => 'required',
            'nome' => 'required',
            'nota' => 'nullable',
            'creditos' => 'required',
            'conversao' => 'nullable',
            'codigo' => 'nullable',
            'carga_horaria' => 'required|integer',
            'pedido_id' => 'nullable',
            'nome_materia_usp' => 'nullable'
        ];

        if($this->tipo == "Obrigatória" && $this->method() == 'POST'){
            $obg = [
            'file'     => 'required|mimes:pdf|max:85000',
        ];

            $data = array_merge($data,$obg);
        } else{
            $opt= [
            'file' => 'nullable|mimes:pdf|max:85000'
        ];
            $data = array_merge($data,$opt);
        }

        return $data;
    }

    protected function prepareForValidation()
    {
        $this->merge(
            ['nome_materia_usp' => Utils::nome_disciplina_usp($this->codigo)] ?? null
        );
    }

    public function messages()
    {
        return [

            'tipo.required' => 'Insira algo no campo Tipo da Disciplina.',
            'nome.required' => 'Insira algo no campo Nome da Disciplina.',
            'creditos.required' => 'Insira algo no campo Créditos.',
            'creditos.integer' => 'Créditos deve ser um campo numérico inteiro.',
            'carga_horaria.required' => 'Insira algo no campo Carga Horaria.',
            'carga_horaria.integer' => 'Campo carga horaria deve ser apenas números.',
            'codigo.required' => 'Insira algo no campo Código USP.',
            'file.required' => 'Insira um arquivo na disciplina obrigatória.',
            'file.max' => 'Tamanho do arquivo não suportado.',
            'file.mimes' => 'Somente arquivos PDFs são aceitos.',

        ];
    }

}
