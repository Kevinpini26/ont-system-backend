<?php

namespace Modules\Stagiaires\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Kernel\Contracts\AuditLogger;
use Modules\Stagiaires\Models\DisponibiliteDemandesStage;
use Modules\Stagiaires\Models\Stagiaire;

class DisponibiliteDemandesStageController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function show()
    {
        $disponibilite = DisponibiliteDemandesStage::actuelle();

        return response()->json([
            'academique' => $disponibilite->academique_ouvert,
            'professionnel' => $disponibilite->professionnel_ouvert,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('gererDisponibiliteDemandesStage', Stagiaire::class);

        $data = $request->validate([
            'type' => ['required', Rule::in(['academique', 'professionnel'])],
            'ouvert' => ['required', 'boolean'],
        ]);

        $disponibilite = DisponibiliteDemandesStage::actuelle();
        $champ = $data['type'].'_ouvert';
        $disponibilite->update([$champ => $data['ouvert']]);

        $this->audit->enregistrer('demande_stage.disponibilite_modifiee', null, $request->user(), [
            'type' => $data['type'],
            'ouvert' => $data['ouvert'],
        ]);

        return response()->json([
            'academique' => $disponibilite->academique_ouvert,
            'professionnel' => $disponibilite->professionnel_ouvert,
        ]);
    }
}
