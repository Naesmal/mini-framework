<?php
/**
 * Classe App: Point d'entrée principal de l'application
 */
class App {
    /**
     * Instance unique de l'application (pattern Singleton)
     * @var App
     */
    private static $instance = null;
    
    /**
     * Instance du routeur
     * @var Router
     */
    private $router;
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        // Charger les fichiers du noyau
        $this->loadCore();
        
        // Initialiser le routeur
        $this->router = new Router();
    }
    
    /**
     * Récupère l'instance unique de l'application
     * @return App
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Démarre l'application
     * @return void
     */
    public function run() {
        // Configurer les erreurs
        $this->setupErrorHandling();
        
        // Démarrer la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Dispatcher la requête
        $this->router->dispatch();
    }
    
    /**
     * Charge les fichiers du noyau
     * @return void
     */
    private function loadCore() {
        // Liste des fichiers noyau à charger
        $coreFiles = [
            'Database.php',
            'Model.php',
            'Controller.php',
            'Router.php',
            'Security.php'
        ];
        
        // Charger chaque fichier
        foreach ($coreFiles as $file) {
            $filePath = __DIR__ . '/' . $file;
            if (file_exists($filePath)) {
                require_once $filePath;
            } else {
                die("Erreur: Le fichier noyau '{$file}' est manquant.");
            }
        }
    }
    
    /**
     * Configure la gestion des erreurs
     * @return void
     */
    private function setupErrorHandling() {
        // Définir le gestionnaire d'erreurs
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        
        // Définir le gestionnaire d'exceptions
        set_exception_handler(function($exception) {
            $this->handleException($exception);
        });
    }
    
    /**
     * Gère les exceptions non capturées
     * @param Exception $exception L'exception
     * @return void
     */
    private function handleException($exception) {
        // Enregistrer l'exception dans les logs
        error_log($exception->getMessage() . ' dans ' . $exception->getFile() . ' à la ligne ' . $exception->getLine());
        
        // En mode développement, afficher l'erreur
        if ($this->isDevMode()) {
            echo '<h1>Erreur</h1>';
            echo '<p>' . $exception->getMessage() . '</p>';
            echo '<p>Dans ' . $exception->getFile() . ' à la ligne ' . $exception->getLine() . '</p>';
            echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        } else {
            // En production, afficher une page d'erreur générique
            include __DIR__ . '/../views/errors/500.php';
        }
        
        exit;
    }
    
    /**
     * Vérifie si l'application est en mode développement
     * @return bool
     */
    private function isDevMode() {
        $configFile = __DIR__ . '/../config/app.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            return isset($config['dev_mode']) && $config['dev_mode'] === true;
        }
        return false;
    }
}