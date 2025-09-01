<!DOCTYPE html>
<html lang="FR-fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dist/css/main.css">
    <title>Créer un compte</title>
</head>
<body>
    <form action="confirm_inscription.php" method="post">
        <h1>Créer un compte</h1>
        <p>Faites de votre prochain grand jour une expérience inoubliable.</p>
        <div>
            <label for="nom">Nom <span>*</span></label> 
            <input type="text" name="nom" id="nom" required>
        </div>
        <div>
            <label for="prenom">Prénom <span>*</span></label> 
            <input type="text" name="prenom" id="prenom" required>
        </div>
        <div>
            <label for="mail">E-mail <span>*</span></label> 
           <input type="email" name="mail" id="mail" required>
        </div>
        <div>
            <label for="password">Mot de passe <span>*</span></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="password">Confirmation du Mot de passe <span>*</span></label>
            <input type="password" name="password" id="password" required>
        </div>

        <div>
            <label for="date">Indiquez la date de l’événement à célébrer pour que nous puissions personnaliser votre expérience.<span>*</span></label> 
            <input type="text" name="date" id="date" required disabled value="<?php echo(date("d/m/Y")); ?>">
        </div>
        <div>
            <label for="residence">Ville de résidence <span>*</span></label> 
           <input type="text" name="residence" id="residence" required list="villes">
            <datalist id="villes">
                <option value="Paris">Paris</option>
                <option value="Lyon">Lyon</option>
                <option value="Toulouse">Toulouse</option>
                
            </datalist>
        </div>
        <div>
            <label for="modules">Modules retenus <span>*</span></label> 
            <select multiple size="6" name="modules" id="modules" required>
                <option value="" desabled>Sélectionner</option>
                <option value="1">HTML</option>
                <option value="2">CSS</option>
                <option value="3">Javascript</option>
                <option value="4">PHP</option>
                <option value="5" selected>Gestion de projet</option>
            </select>
        </div>
        <div>
            <label for="loisir">Loisir</label> 
           <input type="checkbox" name="loisir" value="1">Cinéma
           <input type="checkbox" name="loisir" value="2">Musique
           <input type="checkbox" name="loisir" value="3">Web
        </div>
        <div>
            <label for="password">Mot de passe <span>*</span></label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <input type="reset" value="Effacer le formulaire">
            <input type="submit" value="Finaliser">
            <button class="button button--primary">Button</button>

        </div>
    </form>
</body>
</html>