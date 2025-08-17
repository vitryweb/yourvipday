<?php
require_once "function.php";

// Configuration des paramètres OAuth
$client_id = '219944284667-3p66lh8opkcdunjh5qps03cb427ggcid.apps.googleusercontent.com';
$client_secret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirect_uri = 'http://localhost/yourvipday/callback.php';
$code = htmlspecialchars($_GET['code']); // Le code d'autorisation retourné par Google

// Échange du code d'autorisation contre un jeton d'accès
$token_url = "https://oauth2.googleapis.com/token";
$data = [
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data),
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);
$token_info = json_decode($response, true);
$access_token = $token_info['access_token'];

// Utilisation du jeton d'accès pour obtenir les informations de l'utilisateur
$userinfo_url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=$access_token";
$userinfo = file_get_contents($userinfo_url);
$userinfo = json_decode($userinfo, true);

// On assigne les informations récupérées de l'utilisateur à de nouvelles variables
$prenom = htmlspecialchars($userinfo['given_name']);
$nom = htmlspecialchars($userinfo['family_name']);
$email = htmlspecialchars($userinfo['email']);
$photo = htmlspecialchars($userinfo['picture']);


$tableName = 'utilisateur';
$columns = 'id_utilisateur, nom_utilisateur, prenom_utilisateur, email_utilisateur, photo_utilisateur';
$conditions = 'email_utilisateur LIKE "' . $email . '"';
$values = getValuesFromTable($pdo, $tableName, $columns, $conditions);

// On vérifie si une valeur est assignée à $values. Si aucune valeur n'est assignée, cela veut dire que la variable $conditions n'est pas remplie.
if (isset($values['0'])) {
    // L'utilisateur existe déjà en base de données, on affiche ses informations
}
// Si elle n'existe pas, on enregistre le nouvel utilisateur en base de données et on met à jour la variable $values
else {
    insertUtilisateur($pdo, $prenom, $nom, $email, $photo);
    $values = getValuesFromTable($pdo, $tableName, $columns, $conditions);
}

// On assigne les données enregistrées en base de données à des variables de session
foreach ($values as $row) {
    session_start();
    $_SESSION['id_utilisateur'] = htmlspecialchars($row['id_utilisateur']);
    $_SESSION['prenom_utilisateur'] = htmlspecialchars($row['prenom_utilisateur']);
    $_SESSION['nom_utilisateur'] = htmlspecialchars($row['nom_utilisateur']);
    $_SESSION['email_utilisateur'] = htmlspecialchars($row['email_utilisateur']);
    $_SESSION['photo_utilisateur'] = htmlspecialchars($row['photo_utilisateur']);
}

// echo $_SESSION['id_utilisateur'];
// echo $_SESSION['prenom_utilisateur'];
// echo $_SESSION['nom_utilisateur'];
// echo $_SESSION['email_utilisateur'];
// echo $_SESSION['photo_utilisateur'];

header('Location:'. "index.php");// /!\ Charger l'URL automatiquement en fonction de la page visitée précédemment sur le site E-Klips (utiliser un $_POST ou $_GET)