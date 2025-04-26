<?php
class Autoloader {
    /**
     * Enregistre l'autoloader
     */
    public static function register() {
        spl_autoload_register([self::class, 'autoload']);
    }
    
    /**
     * Charge automatiquement les classes
     * @param string $class Le nom de la classe à charger
     */
    public static function autoload($class) {
        // Chemins possibles pour différents types de classes
        $paths = [
            __DIR__ . '/../models/' . $class . '.php',
            __DIR__ . '/../controllers/' . $class . '.php',
            __DIR__ . '/' . $class . '.php',
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }
}