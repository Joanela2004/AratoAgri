<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre adresse e-mail</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05); border: 1px solid #ddd;">
                    
                    <tr>
                        <td align="center" style="padding: 20px; border-bottom: 2px solid #28a745; background-color: #28a745; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h2 style="color: #ffffff; margin: 0;">Confirmation d'E-mail</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px;">
                            <h1 style="color: #333333; font-size: 24px; margin-top: 0;">
                                Bonjour, {{ $user->nomUtilisateur }} !
                            </h1>

                            <p style="color: #555555; font-size: 16px; line-height: 1.6;">
                                Merci de vous être inscrit(e) sur notre plateforme. Veuillez cliquer sur le bouton ci-dessous pour confirmer votre adresse e-mail et activer votre compte.
                            </p>

                            <table width="100%" cellspacing="0" cellpadding="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" 
                                           style="background-color: #28a745; 
                                                  color: #ffffff; 
                                                  padding: 12px 25px; 
                                                  text-decoration: none; 
                                                  border-radius: 5px; 
                                                  font-weight: bold; 
                                                  font-size: 16px; 
                                                  display: inline-block; 
                                                  border: 1px solid #28a745;">
                                            Vérifier mon E-mail
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; text-align: center;">
                                Si le bouton ne fonctionne pas, copiez et collez le lien ci-dessous dans votre navigateur :
                            </p>
                            <p style="font-size: 14px; color: #555555; word-break: break-all; text-align: center;">
                                <a href="{{ $url }}" style="color: #007bff;">{{ $url }}</a>
                            </p>

                            <p style="color: #555555; font-size: 14px; margin-top: 30px;">
                                Cordialement,<br>
                                L'équipe {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>

                   

                </table>
            </td>
        </tr>
    </table>

</body>
</html>