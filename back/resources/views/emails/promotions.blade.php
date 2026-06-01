<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Code Promo</title>
</head>
<body>
    <h2>Bonjour {{ $promo['nomClient'] }} !</h2>

    <p>Nous avons pensé à vous 🎁</p>

    <p>Voici votre code promo exclusif :</p>

    <ul>
        <li><strong>Code :</strong> {{ $promo['codePromo'] }}</li>
        <li><strong>Promotion :</strong> {{ $promo['nomPromotion'] }}</li>
        <li><strong>Réduction :</strong> {{ $promo['valeur'] }}{{ $promo['type'] == 'Pourcentage' ? '%' : ' Ar' }}</li>
        <li><strong>Valable jusqu'au :</strong> {{ \Carbon\Carbon::parse($promo['dateFin'])->format('d/m/Y') }}</li>
    </ul>

    <p>Utilisez ce code lors de votre prochain achat pour profiter de votre réduction !</p>

    <p>Merci de votre fidélité !<br>L’équipe de votre boutique.</p>
</body>
</html>
