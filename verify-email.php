<?php

require_once 'autoload.php';

use ParcCalanques\Models\UserRepository;
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Auth\JwtService;
use ParcCalanques\Auth\SessionManager;
use ParcCalanques\Services\EmailService;
use ParcCalanques\Exceptions\AuthException;

// Récupérer le token depuis l'URL
$token = $_GET['token'] ?? null;

$message = '';
$success = false;
$userInfo = null;

if (!$token) {
    $message = 'Token de vérification manquant.';
} else {
    try {
        // Initialiser les services
        $pdo = new PDO(
            'mysql:host=localhost;port=3308;dbname=le-parc-national-des-calanques;charset=utf8mb4',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        
        $userRepository = new UserRepository($pdo);
        $sessionManager = new SessionManager($userRepository);
        $jwtService = new JwtService();
        
        // EmailService peut échouer, créons-le optionnel
        $emailService = null;
        try {
            $emailService = new EmailService();
        } catch (Exception $emailError) {
            // Log l'erreur mais continuons sans EmailService
            error_log("EmailService initialization failed: " . $emailError->getMessage());
        }
        
        $authService = new AuthService(
            $userRepository, 
            $sessionManager, 
            $jwtService, 
            $emailService
        );
        
        // Vérifier l'email
        $result = $authService->verifyEmailByToken($token);
        
        $success = true;
        $message = $result['message'];
        $userInfo = $result['user'];
        
    } catch (AuthException $e) {
        $message = $e->getMessage();
    } catch (Exception $e) {
        error_log("Erreur lors de la vérification d'email : " . $e->getMessage());
        $message = 'Une erreur est survenue lors de la vérification. Veuillez réessayer plus tard.';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Email vérifié' : 'Erreur de vérification'; ?> - Parc National des Calanques</title>
    <link href="./public/css/output.css" rel="stylesheet">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="min-h-screen hero-gradient flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="card">
            <?php if ($success): ?>
                <!-- Succès -->
                <div class="success-icon">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        🎉 Email vérifié !
                    </h1>
                    
                    <p class="text-gray-600 mb-6">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                    
                    <?php if ($userInfo): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <p class="text-green-800">
                                <strong>Bienvenue, <?php echo htmlspecialchars($userInfo['first_name']); ?> !</strong><br>
                                Votre compte est maintenant actif.
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-3">
                        <a href="./index.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                            🏔️ Accéder à votre compte
                        </a>
                        
                        <div class="text-sm text-gray-500">
                            Vous allez être redirigé vers la page de connexion
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Erreur -->
                <div class="error-icon">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        ❌ Erreur de vérification
                    </h1>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <p class="text-red-800">
                            <?php echo htmlspecialchars($message); ?>
                        </p>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="./index.php" class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200">
                            🏠 Retour à l'accueil
                        </a>
                        
                        <div class="text-sm text-gray-500">
                            <p>Le lien de vérification peut avoir expiré (24h).</p>
                            <p>Contactez-nous si le problème persiste.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-white/80">
            <p class="text-sm">
                🌊 Parc National des Calanques 🌊
            </p>
            <p class="text-xs mt-2 opacity-75">
                Un patrimoine naturel exceptionnel à préserver
            </p>
        </div>
    </div>
    
    <?php if ($success): ?>
        <!-- Redirection automatique après succès -->
        <script>
            setTimeout(() => {
                window.location.href = './index.php';
            }, 5000); // Redirection après 5 secondes
        </script>
    <?php endif; ?>
</body>
</html>