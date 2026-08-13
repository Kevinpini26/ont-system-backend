<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .entete { text-align: center; margin-bottom: 30px; }
        .entete h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .entete h2 { font-size: 13px; font-weight: normal; margin: 4px 0 0; }
        .titre { text-align: center; text-decoration: underline; font-size: 15px; margin: 24px 0 4px; text-transform: uppercase; }
        .sous-titre { text-align: center; color: #555; margin-bottom: 24px; }
        h3 { font-size: 13px; text-transform: uppercase; margin: 24px 0 8px; border-bottom: 1px solid #999; padding-bottom: 4px; }
        table.donnees { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.donnees th, table.donnees td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        table.donnees th { background: #f0f0f0; }
        .total { margin-top: 6px; font-weight: bold; text-align: right; }
        .signature { margin-top: 60px; text-align: right; }
        .pied { margin-top: 40px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="entete">
        <h1>République Démocratique du Congo</h1>
        <h2>Office National du Tourisme (ONT)</h2>
    </div>

    <div class="titre">Rapport périodique d'activité</div>
    <div class="sous-titre">
        Période : {{ $libellePeriode }} ({{ $debut->toDateString() }} au {{ $fin->toDateString() }})
    </div>

    <h3>Courriers traités par direction et par statut</h3>
    <table class="donnees">
        <thead>
            <tr>
                <th>Direction</th>
                @foreach ($statuts as $statut)
                    <th>{{ $statut->label() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($courriersParDirectionEtStatut as $code => $lignes)
                <tr>
                    <td>{{ $code }} — {{ $lignes->first()->direction_nom }}</td>
                    @foreach ($statuts as $statut)
                        <td>{{ $lignes->firstWhere('statut', $statut->value)->total ?? 0 }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($statuts) + 1 }}">Aucun courrier sur cette période.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p class="total">Total courriers sur la période : {{ $totalCourriers }}</p>

    <h3>Stagiaires accueillis par direction</h3>
    <table class="donnees">
        <thead>
            <tr>
                <th>Direction</th>
                <th>Stagiaires affectés sur la période</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stagiairesParDirection as $ligne)
                <tr>
                    <td>{{ $ligne->direction_code }} — {{ $ligne->direction_nom }}</td>
                    <td>{{ $ligne->total }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Aucune affectation de stagiaire sur cette période.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <p class="total">Total stagiaires accueillis sur la période : {{ $totalStagiaires }}</p>

    <div class="signature">
        <p>Fait à Kinshasa, le {{ now()->translatedFormat('d F Y') }}</p>
        <p>La Direction Générale</p>
    </div>

    <div class="pied">
        Document généré automatiquement par le système d'information de l'ONT — à l'attention de l'autorité de tutelle.
    </div>
</body>
</html>
