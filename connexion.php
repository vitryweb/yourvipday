<?php
$servername = $_ENV('DB_SERVER');
$username = $_ENV('DB_USERNAME');
$password = $_ENV('DB_PASSWORD');
$dbname = $_ENV('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$conn->close();
?>