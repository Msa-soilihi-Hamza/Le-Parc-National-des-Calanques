<?php

// Bootstrap de l'application - Chargement unique des variables d'environnement

require_once __DIR__ . '/../src/Shared/Utils/EnvLoader.php';

use ParcCalanques\Shared\Utils\EnvLoader;

// Charger une seule fois les variables d'environnement
EnvLoader::load();

// Configuration PHP basée sur l'environnement
$appEnv = EnvLoader::get('APP_ENV', 'development');
$appDebug = EnvLoader::get('APP_DEBUG', 'true') === 'true';

if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    $sessionLifetime = (int)EnvLoader::get('SESSION_LIFETIME', '120');
    ini_set('session.gc_maxlifetime', $sessionLifetime * 60);
    session_start();
}