<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Email vérifié</title>
</head>
<body>
    <h1>Merci, {{ $user->nomUtilisateur }} !</h1>
    <p>Votre email a été confirmé avec succès.</p>
    <a href="{{ env('FRONTEND_URL') }}">Retour à la plateforme</a>
</body>
</html>
