@component('mail::message')
# Bonjour cher client !

Nous avons pensé à vous 🎁  
Voici les codes promo que vous pouvez utiliser pour profiter de réductions lors de vos prochains achats :

@foreach($promotions as $promo)
- **Code :** {{ $promo->codePromo }}
- **Promotion :** {{ $promo->nomPromotion }} – Recevez {{ $promo->valeur }}{{ $promo->typePromotion == 'Pourcentage' ? '%' : ' Ar' }} de réduction !
- **Valable jusqu’au :** {{ \Carbon\Carbon::parse($promo->dateFin)->format('d/m/Y') }}

> Ne ratez pas cette opportunité, appliquez ce code lors de votre prochain achat !
---
@endforeach

Nous espérons que cela rendra votre expérience shopping encore plus agréable 😃  

Merci de votre fidélité et à très bientôt !  
L’équipe de votre boutique.

@endcomponent
