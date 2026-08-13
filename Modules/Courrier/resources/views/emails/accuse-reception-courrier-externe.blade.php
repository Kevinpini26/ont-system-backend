<x-mail::message>
Bonjour {{ $courrier->expediteur_externe_nom }},

Nous avons bien reçu votre courrier adressé à l'Office National du Tourisme : « {{ $courrier->objet }} ».

Voici votre numéro d'accusé de réception : **{{ $courrier->numero_accuse_reception }}**

Conservez ce numéro, il vous permettra de suivre l'état de traitement de votre courrier.

<x-mail::button :url="config('app.frontend_url').'/suivi-dossier?numero='.$courrier->numero_accuse_reception">
Suivre l'état de mon dossier
</x-mail::button>

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
