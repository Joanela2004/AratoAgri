@component('mail::message')
# Code de réinitialisation

Votre code de réinitialisation est :

@component('mail::panel')
**{{ $code }}**
@endcomponent

Ce code expire dans 10 minutes.

Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.

Cordialement,
@endcomponent