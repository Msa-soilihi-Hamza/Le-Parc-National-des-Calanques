<?php

declare(strict_types=1);

// Test spécifique pour AuthService et EmailService
require_once __DIR__ . '/../autoload.php';

use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Services\EmailService;

echo "🔍 TEST AUTHSERVICE ET EMAIL\n";
echo "=============================\n\n";

echo "1️⃣ Test d'initialisation EmailService direct...\n";
try {
    $emailService = new EmailService();
    echo "✅ EmailService direct : OK\n\n";
} catch (Exception $e) {
    echo "❌ EmailService direct : ÉCHEC\n";
    echo "   Erreur : " . $e->getMessage() . "\n\n";
    
    echo "🛑 ARRÊT - EmailService ne peut pas être créé\n";
    echo "💡 Vérifiez que PHPMailer est installé : composer install\n";
    exit(1);
}

echo "2️⃣ Test d'initialisation AuthBootstrap...\n";
try {
    $authService = AuthBootstrap::init();
    echo "✅ AuthBootstrap : OK\n\n";
} catch (Exception $e) {
    echo "❌ AuthBootstrap : ÉCHEC\n";
    echo "   Erreur : " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "3️⃣ Test d'inscription avec email...\n";
$testEmail = 'test-debug-' . time() . '@example.com';
echo "   Email de test : $testEmail\n";

try {
    $result = $authService->registerWithJwt(
        'TestNom',
        'TestPrenom',
        $testEmail,
        'motdepassetest123'
    );
    
    echo "✅ Inscription réussie !\n";
    echo "📄 Résultat :\n";
    print_r($result);
    
    if (isset($result['email_verification_required']) && $result['email_verification_required']) {
        echo "\n✅ Vérification d'email requise (normal)\n";
        echo "📧 Un email devrait être envoyé à : $testEmail\n";
        
        // Mais on ne peut pas recevoir l'email car c'est une adresse fictive
        echo "⚠️  Note: $testEmail est fictif, vous ne recevrez pas l'email\n";
    }
    
} catch (Exception $e) {
    echo "❌ Inscription échouée :\n";
    echo "   Erreur : " . $e->getMessage() . "\n";
    echo "   Trace : " . $e->getTraceAsString() . "\n";
}

echo "\n4️⃣ Test d'envoi email réel vers votre adresse...\n";
echo "   Email : hamza.msa-soilihi@laplateforme.io\n";

try {
    $testToken = bin2hex(random_bytes(32));
    $sent = $emailService->sendVerificationEmail(
        'hamza.msa-soilihi@laplateforme.io',
        'Hamza MSA',
        $testToken
    );
    
    if ($sent) {
        echo "✅ Email envoyé vers votre vraie adresse !\n";
        echo "📧 Vérifiez votre boîte mail : hamza.msa-soilihi@laplateforme.io\n";
        echo "🔗 Token : $testToken\n";
    } else {
        echo "❌ Échec envoi email\n";
        echo "🔍 Le service email ne fonctionne pas correctement\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception envoi email :\n";
    echo "   " . $e->getMessage() . "\n";
}

echo "\n📋 RÉSUMÉ :\n";
echo "===========\n";
echo "Si vous voyez '✅ Email envoyé vers votre vraie adresse !'\n";
echo "alors le problème n'est PAS dans le service email.\n";
echo "Le problème peut être :\n";
echo "  - L'inscription via l'interface web ne fonctionne pas\n";
echo "  - Les logs d'erreur ne sont pas visibles\n";
echo "  - L'email va dans les spams\n\n";

echo "Vérifiez maintenant votre email hamza.msa-soilihi@laplateforme.io !\n";
