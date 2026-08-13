<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Modules\Stagiaires\Models\Stagiaire;

class ProlongerStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('prolonger', Stagiaire::class);
    }

    public function rules(): array
    {
        return [
            'nouvelle_date_fin' => [
                'required',
                'date',
                function (string $attribute, $value, $fail) {
                    $dateFinActuelle = $this->route('stagiaire')->date_fin_stage;

                    if ($dateFinActuelle && ! Carbon::parse($value)->gt($dateFinActuelle)) {
                        $fail('La nouvelle date de fin doit être postérieure à la date de fin actuelle.');
                    }
                },
            ],
            'motif' => ['required', 'string', 'max:1000'],
        ];
    }
}
