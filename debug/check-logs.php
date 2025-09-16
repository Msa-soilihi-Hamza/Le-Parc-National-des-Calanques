<?php

declare(strict_types=1);

echo "📋 VÉRIFICATION DES LOGS D'ERREUR\n";
echo "==================================\n\n";

// Chemins possibles des logs
$possibleLogPaths = [
    // Logs PHP par défaut
    ini_get('error_log'),
    
    // Logs Laragon
    'C:\laragon\etc\apache2\logs\error.log',
    'C:\laragon\etc\nginx\logs\error.log',
    
    // Logs PHP dans Laragon
    'C:\laragon\bin\php\php-8.3.12-nts-Win32-vs16-x64\logs\php_errors.log',
    'C:\laragon\bin\php\php-8.2.0-nts-Win32-vs16-x64\logs\php_errors.log',
    
    // Logs système Windows
    __DIR__ . '/../storage/logs/error.log',
    __DIR__ . '/../logs/error.log',
    
    // Logs dans le répertoire du projet
    getcwd() . '/error.log',
    
    // Logs Apache/Nginx locaux
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log'
];

echo "🔍 Recherche des fichiers de logs...\n\n";

$foundLogs = [];

foreach ($possibleLogPaths as $path) {
    if ($path && file_exists($path) && is_readable($path)) {
        $size = filesize($path);
        $foundLogs[] = $path;
        echo "✅ Trouvé : $path (" . formatBytes($size) . ")\n";
    }
}

if (empty($foundLogs)) {
    echo "❌ Aucun log trouvé dans les emplacements courants\n\n";
    
    echo "💡 Configuration PHP actuelle :\n";
    echo "   log_errors = " . (ini_get('log_errors') ? 'On' : 'Off') . "\n";
    echo "   error_log = " . (ini_get('error_log') ?: 'non défini') . "\n";
    echo "   display_errors = " . (ini_get('display_errors') ? 'On' : 'Off') . "\n\n";
    
    echo "🔧 Pour activer les logs, ajoutez dans votre php.ini :\n";
    echo "   log_errors = On\n";
    echo "   error_log = C:\\laragon\\tmp\\php_errors.log\n\n";
    
} else {
    echo "\n📄 Lecture des dernières entrées de logs...\n";
    echo "===========================================\n\n";
    
    foreach ($foundLogs as $logPath) {
        echo "📄 Fichier : $logPath\n";
        echo str_repeat('-', 60) . "\n";
        
        $lines = @file($logPath);
        if ($lines === false) {
            echo "❌ Impossible de lire le fichier\n\n";
            continue;
        }
        
        // Afficher les 20 dernières lignes
        $recentLines = array_slice($lines, -20);
        
        if (empty($recentLines)) {
            echo "📋 Fichier vide\n\n";
            continue;
        }
        
        $foundRelevant = false;
        foreach ($recentLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Filtrer les lignes pertinentes
            if (stripos($line, 'email') !== false || 
                stripos($line, 'smtp') !== false || 
                stripos($line, 'verification') !== false ||
                stripos($line, 'register') !== false ||
                stripos($line, 'auth') !== false ||
                stripos($line, 'calanques') !== false) {
                
                echo "🔍 " . $line . "\n";
                $foundRelevant = true;
            }
        }
        
        if (!$foundRelevant) {
            echo "📋 Aucune entrée récente pertinente trouvée\n";
            echo "   Dernières lignes :\n";
            foreach (array_slice($recentLines, -3) as $line) {
                echo "   " . trim($line) . "\n";
            }
        }
        
        echo "\n";
    }
}

echo "🎯 PROCHAINES ÉTAPES :\n";
echo "======================\n";
echo "1. Testez l'inscription sur votre site web\n";
echo "2. Relancez ce script pour voir les nouveaux logs\n";
echo "3. Ou testez directement avec : php debug/test-auth-service.php\n\n";

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

echo "📝 Note: Les logs avec émojis (✅❌📧) viennent de nos modifications récentes\n";
