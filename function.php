<?php
include "connexionbdd.php";

////////////////////////////////// Fonction pour récupérer des valeurs d'une table //////////////////////////////////
function getValuesFromTable($pdo, $tableName, $columns = '*', $conditions = '1')
{
    try {
        // Préparation de la requête SQL
        $sql = "SELECT $columns FROM $tableName WHERE $conditions";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        // Récupération des résultats
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    } catch (PDOException $e) {
        // Affiche une erreur si la requête échoue
        die("Erreur de requête : " . $e->getMessage());
    }
}

////////////////////////////////// Fonction pour insérer des données dans la table utilisateur //////////////////////////////////
function insertUtilisateur($pdo, $prenom, $nom, $email, $photo)
{
    try {
        // Préparation de la requête SQL
        $sql = "INSERT INTO utilisateur (prenom_utilisateur, nom_utilisateur, email_utilisateur, photo_utilisateur) 
                VALUES (:prenom, :nom, :email, :photo)";
        $stmt = $pdo->prepare($sql);

        // Liaison des paramètres
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':photo', $photo);

        // Exécution de la requête
        $stmt->execute();
        echo "Utilisateur inséré avec succès.";
    } catch (PDOException $e) {
        // Affiche une erreur si l'insertion échoue
        die("Erreur d'insertion : " . $e->getMessage());
    }
}

////////////////////////////////// Fonction pour supprimer un compte utilisateur //////////////////////////////////
function deleteUserByEmail($pdo, $email)
{
    try {
        // Préparation de la requête SQL
        $sql = "DELETE FROM utilisateur WHERE email_utilisateur = :email";
        $stmt = $pdo->prepare($sql);

        // Liaison du paramètre
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Exécution de la requête
        $stmt->execute();
        echo "Utilisateur supprimé avec succès.";

        session_start();
        $email = $_SESSION['email_utilisateur'];
    } catch (PDOException $e) {
        // Affiche une erreur si la requête échoue
        die("Erreur de requête : " . $e->getMessage());
    }
}

////////////////////////////////// Fonction pour générer une string aléatoire //////////////////////////////////
function generateRandomString()
{
    $length = 48;
    $randomString = "";
    $chars = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    for ($i = 0; $i < $length; $i++) {
        shuffle($chars);
        $randomString .= $chars[0];
    }
    return $randomString;
}

function cropImage($url){
    $width = getimagesize($url)[0];
    $height = getimagesize($url)[1];
    
    if ($width <= $height) {
        $size = $width;
    
    } elseif ($width > $height) {
        $size = $height;
    }
    
    return $size;
}
