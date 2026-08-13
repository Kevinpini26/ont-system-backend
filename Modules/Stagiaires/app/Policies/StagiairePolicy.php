<?php

namespace Modules\Stagiaires\Policies;

use Modules\Kernel\Enums\Poste;
use Modules\Kernel\Enums\UserRole;
use Modules\Kernel\Models\User;
use Modules\Stagiaires\Models\Stagiaire;

class StagiairePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Stagiaire $stagiaire): bool
    {
        return true;
    }

    public function gererDossier(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Action sensible : affecter un stagiaire à une direction d'accueil.
     */
    public function affecter(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Réaffectation en cours de stage : même réserve que affecter() — DFP
     * uniquement, jamais la direction d'accueil (actuelle ou nouvelle).
     */
    public function reaffecter(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    public function terminerStage(User $user, Stagiaire $stagiaire): bool
    {
        return $user->role === UserRole::AGENT_DFP
            || ($user->role === UserRole::RESPONSABLE_DIRECTION && $user->direction_id === $stagiaire->direction_id);
    }

    /**
     * La direction ne peut évaluer que si la DFP a explicitement ouvert la
     * période d'évaluation pour ce dossier précis (voir
     * ouvrirPeriodeEvaluation()) — jamais d'accès au formulaire avant ça.
     */
    public function evaluerEnTantQueDirection(User $user, Stagiaire $stagiaire): bool
    {
        return $user->role === UserRole::RESPONSABLE_DIRECTION
            && $user->direction_id === $stagiaire->direction_id
            && $stagiaire->periode_evaluation_ouverte_at !== null;
    }

    public function evaluerEnTantQueDfp(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Réservée à la DFP : donne à la direction l'accès à son formulaire
     * d'évaluation pour ce dossier. Utilisable dès stage_en_cours (pour les
     * stages qui approchent de leur fin), pas seulement une fois
     * evaluation_en_cours.
     */
    public function ouvrirPeriodeEvaluation(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Résultat de l'évaluation (détail des deux grilles + moyenne) :
     * strictement confidentiel, jamais visible par la direction d'accueil
     * — même périmètre que voirRetour().
     */
    public function voirEvaluationFinale(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP || $user->role === UserRole::ADMINISTRATEUR;
    }

    /**
     * Gestion des présences (créer, modifier, consulter) : centralisée à la
     * DFP, jamais à la direction d'accueil — pour éviter une double source
     * de vérité sur l'assiduité utilisée dans l'évaluation finale.
     */
    public function gererPresence(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Informations complémentaires (lieu de naissance, filière, niveau,
     * maître/conseiller de stage) : centralisées à la DFP, même logique que
     * gererPresence() — le maître de stage est de toute façon "désigné par
     * la DFP", pas de raison d'ouvrir la modification à la direction.
     */
    public function gererInformationsComplementaires(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Stage académique : modification directe des dates, sans historique
     * formel — voir prolonger() pour le stage professionnel, mécanisme
     * distinct avec traçabilité.
     */
    public function modifierDatesStage(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    /**
     * Stage professionnel uniquement : prolongation tracée (voir
     * StagiaireProlongation), jamais une simple modification de dates.
     */
    public function prolonger(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }

    public function gererDocument(User $user, Stagiaire $stagiaire): bool
    {
        return $user->role === UserRole::AGENT_DFP
            || ($user->role === UserRole::RESPONSABLE_DIRECTION && $user->direction_id === $stagiaire->direction_id);
    }

    /**
     * Vision agrégée (effectifs, répartition par direction) : transverse à
     * toutes les directions pour DFP, administrateur et le poste DG
     * (tableau de bord DG — jamais les fiches individuelles, seulement ces
     * agrégats). Un responsable de direction y a aussi accès, mais reçoit
     * alors des agrégats déjà restreints à sa seule direction par le
     * Global Scope Eloquent de Stagiaire (tableau de bord "direction
     * d'accueil") — pas une exception à cette règle de confidentialité,
     * juste le même filtrage que partout ailleurs pour ce rôle.
     */
    public function voirStatistiques(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP
            || $user->role === UserRole::ADMINISTRATEUR
            || ($user->role === UserRole::AGENT_CIRCUIT_COURRIER && $user->poste === Poste::DG)
            || $user->role === UserRole::RESPONSABLE_DIRECTION;
    }

    /**
     * Fiche de mission : la direction d'accueil définit les objectifs une
     * fois le stage démarré.
     */
    public function definirObjectifs(User $user, Stagiaire $stagiaire): bool
    {
        return $user->role === UserRole::RESPONSABLE_DIRECTION && $user->direction_id === $stagiaire->direction_id;
    }

    public function signerConventionDirection(User $user, Stagiaire $stagiaire): bool
    {
        return $user->role === UserRole::RESPONSABLE_DIRECTION && $user->direction_id === $stagiaire->direction_id;
    }

    /**
     * Retour d'expérience : strictement réservé à la DFP et à
     * l'administrateur — jamais à la direction d'accueil concernée, pour
     * préserver la sincérité des retours.
     */
    public function voirRetour(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP || $user->role === UserRole::ADMINISTRATEUR;
    }

    /**
     * Ouverture/fermeture des demandes de stage par type : réservée à la
     * DFP, qui pilote seule le calendrier des candidatures.
     */
    public function gererDisponibiliteDemandesStage(User $user): bool
    {
        return $user->role === UserRole::AGENT_DFP;
    }
}
