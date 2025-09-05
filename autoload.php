<?php

// Simple autoloader for the project
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Handle ParcCalanques namespace
    if (strpos($class, 'ParcCalanques\\') === 0) {
        $classPath = str_replace('ParcCalanques\\', 'src' . DIRECTORY_SEPARATOR, $classPath);
    }
    
    $file = __DIR__ . DIRECTORY_SEPARATOR . $classPath . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    // Try to load from config directory for Database class
    if ($class === 'Database') {
        $file = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});