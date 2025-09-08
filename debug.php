<?php
// Page de diagnostic simple
echo "<h1>Diagnostic du Système</h1>";

// Test PHP
echo "<h2>✅ PHP Version</h2>";
echo "<p>Version: " . PHP_VERSION . "</p>";

// Test autoloader
echo "<h2>📁 Autoloader</h2>";
if (file_exists('autoload.php')) {
    echo "<p>✅ autoload.php trouvé</p>";
    require_once 'autoload.php';
    echo "<p>✅ Autoloader chargé</p>";
} else {
    echo "<p>❌ autoload.php manquant</p>";
}

// Test base de données
echo "<h2>🗄️ Base de données</h2>";
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "<p>✅ Connexion base de données réussie</p>";
        
        // Test table utilisateurs
        $stmt = $pdo->query("SHOW TABLES LIKE 'Utilisateur'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Table Utilisateur trouvée</p>";
        } else {
            echo "<p>⚠️ Table Utilisateur non trouvée</p>";
        }
    } else {
        echo "<p>❌ Connexion base de données échouée</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur base de données: " . $e->getMessage() . "</p>";
}

// Test JWT
echo "<h2>🔐 JWT</h2>";
try {
    use ParcCalanques\Auth\JwtService;
    $jwtService = new JwtService();
    echo "<p>✅ Service JWT initialisé</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur JWT: " . $e->getMessage() . "</p>";
}

// Test API endpoint simple
echo "<h2>🌐 Test API</h2>";
echo "<p><a href='api.php?test=1' target='_blank'>Tester api.php</a></p>";

echo "<h2>📝 Informations serveur</h2>";
echo "<p>Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Non défini') . "</p>";
?>