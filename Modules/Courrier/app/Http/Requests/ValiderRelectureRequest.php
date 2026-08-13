<?php

namespace Modules\Courrier\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValiderRelectureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('validerRelecture', $this->route('courrier'));
    }

    public function rules(): array
    {
        return [
            'relecture_commentaire' => ['nullable', 'string'],
        ];
    }
}
