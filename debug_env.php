<?php
echo "<h1>Debug Environment</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Test direct du chemin .env
$currentDir = __DIR__;
$envPath = $currentDir . '/.env';

echo "<strong>Répertoire actuel:</strong> $currentDir<br>";
echo "<strong>Chemin .env testé:</strong> $envPath<br>";

if (file_exists($envPath)) {
    echo "<span class='ok'>✅ Fichier .env trouvé!</span><br>";
    echo "<pre style='background:#f0f0f0;padding:10px;max-height:400px;overflow:auto;'>";
    echo htmlspecialchars(file_get_contents($envPath));
    echo "</pre>";
} else {
    echo "<span class='error'>❌ Fichier .env NOT FOUND</span><br>";
    
    // Lister tous les fichiers cachés
    echo "<h3>Fichiers cachés dans le répertoire:</h3>";
    $files = glob($currentDir . '/.*', GLOB_MARK);
    foreach ($files as $file) {
        if (basename($file) !== '.' && basename($file) !== '..') {
            echo "• " . basename($file) . "<br>";
        }
    }
}

// Test avec EnvLoader
echo "<h2>Test EnvLoader</h2>";
try {
    require_once 'backend/src/Shared/Utils/EnvLoader.php';
    
    // Test direct du chemin calculé par EnvLoader
    $envLoaderDir = dirname(__FILE__);
    $calculatedPath = $envLoaderDir . '/.env';
    echo "<strong>Chemin calculé par EnvLoader:</strong> $calculatedPath<br>";
    
    if (file_exists($calculatedPath)) {
        echo "<span class='ok'>✅ EnvLoader trouve le .env</span><br>";
    } else {
        echo "<span class='error'>❌ EnvLoader ne trouve PAS le .env</span><br>";
    }
    
    // Essayer de charger avec la classe EnvLoader
    $envLoaderClass = new ParcCalanques\Shared\Utils\EnvLoader();
    $envLoaderClass::load();
    echo "<span class='ok'>✅ EnvLoader chargé sans erreur</span><br>";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Erreur EnvLoader: " . $e->getMessage() . "</span><br>";
} catch (Error $e) {
    echo "<span class='error'>❌ Erreur PHP: " . $e->getMessage() . "</span><br>";
}
?>