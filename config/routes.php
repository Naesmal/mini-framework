<?php
/**
 * Configuration des routes de l'application
 * Format: 'pattern' => ['Controller', 'action']
 */
return [
    // Page d'accueil
    '/' => ['HomeController', 'index'],
    
    // Routes pour les utilisateurs (publiques)
    '/users' => ['UserController', 'index'],
    '/users/{id}' => ['UserController', 'show'],
    
    // Routes pour les utilisateurs (authentifiées)
    '/users/create' => ['UserController', 'create'],
    '/users/{id}/edit' => ['UserController', 'edit'],
    '/users/{id}/delete' => ['UserController', 'delete'],
    
    // Routes pour l'authentification
    '/login' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],
    '/register' => ['AuthController', 'register'],
    '/profile' => ['AuthController', 'profile'],
    '/profile/edit' => ['AuthController', 'editProfile'],
    '/profile/password' => ['AuthController', 'changePassword'],
    
    // Routes du panneau d'administration
    '/admin' => ['AdminController', 'dashboard'],
    '/admin/users' => ['AdminController', 'users'],
    '/admin/users/create' => ['AdminController', 'createUser'],
    '/admin/users/{id}/edit' => ['AdminController', 'editUser'],
    '/admin/users/{id}/delete' => ['AdminController', 'deleteUser'],
    '/admin/roles' => ['AdminController', 'roles'],
    '/admin/roles/{id}/edit' => ['AdminController', 'editRole'],
    
    // Pages d'erreur
    '/error/forbidden' => ['ErrorController', 'forbidden'],
    '/error/notfound' => ['ErrorController', 'notFound']
];