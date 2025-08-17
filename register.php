<?php
// register.php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Session.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$session = new Session();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirmPassword) {
        $message = 'Les mots de passe ne correspondent pas';
        $messageType = 'error';
    } else {
        $result = $user->register($nom, $prenom, $email, $password);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        if ($result['success']) {
            header('Location: login.php?message=Inscription réussie, vous pouvez maintenant vous connecter');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"] { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .login-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Inscription</h2>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        </div>
        
        <button type="submit">S'inscrire</button>
    </form>
    
    <div class="login-link">
        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>

