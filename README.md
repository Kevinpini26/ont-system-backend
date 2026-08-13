# ONT — Backend

Système d'information de l'Office National du Tourisme de la RDC : API REST pure (Laravel 13, Sanctum, PostgreSQL), organisée en modules indépendants via [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules).

Aucune vue Blade n'est servie : le backend n'expose que du JSON. Le frontend (React + Vite, dossier `frontend/`) consomme cette API, y compris l'éditeur de texte riche TipTap pour la rédaction des courriers — le backend ne fait que stocker/retourner le contenu structuré (JSON), sans rendu HTML serveur.

## Modules

| Module      | Rôle |
|-------------|------|
| `Kernel`    | Utilisateurs, directions, authentification, Global Scope par direction, policies transverses |
| `Courrier`  | Circuit courrier (machine à états), annotations, relecture, numérotation, événement `CourrierStageAvisFavorable` |
| `Stagiaires`| Cycle de vie du stagiaire, affectation, présences, documents, double évaluation, attestation PDF |
| `Public`    | Endpoint public (sans authentification) de suivi de dossier |

Le code applicatif vit entièrement sous `Modules/<Nom>/app`, jamais dans `app/` (qui ne contient que le câblage framework : `Controller` de base, `bootstrap/app.php`, exceptions globales).

## Installation

```bash
composer install
cp .env.example .env   # puis renseigner DB_* (PostgreSQL)
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Un compte administrateur est créé par le seeder : `admin@ont.cd` / `ChangeMoi#ONT2026` — **à changer immédiatement après le premier déploiement réel**, voir `SECURITY.md` à la racine du dépôt.

### Base de données

PostgreSQL (paramétrable dans `.env` : `DB_CONNECTION=pgsql`). Les tests utilisent une base séparée (`ont_testing`, voir `phpunit.xml`).

### Tests

```bash
php artisan test
```

49 tests Feature couvrant : authentification, filtrage par direction et journal d'audit (Kernel), progression stricte du circuit courrier, refus de signature sans relecture et tableau de bord statistique (Courrier), création automatique de la fiche stagiaire, calcul de la moyenne des évaluations et tableau de bord DFP (Stagiaires), consultation publique d'un dossier (Public).

### Génération du PDF d'attestation

Le module Stagiaires utilise `barryvdh/laravel-dompdf`. Le PDF est stocké sur le disque `local` (`storage/app/attestations/`).

## Authentification

Jetons Sanctum (`Authorization: Bearer <token>`). Toutes les routes sauf `POST /api/v1/auth/login` et le module `Public` exigent ce header. Les jetons expirent après `SANCTUM_TOKEN_EXPIRATION` minutes (8h par défaut) et un administrateur peut les révoquer manuellement (`DELETE /api/v1/users/{id}/tokens`). Voir `SECURITY.md` à la racine du dépôt pour le détail des mesures de sécurité (rate limiting, CORS, journal d'audit, politique de mot de passe...).

## Rôles et postes (module Kernel)

- **Rôles** : `administrateur`, `agent_dfp`, `responsable_direction`, `agent_circuit_courrier`.
- **Postes** (uniquement pour `agent_circuit_courrier`) : `reception`, `protocole`, `dga`, `assistant_protocole` (Ass.P), `assistant_1` (Ass1), `assistant_2` (Ass2), `assistant_dga` (Ass.Dga), `dg`, `secretariat_1`, `secretariat_2`.
- Tout utilisateur sauf `administrateur` et `agent_dfp` est rattaché à une `direction_id`.

### Global Scope par direction

`Modules\Kernel\Scopes\DirectionScope` (et son équivalent bidirectionnel `Modules\Courrier\Scopes\CourrierDirectionScope`) filtrent automatiquement les enregistrements selon `direction_id` de l'utilisateur connecté. Le contournement (voir toutes les directions) s'applique à :
- `administrateur` et `agent_dfp` (toujours) ;
- `agent_circuit_courrier` dont le poste figure dans `config('kernel.circuit_courrier_central_postes')` (par défaut : tous les postes du circuit central, car le circuit courrier est par nature transverse aux directions).

La règle de contournement est un point d'extension : `Modules\Kernel\Contracts\DirectionScopeBypassResolver`, bindée par défaut sur `DefaultDirectionScopeBypassResolver`.

---

## Endpoints — Module Kernel

| Méthode | URL | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/auth/login` | — | `{email, password, device_name?}` → `{user, token}` |
| POST | `/api/v1/auth/logout` | ✔ | Révoque le jeton courant |
| GET  | `/api/v1/auth/me` | ✔ | Profil de l'utilisateur connecté |
| GET | `/api/v1/agents-circuit-courrier` | ✔ (tous) | Liste minimale (id, nom, poste) des agents du circuit courrier — sert à désigner un relecteur |
| GET | `/api/v1/notifications` | ✔ | Notifications de l'utilisateur (`data`, `non_lues`) |
| POST | `/api/v1/notifications/{id}/marquer-lu` | ✔ | Marque une notification comme lue |
| POST | `/api/v1/notifications/marquer-toutes-lues` | ✔ | Marque toutes les notifications comme lues |
| GET  | `/api/v1/directions` | ✔ (tous) | Liste des 8 directions |
| GET  | `/api/v1/directions/{id}` | ✔ (tous) | Détail d'une direction |
| POST | `/api/v1/directions` | ✔ admin | Créer une direction |
| PUT/PATCH | `/api/v1/directions/{id}` | ✔ admin | Modifier une direction |
| DELETE | `/api/v1/directions/{id}` | ✔ admin | Supprimer une direction |
| GET | `/api/v1/users` | ✔ admin | Liste paginée des comptes |
| POST | `/api/v1/users` | ✔ admin | Créer un compte (`role`, `poste?`, `direction_id?`) |
| GET | `/api/v1/users/{id}` | ✔ admin ou soi-même | Détail d'un compte |
| PUT/PATCH | `/api/v1/users/{id}` | ✔ admin | Modifier un compte |
| DELETE | `/api/v1/users/{id}` | ✔ admin | Supprimer un compte |
| DELETE | `/api/v1/users/{id}/tokens` | ✔ admin | Révoque tous les jetons Sanctum du compte (compte compromis) |
| GET | `/api/v1/audit-logs` | ✔ admin | Journal d'audit (connexions, signatures, affectations, notations), filtrable par `action`, `user_id`, `depuis` |

---

## Endpoints — Module Courrier

Circuit strict : `recu → au_protocole → en_circuit_hierarchique → en_attente_avis_dg → projet_reponse_en_cours → en_relecture → signe → enregistre`. Chaque transition n'est possible que vers l'étape immédiatement suivante, et seulement par le poste habilité (voir `config('courrier.circuit_transitions')`, point d'extension `Modules\Courrier\Contracts\CircuitTransitionRules`).

| Méthode | URL | Poste requis | Description |
|---|---|---|---|
| GET | `/api/v1/courriers` | ✔ (filtré par direction) | Liste paginée |
| POST | `/api/v1/courriers` | `reception` **ou** `responsable_direction` | Création (`objet`, `type`, `direction_origine_id?`, `direction_destination_id?`, champs `candidat_*` si `type=demande_stage`) → statut `recu` + génère `numero_accuse_reception`. Si l'auteur est un `responsable_direction`, `direction_origine_id` est forcé à sa propre direction (une direction ne peut pas usurper une autre direction d'origine). |
| GET | `/api/v1/courriers/{id}` | ✔ (filtré) | Détail |
| POST | `/api/v1/courriers/{id}/transmettre-protocole` | `protocole` | `recu → au_protocole` |
| POST | `/api/v1/courriers/{id}/transmettre-circuit-hierarchique` | `protocole` | `au_protocole → en_circuit_hierarchique` |
| POST | `/api/v1/courriers/{id}/transmettre-avis-dg` | `dga` | `en_circuit_hierarchique → en_attente_avis_dg` |
| POST | `/api/v1/courriers/{id}/rendre-avis` | `dg` | `{avis_dg: favorable\|defavorable\|reserve, avis_dg_commentaire?}` → `en_attente_avis_dg → projet_reponse_en_cours`. Si `avis_dg=favorable` et `type=demande_stage`, émet `CourrierStageAvisFavorable`. |
| POST | `/api/v1/courriers/{id}/soumettre-projet-reponse` | `secretariat_1` | `{projet_reponse_contenu (JSON TipTap), relecteur_id}` → `projet_reponse_en_cours → en_relecture` |
| POST | `/api/v1/courriers/{id}/valider-relecture` | relecteur désigné uniquement | `{relecture_commentaire?}` — ne change pas le statut, débloque la signature |
| POST | `/api/v1/courriers/{id}/signer` | `dg` | `en_relecture → signe` — **refusé (422) si la relecture n'a pas été validée** |
| POST | `/api/v1/courriers/{id}/enregistrer` | `secretariat_2` | `{classification: interne\|externe, note_technique?, accuse_reception_partenaire?}` → `signe → enregistre`, génère `numero_enregistrement` |
| GET | `/api/v1/courriers/{id}/annotations` | ✔ (filtré) | Liste des annotations horodatées |
| POST | `/api/v1/courriers/{id}/annotations` | ✔ (filtré) | `{contenu}` |
| GET | `/api/v1/courriers/statistiques` | `agent_circuit_courrier` ou admin | Tableau de bord : volumétrie par étape, en attente de relecture, temps moyen de traitement par étape (calculé sur l'historique `courrier_transitions`) |

Toute tentative de saut d'étape ou de mauvais poste renvoie **422** (`TransitionNonAutoriseeException`) si le poste correspond à l'étape courante mais vise le mauvais statut cible, ou **403** si le poste ne correspond pas du tout à l'étape courante (policy `CourrierPolicy@transmettre`).

---

## Endpoints — Module Stagiaires

Statuts : `dossier_recu → en_attente_affectation → affecte → stage_en_cours → evaluation_en_cours → cloture`.

La fiche est créée **automatiquement** (aucune route de création) par `Modules\Stagiaires\Listeners\CreerFicheStagiaireDepuisCourrier`, à l'écoute de `CourrierStageAvisFavorable`.

| Méthode | URL | Rôle requis | Description |
|---|---|---|---|
| GET | `/api/v1/stagiaires` | ✔ (filtré) | Liste, filtrable par `direction_id`, `statut`, `echeance_proche=1` (stages finissant sous 10 jours) |
| GET | `/api/v1/stagiaires/{id}` | ✔ (filtré) | Détail |
| POST | `/api/v1/stagiaires/{id}/examiner-dossier` | `agent_dfp` | `dossier_recu → en_attente_affectation` |
| POST | `/api/v1/stagiaires/{id}/affecter` | `agent_dfp` | `{direction_id}` (parmi les 8 actives) → `en_attente_affectation → affecte`, notifie le responsable de la direction |
| POST | `/api/v1/stagiaires/{id}/valider-arrivee` | `agent_dfp` | `{date_debut_stage, date_fin_stage}` → `affecte → stage_en_cours` |
| POST | `/api/v1/stagiaires/{id}/terminer-stage` | `agent_dfp` ou responsable de la direction d'accueil | `stage_en_cours → evaluation_en_cours` |
| POST | `/api/v1/stagiaires/{id}/evaluer-direction` | responsable de la direction d'accueil correspondante | `{note (0-20), commentaire?}` — note le travail effectué |
| POST | `/api/v1/stagiaires/{id}/evaluer-dfp` | `agent_dfp` | `{note (0-20), commentaire?}` — note discipline/assiduité/comportement |
| GET | `/api/v1/stagiaires/{id}/presences` | ✔ (filtré) | Liste des présences |
| POST | `/api/v1/stagiaires/{id}/presences` | responsable de la direction d'accueil | `{date_debut, date_fin, present, commentaire?}` (jour unique : `date_debut = date_fin`) |
| GET | `/api/v1/stagiaires/{id}/documents` | ✔ (filtré) | Liste des documents |
| POST | `/api/v1/stagiaires/{id}/documents` | `agent_dfp` ou responsable de la direction d'accueil | Upload multipart `{type, fichier}` (`lettre_motivation`, `piece_identite`, `attestation_inscription`) |
| GET | `/api/v1/stagiaires/{id}/documents/{docId}/telecharger` | ✔ (filtré) | Télécharge un document (y compris l'attestation générée) |
| GET | `/api/v1/stagiaires/statistiques` | `agent_dfp` ou admin | Tableau de bord DFP : effectifs actifs, répartition par direction/statut, échéances ≤ 10 jours, note moyenne sur une période (`depuis`, `jusqua`) |

Dès que `evaluation_direction_note` **et** `evaluation_dfp_note` sont renseignées (quel que soit l'ordre), le système calcule automatiquement `note_finale` (point d'extension `Modules\Stagiaires\Contracts\CalculateurNoteFinale`, moyenne par défaut), passe le statut à `cloture` et génère le PDF d'attestation (`Modules\Stagiaires\Contracts\AttestationGenerator`).

Alerte d'échéance : commande planifiée quotidienne `stagiaires:verifier-echeances` (notifie DFP + direction d'accueil 10 jours avant `date_fin_stage`).

---

## Endpoints — Module Public

| Méthode | URL | Auth |Description |
|---|---|---|---|
| GET | `/api/v1/public/dossiers/{numero_accuse_reception}` | **aucune** | Statut simplifié du dossier (courrier + fiche stagiaire éventuelle). Ne renvoie jamais les annotations, avis interne ou note technique. |

---

## Points d'extension (SOLID)

| Interface | Rôle | Implémentation par défaut |
|---|---|---|
| `Modules\Kernel\Contracts\DirectionScopeBypassResolver` | Qui contourne le filtrage par direction | `DefaultDirectionScopeBypassResolver` (config-driven) |
| `Modules\Courrier\Contracts\CircuitTransitionRules` | Ordre des statuts + poste habilité par étape | `ConfigCircuitTransitionRules` (`config/courrier.php`) |
| `Modules\Courrier\Contracts\NumeroGenerator` / `SequenceGenerator` | Génération des numéros (AR, enregistrement) | `DefaultNumeroGenerator` / `DatabaseSequenceGenerator` (upsert atomique PostgreSQL) |
| `Modules\Stagiaires\Contracts\AffectationRules` | Directions éligibles à l'affectation | `ActiveDirectionsAffectationRules` (directions `actif=true`) |
| `Modules\Stagiaires\Contracts\CalculateurNoteFinale` | Critère de calcul de la note finale | `MoyenneCalculateurNoteFinale` |
| `Modules\Stagiaires\Contracts\AttestationGenerator` | Génération du PDF d'attestation | `DompdfAttestationGenerator` |

Chaque interface est bindée dans le `register()` du `ServiceProvider` de son module — aucune logique métier codée en dur dans les contrôleurs.
