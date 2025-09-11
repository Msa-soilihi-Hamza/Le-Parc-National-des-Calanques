<?php
/**
 * Script de réinitialisation de la base de données
 * ⚠️ ATTENTION : Ce script supprime TOUTES les données !
 */

echo "🚨 ATTENTION : Ce script va SUPPRIMER toutes les données !\n";
echo "Êtes-vous sûr de vouloir continuer ? (tapez 'OUI' pour confirmer) : ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'OUI') {
    echo "❌ Opération annulée.\n";
    exit;
}

try {
    $host = 'localhost';
    $port = '3308';
    $dbname = 'le-parc-national-des-calanques';
    $username = 'root';
    $password = '';
    
    // Connexion sans spécifier de base
    $dsn = "mysql:host=$host;port=$port;charset=utf8";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "🗑️  Suppression de la base de données...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    
    echo "🏗️  Recréation de la base de données...\n";
    $pdo->exec("CREATE DATABASE `$dbname`");
    
    echo "✅ Base de données réinitialisée !\n";
    echo "\n🔄 Exécutez maintenant : php migrate.php\n";
    
} catch(PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}