<?php

namespace Modules\Public\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeposerCourrierExterneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Le contenu (document TipTap) arrive en JSON string : la requête est
     * multipart à cause de piece_jointe. Décodé ici pour que la règle
     * 'array' ci-dessous s'applique normalement.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('contenu'))) {
            $this->merge(['contenu' => json_decode($this->input('contenu'), true)]);
        }
    }

    public function rules(): array
    {
        return [
            'expediteur_externe_nom' => ['required', 'string', 'max:255'],
            'expediteur_externe_email' => ['required', 'email', 'max:255'],
            'expediteur_externe_telephone' => ['nullable', 'string', 'max:255'],
            'objet' => ['required', 'string', 'max:255'],
            'contenu' => ['nullable', 'array'],
            // 'mimes' et 'mimetypes' sont tous deux évalués par Laravel à
            // partir du contenu réel du fichier (finfo), jamais de
            // l'extension déclarée par le client.
            'piece_jointe' => [
                'required',
                'file',
                'max:5120',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
            ],
        ];
    }
}
