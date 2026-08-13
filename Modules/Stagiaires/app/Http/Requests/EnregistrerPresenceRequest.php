<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;
use Modules\Stagiaires\Models\Stagiaire;

class EnregistrerPresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('gererPresence', Stagiaire::class);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'heure_arrivee' => ['nullable', 'date_format:H:i'],
            'heure_depart' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * `after:heure_arrivee` n'est pas utilisable en règle statique : sur une
     * saisie du départ seul (arrivée déjà enregistrée lors d'un appel
     * précédent, upsert), heure_arrivee est absent de cette requête — la
     * comparaison ne peut se faire ici que si les deux sont fournis
     * ensemble.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('date') && Carbon::parse($this->input('date'))->isWeekend()) {
                $validator->errors()->add('date', 'Le pointage ne concerne que les jours ouvrés (lundi à vendredi).');
            }

            if (! $this->filled('heure_arrivee') && ! $this->filled('heure_depart')) {
                $validator->errors()->add('heure_arrivee', "Renseignez au moins l'heure d'arrivée ou l'heure de départ.");
            }

            if ($this->filled('heure_arrivee') && $this->filled('heure_depart')
                && $this->input('heure_depart') <= $this->input('heure_arrivee')) {
                $validator->errors()->add('heure_depart', "L'heure de départ doit être après l'heure d'arrivée.");
            }
        });
    }
}
