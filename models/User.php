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
     * Récupère un utilisateur avec son rôle
     * @param int $id ID de l'utilisateur
     * @return array|false
     */
    public function findWithRole($id) {
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Récupère tous les utilisateurs avec leurs rôles
     * @return array
     */
    public function getAllWithRoles() {
        $sql = "SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id";
        
        return $this->db->fetchAll($sql);
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
        
        // Attribuer le rôle par défaut si non spécifié
        if (!isset($data['role_id'])) {
            $data['role_id'] = 3; // 3 = user (rôle par défaut)
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
    
    /**
     * Sauvegarde un token "se souvenir" pour un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $token Token haché
     * @param string $expiry Date d'expiration (format Y-m-d H:i:s)
     * @return bool Succès ou échec
     */
    public function saveToken($userId, $token, $expiry) {
        // Supprimer les anciens tokens pour cet utilisateur
        $sql = "DELETE FROM user_tokens WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        
        // Insérer le nouveau token
        $sql = "INSERT INTO user_tokens (user_id, token, expiry) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$userId, $token, $expiry])->rowCount() > 0;
    }
    
    /**
     * Vérifie la validité d'un token "se souvenir"
     * @param int $userId ID de l'utilisateur
     * @param string $token Token haché
     * @return bool
     */
    public function verifyToken($userId, $token) {
        $sql = "SELECT COUNT(*) FROM user_tokens 
                WHERE user_id = ? AND token = ? AND expiry > NOW()";
        
        return (int) $this->db->fetchValue($sql, [$userId, $token]) > 0;
    }
    
    /**
     * Supprime tous les tokens "se souvenir" pour un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function removeAllTokens($userId) {
        $sql = "DELETE FROM user_tokens WHERE user_id = ?";
        return $this->db->query($sql, [$userId])->rowCount() > 0;
    }
    
    /**
     * Vérifie si un utilisateur a un rôle spécifique
     * @param int $userId ID de l'utilisateur
     * @param string $roleName Nom du rôle
     * @return bool
     */
    public function hasRole($userId, $roleName) {
        $sql = "SELECT COUNT(*) FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = ? AND r.name = ?";
        
        return (int) $this->db->fetchValue($sql, [$userId, $roleName]) > 0;
    }
    
    /**
     * Attribue un rôle à un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param int $roleId ID du rôle
     * @return bool
     */
    public function assignRole($userId, $roleId) {
        return $this->update($userId, ['role_id' => $roleId]);
    }
}