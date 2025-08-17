<?php
// classes/User.php
class User {
    private PDO $conn;
    
    public function __construct(PDO $database) {
        $this->conn = $database;
    }
    
    public function register(string $nom, string $prenom, string $email, string $password, string $photo = 'default.jpg', int $budget = 0): array {
        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Tous les champs sont obligatoires'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }
        
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'];
        }
        
        // Vérifier si l'email existe déjà
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Un compte avec cet email existe déjà'];
        }
        
        try {
            $query = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, date_inscription, photo, budget, cookie, points_parrainage) 
                     VALUES (:nom, :prenom, :email, :password, :date_inscription, :photo, :budget, :cookie, :points_parrainage)";
            
            $stmt = $this->conn->prepare($query);
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $dateInscription = date('Y-m-d');
            
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':date_inscription', $dateInscription);
            $stmt->bindParam(':photo', $photo);
            $stmt->bindParam(':budget', $budget);
            $stmt->bindValue(':cookie', false, PDO::PARAM_BOOL);
            $stmt->bindValue(':points_parrainage', 0);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Inscription réussie', 'user_id' => $this->conn->lastInsertId()];
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de l'inscription : " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
        }
        
        return ['success' => false, 'message' => 'Erreur inconnue'];
    }
    
    public function login(string $email, string $password): array {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email et mot de passe requis'];
        }
        
        try {
            $query = "SELECT id, nom, prenom, email, mot_de_passe, photo, budget, points_parrainage 
                     FROM Utilisateurs WHERE email = :email LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch();
                
                if (password_verify($password, $user['mot_de_passe'])) {
                    // Supprimer le mot de passe des données retournées
                    unset($user['mot_de_passe']);
                    
                    return [
                        'success' => true, 
                        'message' => 'Connexion réussie',
                        'user' => $user
                    ];
                }
            }
            
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la connexion : " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la connexion'];
        }
    }
    
    public function getUserById(int $userId): ?array {
        try {
            $query = "SELECT id, nom, prenom, email, photo, budget, date_inscription, points_parrainage 
                     FROM Utilisateurs WHERE id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch() ?: null;
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
            return null;
        }
    }
    
    private function emailExists(string $email): bool {
        try {
            $query = "SELECT id FROM Utilisateurs WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'email : " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): array {
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères'];
        }
        
        try {
            // Vérifier le mot de passe actuel
            $query = "SELECT mot_de_passe FROM Utilisateurs WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch();
            if (!$user || !password_verify($currentPassword, $user['mot_de_passe'])) {
                return ['success' => false, 'message' => 'Mot de passe actuel incorrect'];
            }
            
            // Mettre à jour le mot de passe
            $updateQuery = "UPDATE Utilisateurs SET mot_de_passe = :password WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            if ($updateStmt->execute()) {
                return ['success' => true, 'message' => 'Mot de passe mis à jour avec succès'];
            }
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du mot de passe : " . $e->getMessage());
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour du mot de passe'];
    }
}
