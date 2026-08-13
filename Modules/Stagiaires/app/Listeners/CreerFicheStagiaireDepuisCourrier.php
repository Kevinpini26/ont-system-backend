<?php

namespace Modules\Stagiaires\Listeners;

use Illuminate\Support\Facades\Storage;
use Modules\Courrier\Events\CourrierStageAvisFavorable;
use Modules\Stagiaires\Contracts\DoublonDetector;
use Modules\Stagiaires\Enums\DocumentType;
use Modules\Stagiaires\Enums\StagiaireStatut;
use Modules\Stagiaires\Enums\StagiaireTypeStage;
use Modules\Stagiaires\Models\Stagiaire;

/**
 * Crée automatiquement la fiche Stagiaire dès qu'un courrier de demande de
 * stage reçoit un avis favorable de la DG, sans aucune ressaisie manuelle.
 */
class CreerFicheStagiaireDepuisCourrier
{
    public function __construct(private readonly DoublonDetector $doublons) {}

    public function handle(CourrierStageAvisFavorable $event): void
    {
        $stagiaire = Stagiaire::query()->firstOrCreate(
            ['courrier_id' => $event->courrier->id],
            [
                'nom' => $event->candidatNom(),
                'contact' => $event->candidatContact(),
                'etablissement_origine' => $event->candidatEtablissement(),
                'type_stage' => $event->typeStage() ?? StagiaireTypeStage::ACADEMIQUE->value,
                'periode_debut_demandee' => $event->periodeDebutDemandee(),
                'periode_fin_demandee' => $event->periodeFinDemandee(),
                'reference_courrier' => $event->referenceCourrier(),
                'statut' => StagiaireStatut::DOSSIER_RECU,
            ],
        );

        // Ne bloque jamais la création : signalement pour arbitrage par la
        // DFP. On ne recherche un doublon que pour une fiche fraîchement
        // créée (wasRecentlyCreated) — sinon un événement rejoué par
        // firstOrCreate re-testerait à tort une fiche déjà existante.
        if ($stagiaire->wasRecentlyCreated && ! $stagiaire->doublon_suspecte) {
            $doublon = $this->doublons->trouverDoublon($stagiaire);

            if ($doublon) {
                $stagiaire->update([
                    'doublon_suspecte' => true,
                    'doublon_stagiaire_id' => $doublon->id,
                ]);
            }
        }

        if (! $stagiaire->wasRecentlyCreated) {
            return;
        }

        if ($event->courrier->lettre_stage_chemin) {
            $this->copierPiece($stagiaire, $event->courrier->lettre_stage_chemin, DocumentType::LETTRE_STAGE_UNIVERSITE, 'lettre-stage');
        }
        if ($event->cvChemin()) {
            $this->copierPiece($stagiaire, $event->cvChemin(), DocumentType::CV, 'cv');
        }
        if ($event->diplomeEtatChemin()) {
            $this->copierPiece($stagiaire, $event->diplomeEtatChemin(), DocumentType::DIPLOME_ETAT, 'diplome-etat');
        }
        if ($event->dernierDiplomeChemin()) {
            $this->copierPiece($stagiaire, $event->dernierDiplomeChemin(), DocumentType::DERNIER_DIPLOME, 'dernier-diplome');
        }
        if ($event->lettreDemandeChemin()) {
            $this->copierPiece($stagiaire, $event->lettreDemandeChemin(), DocumentType::LETTRE_DEMANDE_STAGE, 'lettre-demande');
        }
    }

    /**
     * Duplique le fichier (plutôt que de le référencer) dans le stockage
     * propre au module Stagiaires : la fiche doit rester consultable même
     * si le courrier d'origine est un jour anonymisé/purgé.
     */
    private function copierPiece(Stagiaire $stagiaire, string $cheminSource, DocumentType $type, string $nomBase): void
    {
        $extension = pathinfo($cheminSource, PATHINFO_EXTENSION);
        $cheminDestination = "stagiaires/{$stagiaire->id}/".uniqid("{$nomBase}-").".{$extension}";

        Storage::disk('local')->copy($cheminSource, $cheminDestination);

        $stagiaire->documents()->create([
            'type' => $type,
            'nom_original' => "{$nomBase}-{$stagiaire->reference_courrier}.{$extension}",
            'chemin' => $cheminDestination,
            'uploaded_by_id' => null,
        ]);
    }
}
