<?php

require_once 'config/database.php';

echo "=== TEST DE CONNEXION À LA BASE DE DONNÉES ===\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Connexion réussie à la base de données !\n\n";
        
        // Test de la connexion avec une requête simple
        $stmt = $pdo->query("SELECT DATABASE() as current_db, VERSION() as mysql_version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Base de données actuelle: " . $result['current_db'] . "\n";
        echo "Version MySQL: " . $result['mysql_version'] . "\n\n";
        
        // Lister les tables existantes
        echo "=== TABLES DANS LA BASE DE DONNÉES ===\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "❌ Aucune table trouvée dans la base de données\n";
        } else {
            echo "Nombre de tables: " . count($tables) . "\n";
            foreach ($tables as $table) {
                echo "- " . $table . "\n";
            }
        }
        
    } else {
        echo "❌ Échec de la connexion à la base de données\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}