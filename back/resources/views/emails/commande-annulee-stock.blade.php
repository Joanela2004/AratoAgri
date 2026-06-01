<x-mail::message>
# Annulation partielle de votre commande

Bonjour **{{ $client->nomUtilisateur ?? 'cher client' }}**,

Nous sommes désolés de vous informer que nous devons **annuler votre commande** :

**Référence : #{{ $commande->referenceCommande }}**  
**Date : {{ $commande->dateCommande->format('d/m/Y') }}**  
**Montant total : {{ number_format($commande->montantTotal, 0, ',', ' ') }} Ar**

### Motif de l'annulation
Malheureusement, les produits suivants ne sont plus disponibles en quantité suffisante :

@foreach($produitsManquants as $produit)
- **{{ $produit['nom'] }}** — Quantité demandée : {{ $produit['poids'] }} kg (stock actuel insuffisant)
@endforeach

Un autre client a validé son paiement juste avant nous pour ces articles.

### Ce que nous avons fait
- Votre commande a été **annulée automatiquement**.
@if($etaitPaye)
- Vous serez **remboursé intégralement** sous 3 à 5 jours ouvrés.
@else
- Aucun montant n’a été débité car le paiement était encore en attente.
@endif

### Que faire maintenant ?
Vous pouvez **repasser commande** dès maintenant :
- Les produits encore disponibles sont toujours en stock.
- Vous pouvez **remplacer** les articles manquants par d’autres produits similaires (nous vous recommandons : [lien vers catégorie viande/poisson/etc.]).

<x-mail::button :url="url('/produits')">
Voir tous les produits disponibles
</x-mail::button>

### Pour nous faire pardonner
Nous vous offrons un **bon d’achat de 5 000 Ar** valable sur votre prochaine commande (code envoyé dans un second email).

Merci de votre compréhension, nous restons à votre disposition.

L’équipe de votre boutique
</x-mail::message>