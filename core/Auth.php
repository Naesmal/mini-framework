<?php
/**
 * Auth: Classe de gestion de l'authentification et des rôles
 */
class Auth {
    /**
     * Modèle d'utilisateur
     * @var User
     */
    private static $userModel;
    
    /**
     * Utilisateur actuellement connecté
     * @var array|null
     */
    private static $currentUser = null;
    
    /**
     * Initialise le modèle d'utilisateur
     */
    private static function initUserModel() {
        if (self::$userModel === null) {
            self::$userModel = new User();
        }
    }
    
    /**
     * Connecte un utilisateur
     * @param string $username Nom d'utilisateur ou email
     * @param string $password Mot de passe
     * @param bool $remember Se souvenir de l'utilisateur (cookie)
     * @return bool
     */
    public static function login($username, $password, $remember = false) {
        self::initUserModel();
        
        // Rechercher l'utilisateur par nom d'utilisateur ou email
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        if ($isEmail) {
            $user = self::$userModel->getByEmail($username);
        } else {
            $user = self::$userModel->getByUsername($username);
        }
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && Security::verifyPassword($password, $user['password'])) {
            // Vérifier si l'utilisateur est actif
            if (isset($user['status']) && $user['status'] !== 'active') {
                return false;
            }
            
            // Stocker les informations de l'utilisateur en session
            self::setUserSession($user);
            
            // Si "se souvenir", créer un cookie de connexion
            if ($remember) {
                self::createRememberToken($user['id']);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Déconnecte l'utilisateur actuel
     */
    public static function logout() {
        Session::remove('user_id');
        Session::remove('user_username');
        Session::remove('user_email');
        Session::remove('user_role');
        
        // Supprimer le cookie "se souvenir"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Vider la variable de l'utilisateur actuel
        self::$currentUser = null;
    }
    
    /**
     * Vérifie si un utilisateur est connecté
     * @return bool
     */
    public static function isAuthenticated() {
        return Session::has('user_id') || self::checkRememberToken();
    }
    
    /**
     * Crée un token "se souvenir"
     * @param int $userId ID de l'utilisateur
     */
    private static function createRememberToken($userId) {
        self::initUserModel();
        
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60); // 30 jours
        
        // Stocker le token en base de données
        $hashedToken = hash('sha256', $token);
        
        // Il faudrait créer une table tokens ou ajouter ces champs à la table users
        // Pour cet exemple, nous supposons qu'une méthode saveToken existe
        // self::$userModel->saveToken($userId, $hashedToken, $expiry);
        
        // Créer le cookie
        setcookie(
            'remember_token',
            $userId . ':' . $token,
            time() + 30 * 24 * 60 * 60, // 30 jours
            '/',
            '',
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            true // HttpOnly
        );
    }
    
    /**
     * Vérifie le token "se souvenir"
     * @return bool
     */
    private static function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        self::initUserModel();
        
        // Extraire l'ID utilisateur et le token
        list($userId, $token) = explode(':', $_COOKIE['remember_token'], 2);
        $userId = (int) $userId;
        
        if (!$userId || !$token) {
            return false;
        }
        
        // Hacher le token
        $hashedToken = hash('sha256', $token);
        
        // Vérifier le token en base de données
        // Pour cet exemple, nous supposons qu'une méthode verifyToken existe
        // $isValid = self::$userModel->verifyToken($userId, $hashedToken);
        $isValid = false; // À remplacer par la vérification réelle
        
        if ($isValid) {
            // Récupérer l'utilisateur et le connecter
            $user = self::$userModel->find($userId);
            if ($user) {
                self::setUserSession($user);
                return true;
            }
        }
        
        // Supprimer le cookie si invalide
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Définit les variables de session pour l'utilisateur
     * @param array $user Données de l'utilisateur
     */
    private static function setUserSession($user) {
        Session::set('user_id', $user['id']);
        Session::set('user_username', $user['username']);
        Session::set('user_email', $user['email']);
        
        // Stocker le rôle si disponible
        if (isset($user['role'])) {
            Session::set('user_role', $user['role']);
        } else {
            Session::set('user_role', 'user'); // Rôle par défaut
        }
        
        // Enregistrer la dernière activité
        Session::set('last_activity', time());
    }
    
    /**
     * Récupère l'utilisateur actuellement connecté
     * @return array|null Données de l'utilisateur ou null si non connecté
     */
    public static function currentUser() {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }
        
        if (!self::isAuthenticated()) {
            return null;
        }
        
        self::initUserModel();
        
        // Récupérer l'utilisateur complet depuis la base de données
        $userId = Session::get('user_id');
        
        if ($userId) {
            self::$currentUser = self::$userModel->find($userId);
            return self::$currentUser;
        }
        
        return null;
    }
    
    /**
     * Vérifie si l'utilisateur connecté a un rôle spécifique
     * @param string|array $roles Rôle(s) à vérifier
     * @return bool
     */
    public static function hasRole($roles) {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        $userRole = Session::get('user_role', 'user');
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }
    
    /**
     * Vérifie si l'utilisateur est autorisé à accéder à une ressource
     * @param string $permission La permission requise
     * @return bool
     */
    public static function can($permission) {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        $userRole = Session::get('user_role', 'user');
        
        // Définir les permissions pour chaque rôle
        $rolePermissions = [
            'admin' => ['users.view', 'users.create', 'users.edit', 'users.delete', 'admin.access'],
            'editor' => ['users.view', 'users.edit'],
            'user' => ['users.view']
        ];
        
        // Si le rôle existe et a des permissions définies
        if (isset($rolePermissions[$userRole])) {
            return in_array($permission, $rolePermissions[$userRole]);
        }
        
        return false;
    }
    
    /**
     * Restreint l'accès à une page en fonction du rôle ou de la permission
     * @param string|array $rolesOrPermission Rôle(s) ou permission requise
     * @param string $redirectUrl URL de redirection en cas d'accès non autorisé
     */
    public static function restrict($rolesOrPermission, $redirectUrl = '/login') {
        // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
        if (!self::isAuthenticated()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        // Vérifier si c'est un rôle ou une permission
        $isPermission = strpos($rolesOrPermission, '.') !== false;
        
        // Vérifier l'accès
        $hasAccess = $isPermission 
            ? self::can($rolesOrPermission)
            : self::hasRole($rolesOrPermission);
        
        // Si l'accès est refusé, rediriger vers la page d'erreur 403
        if (!$hasAccess) {
            header('Location: /error/forbidden');
            exit;
        }
    }
}