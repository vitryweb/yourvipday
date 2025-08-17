<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Confirmation d'inscription</h1>
    <p>
        Merci<?php echo($_POST["prenom"]); ?>; Votre profil a bien été créé. Nous vous contacterons bientôt pour vous proposer une expérience unique et personnalisée. <?php echo($_POST["mail"]); ?>.
    </p>
    <p>
        <a href="connexion.php">Se connecter</a>
    </p>
</body>
</html>