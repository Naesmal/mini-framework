<?php
/**
 * Role: Modèle pour la gestion des rôles
 */
class Role extends Model {
    /**
     * Nom de la table associée
     * @var string
     */
    protected $table = 'roles';
    
    /**
     * Récupère toutes les permissions associées à un rôle
     * @param int $roleId ID du rôle
     * @return array
     */
    public function getPermissions($roleId) {
        $sql = "SELECT p.* FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?";
        
        return $this->db->fetchAll($sql, [$roleId]);
    }
    
    /**
     * Ajoute une permission à un rôle
     * @param int $roleId ID du rôle
     * @param int $permissionId ID de la permission
     * @return bool
     */
    public function addPermission($roleId, $permissionId) {
        // Vérifier si la relation existe déjà
        $sql = "SELECT COUNT(*) FROM role_permissions 
                WHERE role_id = ? AND permission_id = ?";
        
        $exists = (int) $this->db->fetchValue($sql, [$roleId, $permissionId]) > 0;
        
        if (!$exists) {
            // Insérer la nouvelle relation
            $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
            return $this->db->query($sql, [$roleId, $permissionId])->rowCount() > 0;
        }
        
        return true;
    }
    
    /**
     * Supprime une permission d'un rôle
     * @param int $roleId ID du rôle
     * @param int $permissionId ID de la permission
     * @return bool
     */
    public function removePermission($roleId, $permissionId) {
        $sql = "DELETE FROM role_permissions 
                WHERE role_id = ? AND permission_id = ?";
        
        return $this->db->query($sql, [$roleId, $permissionId])->rowCount() > 0;
    }
    
    /**
     * Vérifie si un rôle a une permission spécifique
     * @param int $roleId ID du rôle
     * @param string $permissionName Nom de la permission
     * @return bool
     */
    public function hasPermission($roleId, $permissionName) {
        $sql = "SELECT COUNT(*) FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ? AND p.name = ?";
        
        return (int) $this->db->fetchValue($sql, [$roleId, $permissionName]) > 0;
    }
    
    /**
     * Récupère un rôle par son nom
     * @param string $name Nom du rôle
     * @return array|false
     */
    public function getByName($name) {
        return $this->where('name', $name)->fetchOne();
    }
}