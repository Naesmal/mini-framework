<?php
/**
 * UserController: Contrôleur pour la gestion des utilisateurs
 */
class UserController extends Controller {
    /**
     * Modèle d'utilisateur
     * @var User
     */
    private $userModel;
    
    /**
     * Constructeur
     */
    public function __construct() {
        // Initialiser le modèle
        $this->userModel = new User();
        
        // Vérifier l'authentification pour toutes les actions sauf index et show
        $action = $_GET['action'] ?? '';
        if (!in_array($action, ['index', 'show']) && !Security::isAuthenticated()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Liste tous les utilisateurs
     * @return void
     */
    public function index() {
        // Récupérer tous les utilisateurs
        $users = $this->userModel->all();
        
        // Rendre la vue
        $this->render('users/index', [
            'title' => 'Liste des utilisateurs',
            'users' => $users
        ]);
    }
    
    /**
     * Affiche les détails d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function show($id) {
        // Récupérer l'utilisateur
        $user = $this->userModel->find($id);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->notFound();
        }
        
        // Rendre la vue
        $this->render('users/show', [
            'title' => 'Détails de l\'utilisateur',
            'user' => $user
        ]);
    }
    
    /**
     * Affiche le formulaire de création d'utilisateur
     * @return void
     */
    public function create() {
        // Si la requête est de type POST, traiter les données
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData());
            
            // Valider les données
            $errors = $this->validateUser($data);
            
            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                $userId = $this->userModel->register($data);
                
                if ($userId) {
                    // Rediriger vers la page de détails
                    $this->redirect('/users/' . $userId);
                } else {
                    $errors[] = 'Erreur lors de la création de l\'utilisateur.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('users/create', [
                'title' => 'Créer un utilisateur',
                'data' => $data,
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire vide
            $this->render('users/create', [
                'title' => 'Créer un utilisateur'
            ]);
        }
    }
    
    /**
     * Affiche le formulaire d'édition d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function edit($id) {
        // Récupérer l'utilisateur
        $user = $this->userModel->find($id);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->notFound();
        }
        
        // Si la requête est de type POST, traiter les données
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData());
            
            // Valider les données
            $errors = $this->validateUser($data, $id);
            
            // Si pas d'erreurs, mettre à jour l'utilisateur
            if (empty($errors)) {
                // Si le mot de passe est vide, le supprimer
                if (empty($data['password'])) {
                    unset($data['password']);
                }
                
                $success = $this->userModel->update($id, $data);
                
                if ($success) {
                    // Rediriger vers la page de détails
                    $this->redirect('/users/' . $id);
                } else {
                    $errors[] = 'Erreur lors de la mise à jour de l\'utilisateur.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('users/edit', [
                'title' => 'Modifier l\'utilisateur',
                'user' => array_merge($user, $data),
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire avec les données de l'utilisateur
            $this->render('users/edit', [
                'title' => 'Modifier l\'utilisateur',
                'user' => $user
            ]);
        }
    }
    
    /**
     * Supprime un utilisateur
     * @param int $id ID de l'utilisateur
     * @return void
     */
    public function delete($id) {
        // Vérifier l'authentification
        if (!Security::isAuthenticated()) {
            $this->redirect('/login');
        }
        
        // Vérifier le jeton CSRF
        Security::checkCSRF();
        
        // Récupérer l'utilisateur
        $user = $this->userModel->find($id);
        
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $this->notFound();
        }
        
        // Supprimer l'utilisateur
        $success = $this->userModel->delete($id);
        
        // Rediriger vers la liste des utilisateurs
        $this->redirect('/users');
    }
    
    /**
     * Valide les données d'un utilisateur
     * @param array $data Les données à valider
     * @param int $id ID de l'utilisateur (en cas de mise à jour)
     * @return array Les erreurs de validation
     */
    private function validateUser($data, $id = null) {
        $errors = [];
        
        // Valider le nom d'utilisateur
        if (empty($data['username'])) {
            $errors[] = 'Le nom d\'utilisateur est obligatoire.';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        } elseif ($this->userModel->usernameExists($data['username']) && $id === null) {
            $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
        }
        
        // Valider l'email
        if (empty($data['email'])) {
            $errors[] = 'L\'email est obligatoire.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format d\'email invalide.';
        } elseif ($this->userModel->emailExists($data['email']) && $id === null) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
        
        // Valider le mot de passe (uniquement pour la création ou si modifié)
        if ($id === null || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors[] = 'Le mot de passe est obligatoire.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
        }
        
        return $errors;
    }
}