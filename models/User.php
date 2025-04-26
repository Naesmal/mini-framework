<?php
/**
 * User: Modèle pour la gestion des utilisateurs
 */
class User extends Model {
    /**
     * Nom de la table associée (optionnel, par défaut = nom de la classe en minuscules + 's')
     * @var string
     */
    protected $table = 'users';
    
    /**
     * Récupère un utilisateur par son nom d'utilisateur
     * @param string $username Le nom d'utilisateur
     * @return array|false
     */
    public function getByUsername($username) {
        return $this->where('username', $username)->fetchOne();
    }
    
    /**
     * Récupère un utilisateur par son email
     * @param string $email L'email de l'utilisateur
     * @return array|false
     */
    public function getByEmail($email) {
        return $this->where('email', $email)->fetchOne();
    }
    
    /**
     * Vérifie si un nom d'utilisateur existe déjà
     * @param string $username Le nom d'utilisateur
     * @return bool
     */
    public function usernameExists($username) {
        return $this->where('username', $username)->count() > 0;
    }
    
    /**
     * Vérifie si un email existe déjà
     * @param string $email L'email
     * @return bool
     */
    public function emailExists($email) {
        return $this->where('email', $email)->count() > 0;
    }
    
    /**
     * Enregistre un nouvel utilisateur avec hashage du mot de passe
     * @param array $data Les données de l'utilisateur
     * @return int|false L'ID du nouvel utilisateur ou false en cas d'échec
     */
    public function register($data) {
        // S'assurer que le mot de passe est haché
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }
        
        return $this->create($data);
    }
    
    /**
     * Récupère tous les utilisateurs actifs
     * @return array
     */
    public function getActiveUsers() {
        return $this->where('status', 'active')->fetchAll();
    }
}