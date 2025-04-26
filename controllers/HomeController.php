<?php
/**
 * HomeController: Contrôleur pour la page d'accueil
 */
class HomeController extends Controller {
    /**
     * Action pour la page d'accueil
     * @return void
     */
    public function index() {
        // Exemple de données à passer à la vue
        $data = [
            'title' => 'Accueil',
            'message' => 'Bienvenue sur notre application!'
        ];
        
        // Rendre la vue
        $this->render('home/index', $data);
    }
}