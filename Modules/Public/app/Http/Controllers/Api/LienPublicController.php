<?php

namespace Modules\Public\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Public\Http\Requests\SoumettreRetourRequest;
use Modules\Public\Http\Resources\LienPublicResource;
use Modules\Stagiaires\Enums\TypeLienPublic;
use Modules\Stagiaires\Models\StagiaireLienPublic;
use Modules\Stagiaires\Models\StagiaireRetour;
use Modules\Stagiaires\Services\StagiaireCircuitService;

/**
 * Points d'accès publics (sans authentification) accessibles via un lien à
 * usage unique envoyé à un stagiaire : signature de convention et retour
 * d'expérience. Un stagiaire n'a pas de compte utilisateur Sanctum, ces
 * actions ne peuvent donc pas passer par le circuit authentifié habituel.
 */
class LienPublicController extends Controller
{
    public function __construct(private readonly StagiaireCircuitService $circuit) {}

    private function trouverLienValide(string $token, TypeLienPublic $type): StagiaireLienPublic
    {
        $lien = StagiaireLienPublic::query()
            ->where('token', $token)
            ->where('type', $type)
            ->with(['stagiaire.direction'])
            ->firstOrFail();

        abort_unless($lien->estValide(), 410, 'Ce lien a déjà été utilisé.');

        return $lien;
    }

    public function show(string $token)
    {
        $lien = StagiaireLienPublic::query()->where('token', $token)->with(['stagiaire.direction'])->firstOrFail();

        return new LienPublicResource($lien);
    }

    public function telechargerConvention(string $token)
    {
        $lien = StagiaireLienPublic::query()
            ->where('token', $token)
            ->where('type', TypeLienPublic::CONVENTION)
            ->with('stagiaire')
            ->firstOrFail();

        abort_unless($lien->stagiaire->convention_chemin, 404);

        return Storage::disk('local')->download(
            $lien->stagiaire->convention_chemin,
            "convention-stage-{$lien->stagiaire->nom}.pdf",
        );
    }

    public function signerConvention(string $token)
    {
        $lien = $this->trouverLienValide($token, TypeLienPublic::CONVENTION);

        $this->circuit->signerConventionStagiaire($lien->stagiaire);
        $lien->consommer();

        return response()->json(['message' => 'Convention signée avec succès.']);
    }

    public function soumettreRetour(SoumettreRetourRequest $request, string $token)
    {
        $lien = $this->trouverLienValide($token, TypeLienPublic::RETOUR_EXPERIENCE);

        StagiaireRetour::query()->create([
            ...$request->validated(),
            'stagiaire_id' => $lien->stagiaire_id,
            'created_at' => now(),
        ]);

        $lien->consommer();

        return response()->json(['message' => 'Merci pour votre retour.']);
    }
}
