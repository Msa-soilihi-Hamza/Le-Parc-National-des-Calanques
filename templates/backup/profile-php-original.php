<?php
// Inclusion des composants
require_once __DIR__ . '/components/Navbar.php';

// Simulation d'un utilisateur connecté (en réalité, ces données viendraient de la session)
$isLoggedIn = true;
$userRole = 'user'; // ou 'admin'
$userName = 'John Doe';
$userEmail = 'john@example.com';
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
    <!-- Navbar avec utilisateur connecté -->
    <?= renderParcNavbar($isLoggedIn, $userRole, '', $userName) ?>

    <main class="max-w-4xl mx-auto py-8 px-4 mt-4">
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

        <div class="card mb-8">
            <div class="text-center mb-8">
                <div class="w-24 h-24 bg-blue-800 rounded-full flex items-center justify-center text-white text-3xl mx-auto mb-4">
                    <?= strtoupper(substr($user->getFirstName(), 0, 1) . substr($user->getLastName(), 0, 1)) ?>
                </div>
                <h1 class="text-3xl text-blue-800 font-bold mb-2"><?= htmlspecialchars($user->getFullName()) ?></h1>
                <div class="inline-block <?= $user->isAdmin() ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' ?> px-4 py-1 rounded-full text-sm font-medium">
                    <?php if ($user->isAdmin()): ?>
                        👑 Administrateur
                    <?php else: ?>
                        👤 Utilisateur
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl text-blue-800 font-semibold mb-4">📧 Informations de compte</h3>
                    
                    <div class="mb-4">
                        <div class="form-label">Adresse email</div>
                        <div class="text-gray-900"><?= htmlspecialchars($user->getEmail()) ?></div>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Statut du compte</div>
                        <div class="text-gray-900">
                            <?php if ($user->isActive()): ?>
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-md text-xs font-medium">✓ Actif</span>
                            <?php else: ?>
                                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-md text-xs font-medium">✗ Inactif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Email vérifié</div>
                        <div class="text-gray-900">
                            <?php if ($user->isEmailVerified()): ?>
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-md text-xs font-medium">✓ Vérifié</span>
                                <br><small class="text-gray-500">Le <?= $user->getEmailVerifiedAt()->format('d/m/Y à H:i') ?></small>
                            <?php else: ?>
                                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-md text-xs font-medium">✗ Non vérifié</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Rôle utilisateur</div>
                        <div class="text-gray-900">
                            <strong><?= ucfirst($user->getRole()) ?></strong>
                            <?php if ($user->isAdmin()): ?>
                                <br><small class="text-gray-500">Accès complet à l'administration</small>
                            <?php else: ?>
                                <br><small class="text-gray-500">Accès utilisateur standard</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl text-blue-800 font-semibold mb-4">📅 Informations de membre</h3>
                    
                    <div class="mb-4">
                        <div class="form-label">Membre depuis</div>
                        <div class="text-gray-900">
                            <?php if ($user->getCreatedAt()): ?>
                                <?= $user->getCreatedAt()->format('d/m/Y') ?>
                                <br><small class="text-gray-500"><?= $user->getCreatedAt()->format('H:i') ?></small>
                            <?php else: ?>
                                Non disponible
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Dernière mise à jour</div>
                        <div class="text-gray-900">
                            <?php if ($user->getUpdatedAt()): ?>
                                <?= $user->getUpdatedAt()->format('d/m/Y à H:i') ?>
                            <?php else: ?>
                                Non disponible
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Abonnement</div>
                        <div class="text-gray-900">
                            <?php if ($user->hasAbonnement()): ?>
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-md text-xs font-medium">✓ Actif</span>
                            <?php else: ?>
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 rounded-md text-xs font-medium">Aucun abonnement</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($user->getCarteMembreNumero()): ?>
                    <div class="border <?= $user->isCarteMembreValide() ? 'border-green-400 bg-green-50' : 'border-gray-300' ?> rounded-lg p-4 mt-4">
                        <div class="mb-4">
                            <div class="form-label">Carte de membre</div>
                            <div class="text-gray-900">
                                <strong><?= htmlspecialchars($user->getCarteMembreNumero()) ?></strong>
                                <?php if ($user->getCarteMembreDateValidite()): ?>
                                    <br><small class="text-gray-500">
                                        Valide jusqu'au <?= $user->getCarteMembreDateValidite()->format('d/m/Y') ?>
                                        <?php if ($user->isCarteMembreValide()): ?>
                                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium ml-2">✓ Valide</span>
                                        <?php else: ?>
                                            <span class="inline-block px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium ml-2">✗ Expirée</span>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center mt-8">
                <button type="button" class="btn-primary mr-4" onclick="alert('Fonctionnalité à venir')">
                    Modifier le profil
                </button>
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-200" onclick="alert('Fonctionnalité à venir')">
                    Changer le mot de passe
                </button>
            </div>
        </div>

        <!-- Section données JSON pour développement -->
        <div class="card bg-gray-50 border border-gray-300">
            <h3 class="text-gray-600 mb-4 text-lg font-medium">🔧 Données utilisateur (JSON)</h3>
            <pre class="bg-white p-4 rounded border border-gray-200 overflow-x-auto text-sm text-gray-800"><?= json_encode($user->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
        </div>
    </main>
</body>
</html>