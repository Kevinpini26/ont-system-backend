<?php

namespace Modules\Courrier\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('annoter', $this->route('courrier'));
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string'],
        ];
    }
}
