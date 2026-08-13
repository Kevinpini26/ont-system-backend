<x-mail::message>
# Nouveau stagiaire affecté

Un stagiaire vient d'être affecté à votre direction par la DFP.

<x-mail::panel>
**Nom :** {{ $stagiaire->nom }}<br>
**Établissement :** {{ $stagiaire->etablissement_origine }}
</x-mail::panel>

<x-mail::button :url="config('app.frontend_url').'/direction/stagiaires'">
Consulter mes stagiaires
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
