<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dist/css/main.css">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>
    <form action="index.php" method="post"> 
        <div>
           <label for="mail">E-mail <span>*</span></label> 
           <input type="email" name="mail" id="mail" required>
        </div>
        <div>
            <label for="password">Mot de passe <span>*</span></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <input type="submit" value="Se connecter">
            <button class="button button--primary">Button</button>

        </div>
    </form>
    <p>
        <a href="formulaire2.php">S'inscrire</a>
    </p>
</body>
</html>