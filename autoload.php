<?php

// Load Composer autoloader first
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Simple autoloader for the project
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Handle ParcCalanques namespace - chercher d'abord dans backend/src/, puis src/
    if (strpos($class, 'ParcCalanques\\') === 0) {
        $backendClassPath = str_replace('ParcCalanques\\', 'backend' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $classPath);
        $backendFile = __DIR__ . DIRECTORY_SEPARATOR . $backendClassPath . '.php';

        if (file_exists($backendFile)) {
            require_once $backendFile;
            return true;
        }

        // Fallback vers l'ancien chemin src/
        $classPath = str_replace('ParcCalanques\\', 'src' . DIRECTORY_SEPARATOR, $classPath);
    }
    
    $file = __DIR__ . DIRECTORY_SEPARATOR . $classPath . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    // Try to load from config directory for Database class
    if ($class === 'Database') {
        // Chercher d'abord dans backend/config/
        $backendFile = __DIR__ . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        if (file_exists($backendFile)) {
            require_once $backendFile;
            return true;
        }

        // Fallback vers config/
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});