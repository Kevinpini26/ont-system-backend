<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .entete-tableau { width: 100%; margin-bottom: 24px; border-bottom: 2px solid #184b85; padding-bottom: 10px; }
        .entete-logo { width: 50px; }
        .entete-texte { text-align: center; }
        .entete-texte h1 { font-size: 16px; margin: 0; text-transform: uppercase; color: #184b85; }
        .entete-texte h2 { font-size: 13px; font-weight: normal; margin: 4px 0 0; }
        .references { width: 100%; margin-bottom: 24px; font-size: 11px; color: #444; }
        .references td { padding: 2px 0; }
        .objet { font-weight: bold; margin-bottom: 20px; }
        .corps { line-height: 1.8; text-align: justify; }
        .corps p { margin: 0 0 10px; }
        .corps h1, .corps h2, .corps h3 { margin: 14px 0 8px; }
        .corps ul, .corps ol { margin: 0 0 10px; padding-left: 20px; }
        .signature { margin-top: 60px; text-align: right; }
        .signature .nom { font-weight: bold; }
    </style>
</head>
<body>
    @include('courrier::partials.entete')

    <table class="references">
        <tr>
            <td><strong>N° d'accusé de réception :</strong> {{ $courrier->numero_accuse_reception }}</td>
            <td style="text-align: right;"><strong>Date :</strong> {{ $courrier->signe_at->translatedFormat('d F Y') }}</td>
        </tr>
        @if ($courrier->numero_enregistrement)
            <tr>
                <td><strong>N° d'enregistrement :</strong> {{ $courrier->numero_enregistrement }}</td>
                <td></td>
            </tr>
        @endif
    </table>

    <p class="objet">Objet : {{ $courrier->objet }}</p>

    <div class="corps">
        {!! $corpsHtml !!}
    </div>

    <div class="signature">
        <p class="nom">{{ $courrier->signataire?->name }}</p>
        <p>{{ $courrier->signataire?->poste?->label() ?? $courrier->signataire?->role?->label() }}</p>
    </div>
</body>
</html>
