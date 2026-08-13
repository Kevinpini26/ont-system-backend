<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DefinirObjectifsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('definirObjectifs', $this->route('stagiaire'));
    }

    public function rules(): array
    {
        return [
            'objectifs' => ['required', 'array', 'min:2', 'max:5'],
            'objectifs.*' => ['required', 'string', 'max:255'],
        ];
    }
}
