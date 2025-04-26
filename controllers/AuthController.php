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
        if (Security::isAuthenticated()) {
            $this->redirect('/');
        }
        
        // Si la requête est de type POST, traiter la connexion
        if ($this->isPost()) {
            // Vérifier le jeton CSRF
            Security::checkCSRF();
            
            // Récupérer les données
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Tentative de connexion
            if (Security::login($username, $password)) {
                // Rediriger vers la page d'accueil
                $this->redirect('/');
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
        Security::logout();
        
        // Rediriger vers la page de connexion
        $this->redirect('/login');
    }
    
    /**
     * Affiche le formulaire d'inscription et traite l'inscription
     * @return void
     */
    public function register() {
        // Si l'utilisateur est déjà connecté, rediriger vers l'accueil
        if (Security::isAuthenticated()) {
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
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Enregistrer l'utilisateur
                $userId = $this->userModel->register($userData);
                
                if ($userId) {
                    // Connecter automatiquement l'utilisateur
                    Security::login($data['username'], $data['password']);
                    
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
}