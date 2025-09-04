<?php

require_once 'config/database.php';

echo "=== TEST DE CRÉATION DE TABLE ===\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Connexion réussie\n";
        
        // Test simple de création de table
        $sql = "CREATE TABLE test_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        echo "Création d'une table de test...\n";
        $result = $pdo->exec($sql);
        echo "✅ Table de test créée\n";
        
        // Vérifier les tables maintenant
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\nTables existantes:\n";
        foreach ($tables as $table) {
            echo "- " . $table . "\n";
        }
        
        // Supprimer la table de test
        $pdo->exec("DROP TABLE test_table");
        echo "\n✅ Table de test supprimée\n";
        
    } else {
        echo "❌ Échec de la connexion\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Code d'erreur: " . $e->getCode() . "\n";
}