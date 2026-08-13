<?php

namespace Modules\Courrier\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Courrier\Enums\AvisDg;

class RendreAvisDgRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transmettre', $this->route('courrier'));
    }

    public function rules(): array
    {
        return [
            'avis_dg' => ['required', Rule::enum(AvisDg::class)],
            'avis_dg_commentaire' => ['nullable', 'string'],
        ];
    }
}
