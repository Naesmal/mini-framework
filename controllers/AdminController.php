<?php
/**
 * AdminController: Contrôleur pour la gestion du panneau d'administration
 */
class AdminController extends Controller {
    /**
     * Constructeur
     */
    public function __construct() {
        // Restreindre l'accès au panneau d'administration
        Auth::restrict('admin', '/login');
    }
    
    /**
     * Dashboard de l'administration
     * @return void
     */
    public function dashboard() {
        // Charger les modèles nécessaires
        $userModel = new User();
        $roleModel = new Role();
        
        // Récupérer les statistiques
        $stats = [
            'users_count' => $userModel->count(),
            'active_users_count' => $userModel->where('status', 'active')->count(),
            'inactive_users_count' => $userModel->where('status', 'inactive')->count(),
            'roles_count' => $roleModel->count()
        ];
        
        // Récupérer les 5 derniers utilisateurs
        $recentUsers = $userModel->orderBy('created_at', 'DESC')->limit(5)->fetchAll();
        
        // Rendre la vue
        $this->render('admin/dashboard', [
            'title' => 'Tableau de bord',
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }
    
    /**
     * Liste des utilisateurs dans l'admin
     * @return void
     */
    public function users() {
        $userModel = new User();
        
        // Récupérer tous les utilisateurs avec leurs rôles
        $users = $userModel->getAllWithRoles();
        
        $this->render('admin/users/index', [
            'title' => 'Gestion des utilisateurs',
            'users' => $users
        ]);
    }
    
    /**
     * Éditer un utilisateur (admin)
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function editUser($id) {
        $userModel = new User();
        $roleModel = new Role();
        
        // Récupérer l'utilisateur avec son rôle
        $user = $userModel->findWithRole($id);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->notFound();
        }
        
        // Récupérer tous les rôles disponibles
        $roles = $roleModel->all();
        
        // Si la requête est de type POST, traiter les données
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData([
                'username', 'email', 'password', 'role_id', 'status'
            ]));
            
            // Si le mot de passe est vide, le supprimer des données
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Security::hashPassword($data['password']);
            }
            
            // Valider les données
            $errors = [];
            
            // Valider le nom d'utilisateur
            if (empty($data['username'])) {
                $errors[] = 'Le nom d\'utilisateur est obligatoire.';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            } elseif ($data['username'] !== $user['username'] && $userModel->usernameExists($data['username'])) {
                $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
            
            // Valider l'email
            if (empty($data['email'])) {
                $errors[] = 'L\'email est obligatoire.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format d\'email invalide.';
            } elseif ($data['email'] !== $user['email'] && $userModel->emailExists($data['email'])) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            
            // Si pas d'erreurs, mettre à jour l'utilisateur
            if (empty($errors)) {
                $success = $userModel->update($id, $data);
                
                if ($success) {
                    // Définir un message flash et rediriger
                    Session::setFlash('success', 'L\'utilisateur a été mis à jour avec succès.');
                    $this->redirect('/admin/users');
                } else {
                    $errors[] = 'Erreur lors de la mise à jour de l\'utilisateur.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('admin/users/edit', [
                'title' => 'Modifier l\'utilisateur',
                'user' => array_merge($user, $data),
                'roles' => $roles,
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire avec les données de l'utilisateur
            $this->render('admin/users/edit', [
                'title' => 'Modifier l\'utilisateur',
                'user' => $user,
                'roles' => $roles
            ]);
        }
    }
    
    /**
     * Créer un nouvel utilisateur (admin)
     * @return void
     */
    public function createUser() {
        $userModel = new User();
        $roleModel = new Role();
        
        // Récupérer tous les rôles disponibles
        $roles = $roleModel->all();
        
        // Si la requête est de type POST, traiter les données
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData([
                'username', 'email', 'password', 'role_id', 'status'
            ]));
            
            // Valider les données
            $errors = [];
            
            // Valider le nom d'utilisateur
            if (empty($data['username'])) {
                $errors[] = 'Le nom d\'utilisateur est obligatoire.';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            } elseif ($userModel->usernameExists($data['username'])) {
                $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
            
            // Valider l'email
            if (empty($data['email'])) {
                $errors[] = 'L\'email est obligatoire.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format d\'email invalide.';
            } elseif ($userModel->emailExists($data['email'])) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            
            // Valider le mot de passe
            if (empty($data['password'])) {
                $errors[] = 'Le mot de passe est obligatoire.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            
            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                $userId = $userModel->register($data);
                
                if ($userId) {
                    // Définir un message flash et rediriger
                    Session::setFlash('success', 'L\'utilisateur a été créé avec succès.');
                    $this->redirect('/admin/users');
                } else {
                    $errors[] = 'Erreur lors de la création de l\'utilisateur.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('admin/users/create', [
                'title' => 'Créer un utilisateur',
                'data' => $data,
                'roles' => $roles,
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire vide
            $this->render('admin/users/create', [
                'title' => 'Créer un utilisateur',
                'roles' => $roles
            ]);
        }
    }
    
    /**
     * Supprimer un utilisateur (admin)
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function deleteUser($id) {
        // Vérifier le jeton CSRF
        Security::checkCSRF();
        
        $userModel = new User();
        
        // Récupérer l'utilisateur
        $user = $userModel->find($id);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->notFound();
        }
        
        // Empêcher la suppression de son propre compte
        if ($user['id'] == Session::get('user_id')) {
            Session::setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/admin/users');
        }
        
        // Supprimer l'utilisateur
        $success = $userModel->delete($id);
        
        if ($success) {
            Session::setFlash('success', 'L\'utilisateur a été supprimé avec succès.');
        } else {
            Session::setFlash('error', 'Erreur lors de la suppression de l\'utilisateur.');
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Gestion des rôles
     * @return void
     */
    public function roles() {
        $roleModel = new Role();
        
        // Récupérer tous les rôles
        $roles = $roleModel->all();
        
        $this->render('admin/roles/index', [
            'title' => 'Gestion des rôles',
            'roles' => $roles
        ]);
    }
    
    /**
     * Éditer un rôle
     * @param int $id ID du rôle
     * @return void
     */
    public function editRole($id) {
        $roleModel = new Role();
        
        // Récupérer le rôle
        $role = $roleModel->find($id);
        
        // Vérifier si le rôle existe
        if (!$role) {
            $this->notFound();
        }
        
        // Récupérer les permissions du rôle
        $rolePermissions = $roleModel->getPermissions($id);
        
        // Récupérer toutes les permissions disponibles
        $db = Database::getInstance();
        $allPermissions = $db->fetchAll("SELECT * FROM permissions");
        
        // Si la requête est de type POST, traiter les données
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData(['name', 'description']));
            $selectedPermissions = $_POST['permissions'] ?? [];
            
            // Valider les données
            $errors = [];
            
            // Valider le nom du rôle
            if (empty($data['name'])) {
                $errors[] = 'Le nom du rôle est obligatoire.';
            }
            
            // Si pas d'erreurs, mettre à jour le rôle
            if (empty($errors)) {
                // Mettre à jour les informations du rôle
                $success = $roleModel->update($id, $data);
                
                if ($success) {
                    // Supprimer toutes les permissions actuelles
                    $db->query("DELETE FROM role_permissions WHERE role_id = ?", [$id]);
                    
                    // Ajouter les nouvelles permissions
                    foreach ($selectedPermissions as $permissionId) {
                        $db->query(
                            "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                            [$id, $permissionId]
                        );
                    }
                    
                    // Définir un message flash et rediriger
                    Session::setFlash('success', 'Le rôle a été mis à jour avec succès.');
                    $this->redirect('/admin/roles');
                } else {
                    $errors[] = 'Erreur lors de la mise à jour du rôle.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('admin/roles/edit', [
                'title' => 'Modifier le rôle',
                'role' => array_merge($role, $data),
                'allPermissions' => $allPermissions,
                'rolePermissions' => $rolePermissions,
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire avec les données du rôle
            $this->render('admin/roles/edit', [
                'title' => 'Modifier le rôle',
                'role' => $role,
                'allPermissions' => $allPermissions,
                'rolePermissions' => $rolePermissions
            ]);
        }
    }
}