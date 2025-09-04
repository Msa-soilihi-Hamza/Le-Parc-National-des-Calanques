<?php

require_once 'config/database.php';

echo "=== RESET DE LA BASE DE DONNÉES ===\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        die("❌ Impossible de se connecter à la base de données\n");
    }
    
    echo "✅ Connexion réussie\n";
    
    // Désactiver les contraintes de clés étrangères temporairement
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Obtenir la liste des tables existantes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "ℹ️  Aucune table à supprimer\n";
    } else {
        echo "Tables à supprimer:\n";
        foreach ($tables as $table) {
            echo "- Suppression de: " . $table . "\n";
            $pdo->exec("DROP TABLE IF EXISTS `" . $table . "`");
        }
        echo "✅ Toutes les tables ont été supprimées\n";
    }
    
    // Réactiver les contraintes de clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n=== BASE DE DONNÉES RÉINITIALISÉE ===\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}