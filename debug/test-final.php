<?php

declare(strict_types=1);

// Test final après corrections
require_once __DIR__ . '/../autoload.php';

use ParcCalanques\Auth\AuthBootstrap;

echo "🎯 TEST FINAL - INSCRIPTION COMPLÈTE\n";
echo "=====================================\n\n";

echo "1️⃣ Initialisation AuthService...\n";
try {
    $authService = AuthBootstrap::init();
    echo "✅ AuthService initialisé\n\n";
} catch (Exception $e) {
    echo "❌ Erreur AuthService : " . $e->getMessage() . "\n";
    exit(1);
}

echo "2️⃣ Test d'inscription complète...\n";
$testEmail = 'hamza.msa-soilihi@laplateforme.io'; // Votre vraie adresse
$testData = [
    'nom' => 'TestCorrection',
    'prenom' => 'Hamza',
    'email' => $testEmail,
    'password' => 'motdepassetest123'
];

echo "   Données d'inscription :\n";
echo "   Email: {$testData['email']}\n";
echo "   Nom: {$testData['nom']} {$testData['prenom']}\n\n";

try {
    $result = $authService->registerWithJwt(
        $testData['nom'],
        $testData['prenom'],
        $testData['email'],
        $testData['password']
    );
    
    echo "✅ INSCRIPTION RÉUSSIE !\n";
    echo "📋 Résultat :\n";
    
    if (isset($result['user'])) {
        echo "   👤 Utilisateur créé avec ID : " . $result['user']['id'] . "\n";
        echo "   📧 Email : " . $result['user']['email'] . "\n";
    }
    
    if (isset($result['email_verification_required']) && $result['email_verification_required']) {
        echo "   ✅ Vérification d'email requise (correct)\n";
        echo "   📨 Message : " . $result['message'] . "\n";
        echo "\n🎉 SUCCÈS COMPLET !\n";
        echo "📧 Vérifiez votre boîte mail : $testEmail\n";
        echo "📬 Vous devriez recevoir un email de vérification\n";
    } else {
        echo "   ⚠️  Vérification d'email non requise (inattendu)\n";
    }
    
} catch (Exception $e) {
    echo "❌ INSCRIPTION ÉCHOUÉE :\n";
    echo "   Erreur : " . $e->getMessage() . "\n";
    echo "   Fichier : " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    echo "🔍 Détails de debug :\n";
    echo "   Trace : " . $e->getTraceAsString() . "\n";
}

echo "\n3️⃣ Vérification en base de données...\n";
try {
    // Vérifier si l'utilisateur a été créé
    $database = new Database();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->prepare("SELECT id_utilisateur, email, email_verified, email_verification_token FROM utilisateur WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Utilisateur trouvé en base :\n";
        echo "   ID: {$user['id_utilisateur']}\n";
        echo "   Email: {$user['email']}\n";
        echo "   Email vérifié: " . ($user['email_verified'] ? 'Oui' : 'Non') . "\n";
        echo "   Token de vérification: " . ($user['email_verification_token'] ? 'Présent' : 'Absent') . "\n";
    } else {
        echo "❌ Utilisateur non trouvé en base\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur vérification base : " . $e->getMessage() . "\n";
}

echo "\n🎯 CONCLUSION :\n";
echo "===============\n";
echo "Si vous voyez '✅ INSCRIPTION RÉUSSIE !' et '✅ Utilisateur trouvé en base'\n";
echo "alors le problème est RÉSOLU ! 🎉\n\n";
echo "Vérifiez maintenant votre email : $testEmail\n";
echo "L'email peut prendre quelques minutes à arriver.\n";
echo "Vérifiez aussi le dossier spam/courrier indésirable.\n";
