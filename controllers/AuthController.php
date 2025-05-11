<?php
/**
 * AuthController: Contrôleur pour l'authentification
 */
class AuthController extends Controller {
    /**
     * Modèle d'utilisateur
     * @var User
     */
    private $userModel;
    
    /**
     * Constructeur
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Affiche le formulaire de connexion et traite la connexion
     * @return void
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Auth::isAuthenticated()) {
            $this->redirect('/');
        }
        
        // Si la requête est de type POST, traiter la connexion
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer les données
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
            
            // Tentative de connexion
            if (Auth::login($username, $password, $remember)) {
                // Définir un message flash
                Session::setFlash('success', 'Connexion réussie! Bienvenue ' . Session::get('user_username'));
                
                // Rediriger vers la page de redirection si elle existe, sinon vers l'accueil
                $redirect = Session::get('redirect_after_login', '/');
                Session::remove('redirect_after_login');
                
                $this->redirect($redirect);
            } else {
                // Afficher le formulaire avec une erreur
                $this->render('auth/login', [
                    'title' => 'Connexion',
                    'error' => 'Nom d\'utilisateur ou mot de passe incorrect.',
                    'username' => $username
                ]);
            }
        } else {
            // Afficher le formulaire de connexion
            $this->render('auth/login', [
                'title' => 'Connexion'
            ]);
        }
    }
    
    /**
     * Déconnecte l'utilisateur
     * @return void
     */
    public function logout() {
        // Déconnecter l'utilisateur
        Auth::logout();
        
        // Définir un message flash
        Session::setFlash('success', 'Vous avez été déconnecté avec succès.');
        
        // Rediriger vers la page de connexion
        $this->redirect('/login');
    }
    
    /**
     * Affiche le formulaire d'inscription et traite l'inscription
     * @return void
     */
    public function register() {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Auth::isAuthenticated()) {
            $this->redirect('/');
        }
        
        // Si la requête est de type POST, traiter l'inscription
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData([
                'username', 'email', 'password', 'password_confirm'
            ]));
            
            // Valider les données
            $errors = [];
            
            // Valider le nom d'utilisateur
            if (empty($data['username'])) {
                $errors[] = 'Le nom d\'utilisateur est obligatoire.';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            } elseif ($this->userModel->usernameExists($data['username'])) {
                $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
            
            // Valider l'email
            if (empty($data['email'])) {
                $errors[] = 'L\'email est obligatoire.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format d\'email invalide.';
            } elseif ($this->userModel->emailExists($data['email'])) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            
            // Valider le mot de passe
            if (empty($data['password'])) {
                $errors[] = 'Le mot de passe est obligatoire.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            
            // Valider la confirmation du mot de passe
            if ($data['password'] !== $data['password_confirm']) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            
            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                // Préparer les données pour l'enregistrement
                $userData = [
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role_id' => 3, // Rôle "user" par défaut
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Enregistrer l'utilisateur
                $userId = $this->userModel->register($userData);
                
                if ($userId) {
                    // Connecter automatiquement l'utilisateur
                    Auth::login($data['username'], $data['password']);
                    
                    // Définir un message flash
                    Session::setFlash('success', 'Inscription réussie! Bienvenue ' . $data['username']);
                    
                    // Rediriger vers l'accueil
                    $this->redirect('/');
                } else {
                    $errors[] = 'Erreur lors de l\'inscription.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('auth/register', [
                'title' => 'Inscription',
                'data' => $data,
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire d'inscription
            $this->render('auth/register', [
                'title' => 'Inscription'
            ]);
        }
    }
    
    /**
     * Affiche le profil de l'utilisateur connecté
     * @return void
     */
    public function profile() {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            // Stocker l'URL actuelle pour rediriger après connexion
            Session::set('redirect_after_login', '/profile');
            $this->redirect('/login');
        }
        
        // Récupérer l'utilisateur connecté
        $user = Auth::currentUser();
        
        if (!$user) {
            Session::setFlash('error', 'Une erreur est survenue. Veuillez vous reconnecter.');
            Auth::logout();
            $this->redirect('/login');
        }
        
        // Récupérer le rôle de l'utilisateur
        $roleModel = new Role();
        $role = $roleModel->find($user['role_id']);
        
        // Rendre la vue
        $this->render('auth/profile', [
            'title' => 'Mon profil',
            'user' => $user,
            'role' => $role
        ]);
    }
    
    /**
     * Affiche et traite le formulaire de modification du profil
     * @return void
     */
    public function editProfile() {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            Session::set('redirect_after_login', '/profile/edit');
            $this->redirect('/login');
        }
        
        // Récupérer l'utilisateur connecté
        $user = Auth::currentUser();
        
        if (!$user) {
            Session::setFlash('error', 'Une erreur est survenue. Veuillez vous reconnecter.');
            Auth::logout();
            $this->redirect('/login');
        }
        
        // Si la requête est de type POST, traiter la modification
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData(['username', 'email']));
            
            // Valider les données
            $errors = [];
            
            // Valider le nom d'utilisateur
            if (empty($data['username'])) {
                $errors[] = 'Le nom d\'utilisateur est obligatoire.';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            } elseif ($data['username'] !== $user['username'] && $this->userModel->usernameExists($data['username'])) {
                $errors[] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }
            
            // Valider l'email
            if (empty($data['email'])) {
                $errors[] = 'L\'email est obligatoire.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format d\'email invalide.';
            } elseif ($data['email'] !== $user['email'] && $this->userModel->emailExists($data['email'])) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
            
            // Si pas d'erreurs, mettre à jour l'utilisateur
            if (empty($errors)) {
                $success = $this->userModel->update($user['id'], $data);
                
                if ($success) {
                    // Mettre à jour les données de session
                    Session::set('user_username', $data['username']);
                    Session::set('user_email', $data['email']);
                    
                    // Définir un message flash
                    Session::setFlash('success', 'Votre profil a été mis à jour avec succès.');
                    
                    // Rediriger vers la page de profil
                    $this->redirect('/profile');
                } else {
                    $errors[] = 'Erreur lors de la mise à jour du profil.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('auth/edit_profile', [
                'title' => 'Modifier mon profil',
                'user' => array_merge($user, $data),
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire de modification
            $this->render('auth/edit_profile', [
                'title' => 'Modifier mon profil',
                'user' => $user
            ]);
        }
    }
    
    /**
     * Affiche et traite le formulaire de changement de mot de passe
     * @return void
     */
    public function changePassword() {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::isAuthenticated()) {
            Session::set('redirect_after_login', '/profile/password');
            $this->redirect('/login');
        }
        
        // Récupérer l'utilisateur connecté
        $user = Auth::currentUser();
        
        if (!$user) {
            Session::setFlash('error', 'Une erreur est survenue. Veuillez vous reconnecter.');
            Auth::logout();
            $this->redirect('/login');
        }
        
        // Si la requête est de type POST, traiter le changement de mot de passe
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer et nettoyer les données
            $data = Security::sanitize($this->getPostData([
                'current_password', 'new_password', 'confirm_password'
            ]));
            
            // Valider les données
            $errors = [];
            
            // Vérifier le mot de passe actuel
            if (empty($data['current_password'])) {
                $errors[] = 'Le mot de passe actuel est obligatoire.';
            } elseif (!Security::verifyPassword($data['current_password'], $user['password'])) {
                $errors[] = 'Le mot de passe actuel est incorrect.';
            }
            
            // Valider le nouveau mot de passe
            if (empty($data['new_password'])) {
                $errors[] = 'Le nouveau mot de passe est obligatoire.';
            } elseif (strlen($data['new_password']) < 6) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
            } elseif ($data['new_password'] === $data['current_password']) {
                $errors[] = 'Le nouveau mot de passe doit être différent de l\'ancien.';
            }
            
            // Valider la confirmation du mot de passe
            if ($data['new_password'] !== $data['confirm_password']) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }
            
            // Si pas d'erreurs, mettre à jour le mot de passe
            if (empty($errors)) {
                $success = $this->userModel->update($user['id'], [
                    'password' => Security::hashPassword($data['new_password'])
                ]);
                
                if ($success) {
                    // Définir un message flash
                    Session::setFlash('success', 'Votre mot de passe a été modifié avec succès.');
                    
                    // Rediriger vers la page de profil
                    $this->redirect('/profile');
                } else {
                    $errors[] = 'Erreur lors de la modification du mot de passe.';
                }
            }
            
            // En cas d'erreurs, afficher le formulaire avec les erreurs
            $this->render('auth/change_password', [
                'title' => 'Changer de mot de passe',
                'errors' => $errors
            ]);
        } else {
            // Afficher le formulaire de changement de mot de passe
            $this->render('auth/change_password', [
                'title' => 'Changer de mot de passe'
            ]);
        }
    }
}