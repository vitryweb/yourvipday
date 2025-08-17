<?php
// login.php
session_start();
require_once 'config/database.php';
require_once 'classes/User.php';
require_once 'classes/Session.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$session = new Session();

// Rediriger si déjà connecté
if ($session->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$message = $_GET['message'] ?? '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $user->login($email, $password);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        $session->login($result['user']);
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="password"] { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;
        }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .register-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Connexion</h2>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Se connecter</button>
    </form>
    
    <div class="register-link">
        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
    </div>
</body>
</html>
