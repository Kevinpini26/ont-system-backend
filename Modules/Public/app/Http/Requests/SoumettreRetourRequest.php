<?php

namespace Modules\Public\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoumettreRetourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_encadrement' => ['required', 'integer', 'between:1,5'],
            'note_missions' => ['required', 'integer', 'between:1,5'],
            'note_ambiance' => ['required', 'integer', 'between:1,5'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
