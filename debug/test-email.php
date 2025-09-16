<?php

declare(strict_types=1);

// Script de test pour l'envoi d'email
require_once __DIR__ . '/../autoload.php';

use ParcCalanques\Services\EmailService;

echo "📧 TEST D'ENVOI D'EMAIL\n";
echo "========================\n\n";

// Demander l'email de test
echo "Entrez votre email pour recevoir un test de vérification :\n";
$handle = fopen("php://stdin", "r");
$testEmail = trim(fgets($handle));
fclose($handle);

if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Email invalide\n";
    exit(1);
}

echo "\n🚀 Tentative d'envoi d'email à : $testEmail\n";
echo "--------------------------------------------\n";

try {
    $emailService = new EmailService();
    echo "✅ Service email initialisé\n";
    
    $testToken = bin2hex(random_bytes(32));
    echo "🔑 Token de test généré : " . substr($testToken, 0, 16) . "...\n";
    
    echo "📤 Envoi en cours...\n";
    $result = $emailService->sendVerificationEmail(
        $testEmail,
        'Utilisateur Test',
        $testToken
    );
    
    if ($result) {
        echo "✅ EMAIL ENVOYÉ AVEC SUCCÈS !\n";
        echo "📬 Vérifiez votre boîte de réception\n";
        echo "🔗 Lien de test : http://localhost/Le-Parc-National-des-Calanques/verify-email.php?token=$testToken\n";
    } else {
        echo "❌ ÉCHEC DE L'ENVOI\n";
        echo "🔍 Vérifiez :\n";
        echo "   - Votre connexion internet\n";
        echo "   - Les identifiants Gmail\n";
        echo "   - Le mot de passe d'application Gmail\n";
        echo "   - Les logs d'erreur PHP\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "\n🔧 Solutions possibles :\n";
    echo "   1. Vérifiez que PHPMailer est installé : composer install\n";
    echo "   2. Activez l'authentification à 2 facteurs sur Gmail\n";
    echo "   3. Générez un mot de passe d'application Gmail\n";
    echo "   4. Vérifiez que le port 587 n'est pas bloqué\n";
}

echo "\n";
