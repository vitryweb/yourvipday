<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formulaire2.css">
    <title>Poster un message</title>
</head>
<body>
    <?php
    $pseudo="";
    $mail="";
    $message="";
        if(!empty($_POST)){
            // Récupération des valeurs
            $pseudo = $_POST["pseudo"];
            $mail = $_POST["mail"];
            $message = $_POST["message"];
            //Gestion des erreurs
            if ($pseudo=="" or $mail=="" or $message==""){
                $erreur="Merci de saisir toutes les informations !";
            }
            elseif (strlen($message)> 180){
                $erreur="Le message est trop longs";
            }
            elseif (!filter_var($mail,FILTER_VALIDATE_EMAIL)){
                $erreur="L'adresse mail n'est pas correcte";
            }
            // Cas nominal(quand tout se passe bien)
            else{
                echo("<p>Merci ".$pseudo.", votre message a bien été enregistré ! Une confirmation vous a été envoyée à l'adresse ".$mail."</p>");
                $succes=
            }
        }
        if (isset($erreur)){
            echo("<p class=\"erreur\">".$erreur."</p>");
        }
        if(!isset($succes)){

        }
    ?>
    <form method="post" action="forum.php"></form>
    <div>
        <label for="pseudo">Pseudo<span>*</span></label>
        <input type="text" name="pseudo" required id="pseudo" value="
        <?php echo($pseudo); ?>">
    </div>
    <div>
        <label for="mail">E-mail<span>*</span></label>
        <input type="email" name="mail" required id="mail" value="
        <?php echo($mail); ?>">
    </div>
    <div>
        <label for="message">Message<span>*</span></label>
        <textarea type="text" name="message" required id="message" rows="10" cols="50"></textarea>
        
    </div>
    <div>
        <input type="submit" value="Envoyer le message">
    </div>
</body>
</html>