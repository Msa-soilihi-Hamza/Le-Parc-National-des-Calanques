<?php
// Script pour vérifier et créer la base de données si nécessaire

echo "<h1>Vérification Base de Données</h1>";

try {
    // Connexion sans spécifier la base pour la créer si nécessaire
    $pdo = new PDO("mysql:host=localhost;port=3308;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connexion MySQL réussie</p>";
    
    // Vérifier si la base existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'le-parc-national-des-calanques'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Base de données 'le-parc-national-des-calanques' existe</p>";
        
        // Se connecter à la base et vérifier les tables
        $pdo->exec("USE `le-parc-national-des-calanques`");
        
        $tables = ['Utilisateur', 'users'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<p>✅ Table '$table' existe</p>";
                
                // Compter les utilisateurs
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $stmt->fetch()['count'];
                echo "<p>📊 Nombre d'utilisateurs dans '$table': $count</p>";
            } else {
                echo "<p>⚠️ Table '$table' n'existe pas</p>";
            }
        }
    } else {
        echo "<p>❌ Base de données 'le-parc-national-des-calanques' n'existe pas</p>";
        echo "<p>🔧 Création de la base de données...</p>";
        
        $pdo->exec("CREATE DATABASE `le-parc-national-des-calanques`");
        echo "<p>✅ Base de données créée</p>";
        echo "<p>➡️ Maintenant, exécutez: <code>php migrate.php</code></p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Erreur: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<p>💡 MySQL n'est pas démarré. Démarrez-le dans WAMP.</p>";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p>💡 Problème d'authentification. Vérifiez user/password.</p>";
    }
}

echo "<hr>";
echo "<p><a href='index_simple.php'>← Retour à l'accueil</a></p>";
?>