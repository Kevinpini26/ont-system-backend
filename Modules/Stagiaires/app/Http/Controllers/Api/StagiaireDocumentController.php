<?php

namespace Modules\Stagiaires\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Stagiaires\Enums\DocumentType;
use Modules\Stagiaires\Http\Requests\UploadDocumentRequest;
use Modules\Stagiaires\Http\Resources\StagiaireDocumentResource;
use Modules\Stagiaires\Models\Stagiaire;
use Modules\Stagiaires\Models\StagiaireDocument;
use Modules\Stagiaires\Services\StagiaireCircuitService;

class StagiaireDocumentController extends Controller
{
    public function __construct(private readonly StagiaireCircuitService $circuit) {}

    public function index(Stagiaire $stagiaire)
    {
        $this->authorize('view', $stagiaire);

        return StagiaireDocumentResource::collection($stagiaire->documents()->latest()->get());
    }

    public function store(UploadDocumentRequest $request, Stagiaire $stagiaire)
    {
        $fichier = $request->file('fichier');
        $chemin = $fichier->store("stagiaires/{$stagiaire->id}", 'local');

        $document = $this->circuit->ajouterDocument(
            $stagiaire,
            $request->user(),
            DocumentType::from($request->validated()['type']),
            $fichier->getClientOriginalName(),
            $chemin,
        );

        return (new StagiaireDocumentResource($document))->response()->setStatusCode(201);
    }

    public function download(Stagiaire $stagiaire, StagiaireDocument $document)
    {
        $this->authorize('view', $stagiaire);

        abort_unless($document->stagiaire_id === $stagiaire->id, 404);

        // L'attestation contient le détail des deux évaluations : jamais
        // accessible à la direction d'accueil, même une fois le dossier
        // clôturé (voir StagiairePolicy::voirEvaluationFinale()).
        if ($document->type === DocumentType::ATTESTATION_STAGE) {
            $this->authorize('voirEvaluationFinale', Stagiaire::class);
        }

        return Storage::disk('local')->download($document->chemin, $document->nom_original);
    }
}
