<?php

declare(strict_types=1);

// Test simple pour diagnostiquer l'envoi d'email
require_once __DIR__ . '/../autoload.php';

use ParcCalanques\Services\EmailService;

echo "🔍 DIAGNOSTIC ENVOI EMAIL\n";
echo "=========================\n\n";

echo "1️⃣ Test d'initialisation du service email...\n";

try {
    $emailService = new EmailService();
    echo "✅ EmailService créé avec succès\n\n";
    
    echo "2️⃣ Configuration détaillée :\n";
    echo "   Host: smtp.gmail.com\n";
    echo "   Port: 587\n";
    echo "   Username: hamza.msa-soilihi@laplateforme.io\n";
    echo "   Password: anab frwp hvlu gobb\n";
    echo "   From: hamza.msa-soilihi@laplateforme.io\n\n";
    
    echo "3️⃣ Test d'envoi d'email de vérification...\n";
    echo "   Email destinataire: hamza.msa-soilihi@laplateforme.io\n";
    echo "   (Vous devriez recevoir l'email sur cette adresse)\n\n";
    
    // Test avec votre propre email
    $testToken = bin2hex(random_bytes(32));
    $result = $emailService->sendVerificationEmail(
        'hamza.msa-soilihi@laplateforme.io', // Votre email
        'Hamza Test',
        $testToken
    );
    
    if ($result) {
        echo "✅ EMAIL ENVOYÉ AVEC SUCCÈS !\n";
        echo "📧 Vérifiez votre boîte de réception : hamza.msa-soilihi@laplateforme.io\n";
        echo "🔗 Token de test : $testToken\n";
    } else {
        echo "❌ ÉCHEC DE L'ENVOI\n";
        echo "🔍 L'email n'a pas pu être envoyé\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERREUR CRITIQUE :\n";
    echo "   Message : " . $e->getMessage() . "\n";
    echo "   Fichier : " . $e->getFile() . "\n";
    echo "   Ligne : " . $e->getLine() . "\n\n";
    
    echo "🔧 SOLUTIONS POSSIBLES :\n";
    echo "   1. Vérifiez votre connexion internet\n";
    echo "   2. Le mot de passe Gmail 'anab frwp hvlu gobb' est-il correct ?\n";
    echo "   3. Avez-vous activé l'authentification à 2 facteurs sur Gmail ?\n";
    echo "   4. Ce mot de passe est-il un 'mot de passe d'application' ?\n";
    echo "   5. Essayez de vous connecter manuellement à Gmail avec ces identifiants\n";
}

echo "\n4️⃣ Test de connectivité SMTP...\n";

$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;

$connection = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
if ($connection) {
    echo "✅ Connexion SMTP possible vers $smtp_host:$smtp_port\n";
    fclose($connection);
} else {
    echo "❌ Impossible de se connecter à $smtp_host:$smtp_port\n";
    echo "   Erreur : $errstr ($errno)\n";
    echo "   Le port 587 est peut-être bloqué par votre firewall\n";
}

echo "\n🎯 PROCHAINES ÉTAPES :\n";
echo "   1. Si l'email est envoyé ✅, le problème est ailleurs\n";
echo "   2. Si l'email échoue ❌, corrigez la configuration Gmail\n";
echo "   3. Testez ensuite l'inscription complète\n";

echo "\n";
