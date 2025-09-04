<?php

require_once 'config/database.php';

echo "=== INSERTION DES DONNÉES D'EXEMPLE ===\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        die("❌ Impossible de se connecter à la base de données\n");
    }
    
    echo "✅ Connexion réussie\n";
    
    // Lire et exécuter le fichier de données
    $sql = file_get_contents('database/migrations/002_insert_sample_data.sql');
    
    if ($sql === false) {
        die("❌ Impossible de lire le fichier de données\n");
    }
    
    // Nettoyer le SQL des commentaires
    $lines = explode("\n", $sql);
    $cleanedLines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && !preg_match('/^\s*--/', $line)) {
            $cleanedLines[] = $line;
        }
    }
    
    $cleanedSql = implode(' ', $cleanedLines);
    
    // Diviser en requêtes
    $queries = array_filter(
        array_map('trim', explode(';', $cleanedSql)),
        function($query) {
            return !empty($query);
        }
    );
    
    $pdo->beginTransaction();
    
    foreach ($queries as $query) {
        if (!empty(trim($query))) {
            echo "Insertion: " . substr(trim($query), 0, 50) . "...\n";
            try {
                $result = $pdo->exec($query);
                echo "  ✅ Données insérées\n";
            } catch (PDOException $e) {
                echo "  ❌ Erreur: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }
    
    $pdo->commit();
    echo "\n✅ Toutes les données d'exemple ont été insérées\n";
    
} catch (Exception $e) {
    if ($pdo) {
        $pdo->rollback();
    }
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}