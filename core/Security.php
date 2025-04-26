<?php
/**
 * Classe Security: Gère la sécurité de l'application
 */
class Security {
    /**
     * Génère un jeton CSRF et le stocke en session
     * @return string Le jeton CSRF sous forme de champ de formulaire caché
     */
    public static function generateCSRFToken() {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Générer un jeton aléatoire s'il n'existe pas
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Retourner le jeton sous forme de champ caché
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }
    
    /**
     * Vérifie la validité du jeton CSRF
     * @return bool
     */
    public static function checkCSRF() {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si le jeton existe en session et dans la requête
        if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
            self::handleSecurityBreach('Jeton CSRF manquant');
            return false;
        }
        
        // Vérifier si les jetons correspondent
        if ($_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            self::handleSecurityBreach('Jeton CSRF invalide');
            return false;
        }
        
        // Régénérer le jeton pour une utilisation ultérieure
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        return true;
    }
    
    /**
     * Hache un mot de passe
     * @param string $password Le mot de passe à hacher
     * @return string Le mot de passe haché
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Vérifie si un mot de passe correspond à un hash
     * @param string $password Le mot de passe à vérifier
     * @param string $hash Le hash stocké
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Connecte un utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @return bool
     */
    public static function login($username, $password) {
        // Rechercher l'utilisateur dans la base de données
        $user = (new User())->where('username', $username)->fetchOne();
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && self::verifyPassword($password, $user['password'])) {
            // Démarrer la session si ce n'est pas déjà fait
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Stocker les informations de l'utilisateur en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Régénérer l'ID de session pour éviter les attaques de fixation de session
            session_regenerate_id(true);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Vérifie si un utilisateur est connecté
     * @return bool
     */
    public static function isAuthenticated() {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Déconnecte l'utilisateur actuel
     * @return void
     */
    public static function logout() {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Supprimer toutes les variables de session
        $_SESSION = [];
        
        // Si un cookie de session est utilisé, supprimer le cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Détruire la session
        session_destroy();
    }
    
    /**
     * Nettoie les données d'entrée
     * @param mixed $data Les données à nettoyer
     * @return mixed
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Gère une violation de sécurité
     * @param string $message Message d'erreur
     * @return void
     */
    private static function handleSecurityBreach($message) {
        // Enregistrer la tentative dans les logs
        error_log('Violation de sécurité: ' . $message);
        
        // Rediriger vers une page d'erreur
        header('Location: /error/forbidden');
        exit;
    }
}