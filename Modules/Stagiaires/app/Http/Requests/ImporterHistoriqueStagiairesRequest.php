<?php

namespace Modules\Stagiaires\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Kernel\Enums\UserRole;

class ImporterHistoriqueStagiairesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::ADMINISTRATEUR;
    }

    public function rules(): array
    {
        return [
            // 'mimes' et 'mimetypes' sont évalués par Laravel à partir du
            // contenu réel du fichier (finfo), jamais de l'extension
            // déclarée par le client.
            'fichier' => [
                'required',
                'file',
                'max:5120',
                'mimes:csv,txt',
                'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel',
            ],
        ];
    }
}
