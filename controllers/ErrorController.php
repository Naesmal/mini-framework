<?php
/**
 * ErrorController: Contrôleur pour les pages d'erreur
 */
class ErrorController extends Controller {
    /**
     * Affiche la page 404 (non trouvé)
     * @return void
     */
    public function notFound() {
        http_response_code(404);
        $this->render('errors/404', [
            'title' => 'Page non trouvée'
        ]);
    }
    
    /**
     * Affiche la page d'erreur 403 (interdit)
     * @return void
     */
    public function forbidden() {
        http_response_code(403);
        $this->render('errors/403', [
            'title' => 'Accès interdit'
        ]);
    }
    
    /**
     * Affiche la page d'erreur 500 (erreur serveur)
     * @return void
     */
    public function serverError() {
        http_response_code(500);
        $this->render('errors/500', [
            'title' => 'Erreur serveur'
        ]);
    }
}