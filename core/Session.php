<?php
/**
 * Session: Classe de gestion des sessions utilisateur
 */
class Session {
    /**
     * Démarre la session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuration des cookies de session sécurisés
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            
            // Si HTTPS est activé, marquer le cookie comme sécurisé
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            // Démarrer la session
            session_start();
            
            // Régénérer l'id de session périodiquement (toutes les 30 minutes)
            if (!isset($_SESSION['last_regeneration']) || 
                time() - $_SESSION['last_regeneration'] > 1800) {
                self::regenerateId();
            }
        }
    }
    
    /**
     * Régénère l'ID de session
     */
    public static function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Définit une variable de session
     * @param string $key La clé
     * @param mixed $value La valeur
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Récupère une variable de session
     * @param string $key La clé
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Vérifie si une variable de session existe
     * @param string $key La clé
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Supprime une variable de session
     * @param string $key La clé
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Définit une variable de session flash
     * @param string $key La clé
     * @param mixed $value La valeur
     */
    public static function setFlash($key, $value) {
        $_SESSION['flash_messages'][$key] = $value;
    }
    
    /**
     * Récupère une variable de session flash et la supprime
     * @param string $key La clé
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function getFlash($key, $default = null) {
        $value = $_SESSION['flash_messages'][$key] ?? $default;
        self::removeFlash($key);
        return $value;
    }
    
    /**
     * Supprime une variable de session flash
     * @param string $key La clé
     */
    public static function removeFlash($key) {
        if (isset($_SESSION['flash_messages'][$key])) {
            unset($_SESSION['flash_messages'][$key]);
        }
    }
    
    /**
     * Détruit la session
     */
    public static function destroy() {
        $_SESSION = [];
        
        // Détruire le cookie de session
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
}