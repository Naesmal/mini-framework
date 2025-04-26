<?php
/**
 * Classe Controller: Classe de base pour tous les contrôleurs
 */
class Controller {
    /**
     * Données à partager avec la vue
     * @var array
     */
    protected $viewData = [];
    
    /**
     * Rend une vue avec les données fournies
     * @param string $view Chemin de la vue (sans extension)
     * @param array $data Données à passer à la vue
     * @return void
     */
    protected function render($view, $data = []) {
        // Fusionner les données avec les données de vue existantes
        $this->viewData = array_merge($this->viewData, $data);
        
        // Extraire les variables pour qu'elles soient accessibles dans la vue
        extract($this->viewData);
        
        // Chemin complet de la vue
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        // Vérifier si la vue existe
        if (!file_exists($viewPath)) {
            throw new Exception("La vue '{$view}' n'existe pas.");
        }
        
        // Tampon de sortie pour capturer le contenu de la vue
        ob_start();
        include $viewPath;
        $content = ob_get_clean();  // Cette ligne capture le contenu dans $content
        
        // Vérifier s'il y a un layout
        $layoutPath = __DIR__ . '/../views/layouts/main.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;  // Si pas de layout, afficher directement le contenu
        }
    }
    
    /**
     * Redirige vers une URL spécifique
     * @param string $url L'URL de redirection
     * @return void
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Vérifie si la requête est de type POST
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Vérifie si la requête est de type GET
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Récupère les données POST filtrées
     * @param array $fields Liste des champs à récupérer (optionnel)
     * @return array
     */
    protected function getPostData($fields = []) {
        if (empty($fields)) {
            return filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        }
        
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = filter_input(INPUT_POST, $field, FILTER_SANITIZE_STRING);
        }
        
        return $data;
    }
    
    /**
     * Récupère les données GET filtrées
     * @param array $fields Liste des champs à récupérer (optionnel)
     * @return array
     */
    protected function getQueryData($fields = []) {
        if (empty($fields)) {
            return filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        }
        
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = filter_input(INPUT_GET, $field, FILTER_SANITIZE_STRING);
        }
        
        return $data;
    }
    
    /**
     * Partage des données avec toutes les vues
     * @param string $key Nom de la variable
     * @param mixed $value Valeur de la variable
     * @return void
     */
    protected function share($key, $value) {
        $this->viewData[$key] = $value;
    }
    
    /**
     * Renvoie une réponse JSON
     * @param mixed $data Les données à encoder en JSON
     * @param int $statusCode Code HTTP (par défaut 200)
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Gère les erreurs 404
     * @return void
     */
    protected function notFound() {
        http_response_code(404);
        $this->render('errors/404');
        exit;
    }
}