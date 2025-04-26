<?php
/**
 * Point d'entrée de l'application
 * Tous les requêtes HTTP passent par ce fichier
 */

// Définir le chemin vers le dossier racine
define('ROOT_PATH', dirname(__DIR__));

// Charger la classe App
require_once ROOT_PATH . '/core/App.php';

// Démarrer l'application
App::getInstance()->run();