<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Stagiaires\Models\Stagiaire;

class OuvrirPeriodeEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ouvrirPeriodeEvaluation', Stagiaire::class);
    }

    public function rules(): array
    {
        return [];
    }
}
