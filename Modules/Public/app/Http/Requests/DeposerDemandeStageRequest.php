<?php

namespace Modules\Public\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Stagiaires\Models\DisponibiliteDemandesStage;

/**
 * Ne collecte volontairement aucune période souhaitée : les dates de stage
 * dépendent du calendrier de la DFP, pas d'un souhait exprimé par le
 * candidat au dépôt — les lui demander ici serait trompeur.
 *
 * Les pièces exigées dépendent de `type_stage` : lettre de stage seule pour
 * un stage académique, CV + diplôme d'État + dernier diplôme pour un stage
 * professionnel — jamais les deux jeux à la fois (voir DemandeStagePublicController).
 */
class DeposerDemandeStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 'mimes' et 'mimetypes' sont tous deux évalués par Laravel à
        // partir du contenu réel du fichier (finfo), jamais de l'extension
        // déclarée par le client.
        $regleFichier = ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png'];

        return [
            'candidat_nom' => ['required', 'string', 'max:255'],
            'candidat_email' => ['required', 'email', 'max:255'],
            'candidat_contact' => ['nullable', 'string', 'max:255'],
            'candidat_etablissement' => ['required', 'string', 'max:255'],
            'type_stage' => ['required', Rule::in(['academique', 'professionnel'])],
            'lettre_stage' => ['required_if:type_stage,academique', ...$regleFichier],
            'lettre_demande' => ['required_if:type_stage,professionnel', ...$regleFichier],
            'cv' => ['required_if:type_stage,professionnel', ...$regleFichier],
            'diplome_etat' => ['required_if:type_stage,professionnel', ...$regleFichier],
            'dernier_diplome' => ['required_if:type_stage,professionnel', ...$regleFichier],
        ];
    }

    /**
     * Rejet serveur d'un dépôt sur un type de stage fermé — la désactivation
     * côté DFP (voir DisponibiliteDemandesStage) ne serait qu'un habillage
     * visuel contournable si elle n'était pas aussi appliquée ici.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type_stage');

            if (! in_array($type, ['academique', 'professionnel'], true)) {
                return;
            }

            if (! DisponibiliteDemandesStage::actuelle()->ouvertPourType($type)) {
                $validator->errors()->add('type_stage', "Les demandes de stage {$type} ne sont pas ouvertes actuellement.");
            }
        });
    }
}
