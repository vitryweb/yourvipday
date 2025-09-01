<?php
require_once '../src/config/db.php';

// Démarrer la session si pas déjà fait
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fonction pour inscrire un utilisateur
 */
function inscrireUtilisateur($nom, $prenom, $email, $mot_de_passe, $budget = 0, $photo = 'default-avatar.jpg') {
    $pdo = getDbConnection();
    
    try {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
        }
        
        // Hasher le mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        // Insérer l'utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, date_inscription, photo, budget, cookie, points_parrainage) 
            VALUES (?, ?, ?, ?, CURDATE(), ?, ?, 1, 0)
        ");
        $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash, $photo, $budget]);
        
        $user_id = $pdo->lastInsertId();
        
        return ['success' => true, 'user_id' => $user_id, 'message' => 'Inscription réussie'];
        
    } catch (PDOException $e) {
        error_log("Erreur inscription : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
}

/**
 * Fonction pour connecter un utilisateur
 */
function connecterUtilisateur($email, $mot_de_passe) {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, mot_de_passe, photo, budget FROM Utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch();
        
        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_nom'] = $utilisateur['nom'];
            $_SESSION['user_prenom'] = $utilisateur['prenom'];
            $_SESSION['user_email'] = $utilisateur['email'];
            $_SESSION['user_photo'] = $utilisateur['photo'];
            $_SESSION['user_budget'] = $utilisateur['budget'];
            
            return ['success' => true, 'message' => 'Connexion réussie'];
        } else {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }
        
    } catch (PDOException $e) {
        error_log("Erreur connexion : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la connexion'];
    }
}

/**
 * Fonction pour connecter avec un réseau social (Google, Instagram, TikTok)
 */
function connecterAvecReseauSocial($type_reseau, $social_id, $nom, $prenom, $email, $photo = '') {
    $pdo = getDbConnection();
    
    try {
        // Vérifier si l'utilisateur existe déjà avec cet ID social
        $column_social = $type_reseau . '_id';
        $stmt = $pdo->prepare("SELECT id, nom, prenom, email, photo, budget FROM Utilisateurs WHERE $column_social = ?");
        $stmt->execute([$social_id]);
        $utilisateur = $stmt->fetch();
        
        if ($utilisateur) {
            // Utilisateur existant
            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_nom'] = $utilisateur['nom'];
            $_SESSION['user_prenom'] = $utilisateur['prenom'];
            $_SESSION['user_email'] = $utilisateur['email'];
            $_SESSION['user_photo'] = $utilisateur['photo'];
            $_SESSION['user_budget'] = $utilisateur['budget'];
            
            return ['success' => true, 'message' => 'Connexion réussie', 'new_user' => false];
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM Utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Un compte existe déjà avec cet email'];
            }
            
            // Créer un nouvel utilisateur
            $stmt = $pdo->prepare("
                INSERT INTO Utilisateurs (nom, prenom, email, date_inscription, photo, budget, cookie, points_parrainage, $column_social) 
                VALUES (?, ?, ?, CURDATE(), ?, 0, 1, 0, ?)
            ");
            $stmt->execute([$nom, $prenom, $email, $photo ?: 'default-avatar.jpg', $social_id]);
            
            $user_id = $pdo->lastInsertId();
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_photo'] = $photo ?: 'default-avatar.jpg';
            $_SESSION['user_budget'] = 0;
            
            return ['success' => true, 'message' => 'Inscription et connexion réussies', 'new_user' => true];
        }
        
    } catch (PDOException $e) {
        error_log("Erreur connexion sociale : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la connexion'];
    }
}

/**
 * Fonction pour vérifier si un utilisateur est connecté
 */
function estConnecte() {
    return isset($_SESSION['user_id']);
}

/**
 * Fonction pour déconnecter un utilisateur
 */
function deconnecterUtilisateur() {
    session_destroy();
    return ['success' => true, 'message' => 'Déconnexion réussie'];
}

/**
 * Fonction pour obtenir les informations d'un utilisateur
 */
function obtenirProfilUtilisateur($user_id) {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, nom, prenom, email, photo, budget, date_inscription, points_parrainage 
            FROM Utilisateurs 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        error_log("Erreur récupération profil : " . $e->getMessage());
        return false;
    }
}

/**
 * Fonction pour mettre à jour le profil utilisateur
 */
function mettreAJourProfil($user_id, $nom, $prenom, $email, $budget = null, $photo = null) {
    $pdo = getDbConnection();
    
    try {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM Utilisateurs WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Cet email est déjà utilisé par un autre compte'];
        }
        
        // Construire la requête de mise à jour
        $fields = ['nom = ?', 'prenom = ?', 'email = ?'];
        $params = [$nom, $prenom, $email];
        
        if ($budget !== null) {
            $fields[] = 'budget = ?';
            $params[] = $budget;
        }
        
        if ($photo !== null) {
            $fields[] = 'photo = ?';
            $params[] = $photo;
        }
        
        $params[] = $user_id;
        
        $sql = "UPDATE Utilisateurs SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Mettre à jour la session
        $_SESSION['user_nom'] = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_email'] = $email;
        if ($budget !== null) $_SESSION['user_budget'] = $budget;
        if ($photo !== null) $_SESSION['user_photo'] = $photo;
        
        return ['success' => true, 'message' => 'Profil mis à jour avec succès'];
        
    } catch (PDOException $e) {
        error_log("Erreur mise à jour profil : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
}

/**
 * Fonction pour supprimer un compte utilisateur (RGPD)
 */
function supprimerCompteUtilisateur($user_id) {
    $pdo = getDbConnection();
    
    try {
        $pdo->beginTransaction();
        
        // Supprimer toutes les données liées à l'utilisateur
        $tables_to_clean = [
            ['table' => 'avis', 'column' => 'id_Utilisateurs'],
            ['table' => 'partage_lien', 'column' => 'id_Utilisateurs'],
            ['table' => 'aimer', 'column' => 'id'],
            ['table' => 'parrainer', 'column' => 'id'],
            ['table' => 'parrainer', 'column' => 'id_Utilisateurs'],
            ['table' => 'enregistrer', 'column' => 'id_Utilisateurs'],
            ['table' => 'reserver', 'column' => 'id_Utilisateurs'],
            ['table' => 'ajouterpanier', 'column' => 'id_Utilisateurs'],
            ['table' => 'occasions_utilisateur', 'column' => 'id_Utilisateurs'],
            ['table' => 'abonnement_premium', 'column' => 'id_Utilisateurs']
        ];
        
        foreach ($tables_to_clean as $table_info) {
            try {
                $stmt = $pdo->prepare("DELETE FROM `" . $table_info['table'] . "` WHERE `" . $table_info['column'] . "` = ?");
                $stmt->execute([$user_id]);
                $deleted_rows = $stmt->rowCount();
                error_log("Suppression compte - Table " . $table_info['table'] . ": $deleted_rows ligne(s) supprimée(s)");
            } catch (PDOException $e) {
                error_log("Erreur suppression table " . $table_info['table'] . ": " . $e->getMessage());
                // Continue avec les autres tables même si une échoue
            }
        }
        
        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM `Utilisateurs` WHERE `id` = ?");
        $stmt->execute([$user_id]);
        $user_deleted = $stmt->rowCount();
        
        if ($user_deleted === 0) {
            throw new Exception("Aucun utilisateur trouvé avec l'ID $user_id");
        }
        
        error_log("Suppression compte - Utilisateur principal: $user_deleted ligne(s) supprimée(s)");
        
        $pdo->commit();
        
        // Détruire la session
        session_destroy();
        
        return ['success' => true, 'message' => 'Compte supprimé avec succès'];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erreur suppression compte : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la suppression du compte'];
    }
}

/**
 * Fonction pour obtenir les occasions d'un utilisateur
 */
function obtenirOccasionsUtilisateur($user_id) {
    $pdo = getDbConnection();
    
    try {
        $stmt = $pdo->prepare("
            SELECT ou.*, o.nom as nom_occasion, o.description 
            FROM Occasions_Utilisateur ou
            JOIN Occasions o ON ou.id_Occasions = o.id
            WHERE ou.id_Utilisateurs = ?
            ORDER BY ou.date_evenement
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Erreur récupération occasions : " . $e->getMessage());
        return [];
    }
}
?>
