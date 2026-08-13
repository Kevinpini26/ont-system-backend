<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1a1a1a; }
        .entete-tableau { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #184b85; padding-bottom: 12px; }
        .entete-logo { width: 50px; }
        .entete-texte { text-align: center; }
        .entete-texte h1 { font-size: 16px; margin: 0; text-transform: uppercase; color: #184b85; }
        .entete-texte h2 { font-size: 13px; font-weight: normal; margin: 4px 0 0; }
        .titre { text-align: center; text-decoration: underline; font-size: 15px; margin: 30px 0; text-transform: uppercase; }
        .corps { line-height: 1.8; text-align: justify; }
        .tableau { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .tableau td { padding: 6px 10px; border: 1px solid #999; }
        .badge-reussite { vertical-align: middle; margin-left: 8px; }
        .signature { margin-top: 60px; text-align: right; }
        .verification { margin-top: 50px; text-align: center; }
        .verification img { width: 90px; height: 90px; }
        .verification p { font-size: 10px; color: #555; margin: 4px 0 0; }
    </style>
</head>
<body>
    @include('stagiaires::partials.entete')

    <div class="titre">Attestation de stage</div>

    <div class="corps">
        <p>
            L'Office National du Tourisme atteste que <strong>{{ $stagiaire->nom }}</strong>,
            de l'établissement {{ $stagiaire->etablissement_origine }}, a effectué un
            <strong>{{ mb_strtolower($stagiaire->type_stage->label()) }}</strong>
            au sein de la direction {{ $stagiaire->direction?->nom }}
            du {{ optional($stagiaire->date_debut_stage)->translatedFormat('d F Y') }}
            au {{ optional($stagiaire->date_fin_stage)->translatedFormat('d F Y') }}.
        </p>
    </div>

    <table class="tableau">
        <tr>
            <td>Numéro d'attestation</td>
            <td>{{ $stagiaire->numero_attestation }}</td>
        </tr>
        <tr>
            <td>Référence du courrier</td>
            <td>{{ $stagiaire->reference_courrier }}</td>
        </tr>
        <tr>
            <td>Type de stage</td>
            <td>{{ $stagiaire->type_stage->label() }}</td>
        </tr>
        <tr>
            <td>Évaluation de la direction d'accueil</td>
            <td>{{ $stagiaire->evaluation_direction_total }} / 100</td>
        </tr>
        <tr>
            <td>Évaluation de la DFP</td>
            <td>{{ $stagiaire->evaluation_dfp_total }} / 100</td>
        </tr>
        <tr>
            <td><strong>Note finale</strong></td>
            <td>
                <strong>{{ $stagiaire->note_finale }} / 100</strong>
                {{-- dompdf ne rend pas de manière fiable un <svg> inline : comme pour le QR
                     code de vérification ci-dessous, on passe par un <img> en data URI. --}}
                <img class="badge-reussite" src="{{ $badgeReussiteDataUri }}" alt="Sceau de réussite" width="22" height="22">
            </td>
        </tr>
    </table>

    <div class="signature">
        <p>Fait à Kinshasa, le {{ now()->translatedFormat('d F Y') }}</p>
        <p>La Direction de la Formation et de la Professionnalisation</p>
    </div>

    @if($qrCodeDataUri)
        <div class="verification">
            <img src="{{ $qrCodeDataUri }}" alt="QR code de vérification">
            <p>Scannez ce code pour vérifier l'authenticité de cette attestation<br>({{ $stagiaire->numero_attestation }})</p>
        </div>
    @endif
</body>
</html>
