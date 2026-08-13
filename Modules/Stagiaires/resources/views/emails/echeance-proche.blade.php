<x-mail::message>
# Échéance de stage dans 10 jours

Le stage suivant se termine dans 10 jours.

<x-mail::panel>
**Stagiaire :** {{ $stagiaire->nom }}<br>
**Direction d'accueil :** {{ $stagiaire->direction?->nom }}<br>
**Fin de stage :** {{ optional($stagiaire->date_fin_stage)->translatedFormat('d F Y') }}
</x-mail::panel>

<x-mail::button :url="config('app.frontend_url').'/stagiaires/'.$stagiaire->id">
Consulter le dossier
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
