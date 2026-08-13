<?php

namespace Modules\Stagiaires\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Stagiaires\Http\Requests\ImporterHistoriqueStagiairesRequest;
use Modules\Stagiaires\Support\StagiaireCsvImporter;

class ImportHistoriqueController extends Controller
{
    public function __construct(private readonly StagiaireCsvImporter $importateur) {}

    public function importer(ImporterHistoriqueStagiairesRequest $request)
    {
        $rapport = $this->importateur->importer($request->file('fichier'), $request->user());

        return response()->json($rapport);
    }
}
