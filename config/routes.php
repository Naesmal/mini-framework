<?php
/**
 * Configuration des routes de l'application
 * Format: 'pattern' => ['Controller', 'action']
 */
return [
    // Page d'accueil
    '/' => ['HomeController', 'index'],
    
    // Routes pour les utilisateurs
    '/users' => ['UserController', 'index'],
    '/users/create' => ['UserController', 'create'],
    '/users/{id}' => ['UserController', 'show'],
    '/users/{id}/edit' => ['UserController', 'edit'],
    '/users/{id}/delete' => ['UserController', 'delete'],
    
    // Routes pour l'authentification
    '/login' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],
    '/register' => ['AuthController', 'register'],
    
    // Pages d'erreur
    '/error/forbidden' => ['ErrorController', 'forbidden'],
    '/error/notfound' => ['ErrorController', 'notFound']
];