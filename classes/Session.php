<?php
// classes/Session.php
class Session {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function login(array $userData): void {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_nom'] = $userData['nom'];
        $_SESSION['user_prenom'] = $userData['prenom'];
        $_SESSION['logged_in'] = true;
    }
    
    public function logout(): void {
        session_unset();
        session_destroy();
    }
    
    public function isLoggedIn(): bool {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getUserData(): array {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'nom' => $_SESSION['user_nom'] ?? null,
            'prenom' => $_SESSION['user_prenom'] ?? null,
        ];
    }
    
    public function requireLogin(string $redirectUrl = 'login.php'): void {
        if (!$this->isLoggedIn()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
}
