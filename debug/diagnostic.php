<?php

declare(strict_types=1);

// Script de diagnostic complet pour identifier les problèmes
require_once __DIR__ . '/../autoload.php';

use ParcCalanques\Services\EmailService;
use ParcCalanques\Models\UserRepository;
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Auth\SessionManager;
use Database;

echo "🔍 DIAGNOSTIC COMPLET DU SYSTÈME\n";
echo "================================\n\n";

// 1. Test de la base de données
echo "1️⃣ TEST DE LA BASE DE DONNÉES\n";
echo "------------------------------\n";
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if ($pdo) {
        echo "✅ Connexion à la base de données : OK\n";
        
        // Test de la table utilisateur
        $stmt = $pdo->query("SHOW TABLES LIKE 'utilisateur'");
        if ($stmt->fetch()) {
            echo "✅ Table 'utilisateur' : EXISTE\n";
            
            // Vérifier les colonnes email verification
            $stmt = $pdo->query("SHOW COLUMNS FROM utilisateur WHERE Field LIKE '%email_verification%'");
            $emailCols = $stmt->fetchAll();
            if (count($emailCols) >= 2) {
                echo "✅ Colonnes de vérification email : OK\n";
            } else {
                echo "❌ Colonnes de vérification email : MANQUANTES\n";
                echo "   Exécutez la migration 009_add_email_verification.sql\n";
            }
        } else {
            echo "❌ Table 'utilisateur' : MANQUANTE\n";
        }
    } else {
        echo "❌ Connexion à la base de données : ÉCHEC\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur base de données : " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Test des dépendances Composer
echo "2️⃣ TEST DES DÉPENDANCES COMPOSER\n";
echo "--------------------------------\n";

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer : INSTALLÉ\n";
} else {
    echo "❌ PHPMailer : MANQUANT\n";
    echo "   Exécutez : composer install\n";
}

if (class_exists('Firebase\JWT\JWT')) {
    echo "✅ Firebase JWT : INSTALLÉ\n";
} else {
    echo "❌ Firebase JWT : MANQUANT\n";
}

echo "\n";

// 3. Test du service Email
echo "3️⃣ TEST DU SERVICE EMAIL\n";
echo "-------------------------\n";
try {
    $emailService = new EmailService();
    echo "✅ EmailService : INITIALISÉ\n";
    
    // Test d'envoi fictif (sans vraiment envoyer)
    echo "📧 Configuration SMTP : smtp.gmail.com:587\n";
    echo "📧 Email expéditeur : hamza.msa-soilihi@laplateforme.io\n";
    
} catch (Exception $e) {
    echo "❌ EmailService : ERREUR\n";
    echo "   Détails : " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Test du UserRepository
echo "4️⃣ TEST DU USER REPOSITORY\n";
echo "---------------------------\n";
try {
    if (isset($pdo)) {
        $userRepo = new UserRepository($pdo);
        echo "✅ UserRepository : INITIALISÉ\n";
        
        // Test des méthodes email verification
        if (method_exists($userRepo, 'findByVerificationToken')) {
            echo "✅ Méthode findByVerificationToken : EXISTE\n";
        } else {
            echo "❌ Méthode findByVerificationToken : MANQUANTE\n";
        }
        
        if (method_exists($userRepo, 'markEmailAsVerified')) {
            echo "✅ Méthode markEmailAsVerified : EXISTE\n";
        } else {
            echo "❌ Méthode markEmailAsVerified : MANQUANTE\n";
        }
    }
} catch (Exception $e) {
    echo "❌ UserRepository : ERREUR\n";
    echo "   Détails : " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Test du système d'authentification complet
echo "5️⃣ TEST SYSTÈME D'AUTHENTIFICATION\n";
echo "-----------------------------------\n";
try {
    if (isset($pdo) && isset($userRepo)) {
        $sessionManager = new SessionManager($userRepo);
        $jwtService = new JwtService();
        $authService = new AuthService($userRepo, $sessionManager, $jwtService, $emailService ?? null);
        
        echo "✅ AuthService : INITIALISÉ\n";
        
        if (method_exists($authService, 'registerWithJwt')) {
            echo "✅ Méthode registerWithJwt : EXISTE\n";
        }
        
        if (method_exists($authService, 'verifyEmailByToken')) {
            echo "✅ Méthode verifyEmailByToken : EXISTE\n";
        }
    }
} catch (Exception $e) {
    echo "❌ AuthService : ERREUR\n";
    echo "   Détails : " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Test des endpoints API
echo "6️⃣ TEST DES ENDPOINTS API\n";
echo "--------------------------\n";
if (file_exists(__DIR__ . '/../api.php')) {
    echo "✅ Fichier api.php : EXISTE\n";
} else {
    echo "❌ Fichier api.php : MANQUANT\n";
}

if (file_exists(__DIR__ . '/../verify-email.php')) {
    echo "✅ Fichier verify-email.php : EXISTE\n";
} else {
    echo "❌ Fichier verify-email.php : MANQUANT\n";
}

echo "\n";

// 7. Recommandations
echo "7️⃣ RECOMMANDATIONS\n";
echo "-------------------\n";
echo "🔧 Pour tester l'inscription complète :\n";
echo "   1. Assurez-vous que Laragon/Apache est démarré\n";
echo "   2. Accédez à : http://localhost/Le-Parc-National-des-Calanques/\n";
echo "   3. Essayez de créer un compte\n";
echo "   4. Vérifiez les logs d'erreur PHP\n\n";

echo "📧 Pour tester l'envoi d'email :\n";
echo "   1. Vérifiez que vous avez un mot de passe d'application Gmail\n";
echo "   2. Testez avec : php debug/test-email.php\n\n";

echo "🗄️ Pour vérifier la base de données :\n";
echo "   1. Ouvrez phpMyAdmin\n";
echo "   2. Vérifiez la table 'utilisateur'\n";
echo "   3. Assurez-vous que les colonnes email_verification existent\n\n";

echo "✅ DIAGNOSTIC TERMINÉ\n";
echo "====================\n";
