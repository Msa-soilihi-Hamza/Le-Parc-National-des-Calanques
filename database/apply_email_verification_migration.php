<?php

require_once __DIR__ . '/../autoload.php';

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3308;dbname=le-parc-national-des-calanques;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "🔄 Application de la migration pour la vérification d'email...\n";

    // Lire et exécuter la migration
    $migrationSQL = file_get_contents(__DIR__ . '/migrations/009_add_email_verification.sql');
    
    // Diviser par requêtes (séparées par ;)
    $queries = array_filter(array_map('trim', explode(';', $migrationSQL)));
    
    $pdo->beginTransaction();
    
    foreach ($queries as $query) {
        if (!empty($query) && !str_starts_with(trim($query), '--')) {
            echo "📝 Exécution : " . substr($query, 0, 50) . "...\n";
            $pdo->exec($query);
        }
    }
    
    $pdo->commit();
    echo "✅ Migration appliquée avec succès !\n";
    
    // Vérifier les colonnes ajoutées
    $result = $pdo->query("DESCRIBE Utilisateur");
    echo "\n📋 Structure de la table Utilisateur :\n";
    while ($row = $result->fetch()) {
        if (in_array($row['Field'], ['email_verified', 'email_verification_token', 'email_verification_expires_at'])) {
            echo "✅ " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "❌ Erreur lors de l'application de la migration : " . $e->getMessage() . "\n";
}