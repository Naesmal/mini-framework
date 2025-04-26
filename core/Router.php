<?php
/**
 * Classe Router: Gère le routage des requêtes HTTP
 */
class Router {
    /**
     * Routes définies (format: ['pattern' => ['Controller', 'action']])
     * @var array
     */
    private $routes = [];
    
    /**
     * Constructeur qui charge les routes définies
     */
    public function __construct() {
        // Charger les routes depuis le fichier de configuration
        $routesFile = __DIR__ . '/../config/routes.php';
        if (file_exists($routesFile)) {
            $this->routes = require $routesFile;
        }
    }
    
    /**
     * Route la requête vers le contrôleur et l'action appropriés
     * @return void
     */
    public function dispatch() {
        // Récupérer l'URL demandée
        $url = $this->getRequestUrl();
        
        // Parcourir les routes définies
        foreach ($this->routes as $pattern => $target) {
            // Convertir le pattern en expression régulière
            $regexPattern = $this->patternToRegex($pattern);
            
            // Vérifier si l'URL correspond au pattern
            if (preg_match($regexPattern, $url, $matches)) {
                // Extraire le contrôleur et l'action
                list($controller, $action) = $target;
                
                // Extraire les paramètres de l'URL
                array_shift($matches); // Supprime le premier élément (correspondance complète)
                $params = array_values($matches);
                
                // Vérifier si le contrôleur existe
                $controllerFile = __DIR__ . "/../controllers/{$controller}.php";
                if (!file_exists($controllerFile)) {
                    $this->handleError("Le contrôleur '{$controller}' n'existe pas");
                }
                
                // Inclure le fichier du contrôleur
                require_once $controllerFile;
                
                // Vérifier si la classe du contrôleur existe
                if (!class_exists($controller)) {
                    $this->handleError("La classe du contrôleur '{$controller}' n'existe pas");
                }
                
                // Instancier le contrôleur
                $controllerInstance = new $controller();
                
                // Vérifier si l'action existe
                if (!method_exists($controllerInstance, $action)) {
                    $this->handleError("L'action '{$action}' n'existe pas dans le contrôleur '{$controller}'");
                }
                
                // Appeler l'action avec les paramètres
                call_user_func_array([$controllerInstance, $action], $params);
                return;
            }
        }
        
        // Si aucune route n'a été trouvée
        $this->handleError("Page non trouvée", 404);
    }
    
    /**
     * Convertit un pattern de route en expression régulière
     * @param string $pattern Pattern de la route (ex: /users/{id})
     * @return string
     */
    private function patternToRegex($pattern) {
        // Échapper les caractères spéciaux
        $pattern = preg_quote($pattern, '/');
        
        // Remplacer les placeholders {param} par des groupes de capture
        $pattern = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '([^/]+)', $pattern);
        
        // Ajouter les délimiteurs et ancres
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Récupère l'URL demandée
     * @return string
     */
    private function getRequestUrl() {
        // Récupérer le chemin de l'URL
        $url = $_SERVER['REQUEST_URI'];
        
        // Supprimer les paramètres de requête
        $position = strpos($url, '?');
        if ($position !== false) {
            $url = substr($url, 0, $position);
        }
        
        // Supprimer le slash final si présent
        $url = rtrim($url, '/');
        
        // Ajouter un slash si l'URL est vide
        if (empty($url)) {
            $url = '/';
        }
        
        return $url;
    }
    
    /**
     * Gère les erreurs
     * @param string $message Message d'erreur
     * @param int $code Code HTTP
     * @return void
     */
    private function handleError($message, $code = 500) {
        http_response_code($code);
        
        // Vérifier si la vue d'erreur existe
        $errorView = __DIR__ . "/../views/errors/{$code}.php";
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo "<h1>Erreur {$code}</h1>";
            echo "<p>{$message}</p>";
        }
        
        exit;
    }
}