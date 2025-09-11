<?php
// Inclusion des composants
require_once __DIR__ . '/components/Navbar.php';

// Récupération des données utilisateur depuis la session/database
// Ces données seront passées au composant React
?>
<!DOCTYPE html>
<html lang="fr" data-theme="cupcake">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Parc National des Calanques</title>
    <link href="/Le-Parc-National-des-Calanques/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-base-200 font-sans leading-relaxed">
    <!-- Navbar PHP conservée -->
    <?= renderParcNavbar($isLoggedIn ?? true, $userRole ?? 'user', '', $userName ?? 'Utilisateur') ?>

    <main class="max-w-4xl mx-auto py-8 px-4 mt-4">
        <!-- Messages d'alerte PHP conservés -->
        <?php if (isset($welcome_message)): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-4">
                <?= htmlspecialchars($welcome_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Container pour le composant React -->
        <div id="user-profile-react"></div>

        <!-- Section de données JSON pour développement (conservée) -->
        <?php if (isset($user) && method_exists($user, 'toArray')): ?>
        <div class="card bg-gray-50 border border-gray-300 mt-8">
            <h3 class="text-gray-600 mb-4 text-lg font-medium">🔧 Données utilisateur (JSON)</h3>
            <pre class="bg-white p-4 rounded border border-gray-200 overflow-x-auto text-sm text-gray-800"><?= json_encode($user->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
        </div>
        <?php endif; ?>
    </main>

    <!-- Scripts React -->
    <script src="/Le-Parc-National-des-Calanques/public/js/main.bundle.js"></script>
    <script>
        // Montage du composant React avec les données utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($user) && method_exists($user, 'toArray')): ?>
            // Données utilisateur depuis PHP
            const userData = <?= json_encode($user->toArray()) ?>;
            
            // Montage du composant React
            if (window.UserProfile && window.mountReactComponent) {
                window.mountReactComponent(window.UserProfile, 'user-profile-react', { userData });
            } else {
                console.error('Composants React non disponibles');
                
                // Fallback: afficher un message d'erreur
                const container = document.getElementById('user-profile-react');
                if (container) {
                    container.innerHTML = `
                        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                            ⚠️ Le composant React n'a pas pu être chargé. Veuillez actualiser la page.
                        </div>
                    `;
                }
            }
            <?php else: ?>
            // Pas de données utilisateur disponibles - laisser React charger via API
            if (window.UserProfile && window.mountReactComponent) {
                window.mountReactComponent(window.UserProfile, 'user-profile-react', {});
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>