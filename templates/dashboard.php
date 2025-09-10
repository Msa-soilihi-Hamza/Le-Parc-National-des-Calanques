<!DOCTYPE html>
<html lang="fr" data-theme="parc">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Parc National des Calanques</title>
    <link href="/Le-Parc-National-des-Calanques/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen font-sans leading-relaxed">
    <header class="bg-primary text-primary-content shadow-lg">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-xl font-bold">🏞️ Parc des Calanques</div>
            <nav class="flex items-center gap-8">
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/dashboard" class="text-primary-content hover:opacity-80 transition-opacity">Tableau de bord</a>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/profile" class="text-primary-content hover:opacity-80 transition-opacity">Profil</a>
                <?php if ($user->isAdmin()): ?>
                    <a href="<?= $GLOBALS['basePath'] ?? '' ?>/admin" class="text-primary-content hover:opacity-80 transition-opacity">Administration</a>
                <?php endif; ?>
                <div class="bg-primary-content/10 px-4 py-2 rounded-lg text-sm">
                    <?= htmlspecialchars($user->getFullName()) ?>
                    (<?= htmlspecialchars($user->getRole()) ?>)
                </div>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/logout" class="text-primary-content hover:opacity-80 transition-opacity">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <?php if (isset($welcome_message)): ?>
            <div class="alert alert-success mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span><?= htmlspecialchars($welcome_message) ?></span>
            </div>
        <?php endif; ?>

        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h1 class="card-title text-3xl text-primary mb-4">Tableau de bord</h1>
                <p class="text-base-content/70">Bienvenue sur votre espace personnel du Parc National des Calanques.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">📍 Vos Visites</h3>
                    <p class="text-base-content/70 mb-4">Planifiez et gérez vos visites dans le parc.</p>
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">3</div>
                            <div class="stat-title text-sm text-base-content/60">Visites prévues</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">12</div>
                            <div class="stat-title text-sm text-base-content/60">Visites passées</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="<?= $GLOBALS['basePath'] ?? '' ?>/visits" class="btn btn-primary">Gérer les visites</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">🗺️ Itinéraires Favoris</h3>
                    <p class="text-base-content/70 mb-4">Vos sentiers et points d'intérêt sauvegardés.</p>
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">5</div>
                            <div class="stat-title text-sm text-base-content/60">Itinéraires</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">18</div>
                            <div class="stat-title text-sm text-base-content/60">Points d'intérêt</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="<?= $GLOBALS['basePath'] ?? '' ?>/routes" class="btn btn-primary">Voir les itinéraires</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">📚 Ressources</h3>
                    <p class="text-base-content/70 mb-4">Documentation et guides du parc.</p>
                    <ul class="list-disc list-inside space-y-2 my-4 text-base-content/70">
                        <li>Guide des sentiers</li>
                        <li>Faune et flore</li>
                        <li>Règlement du parc</li>
                        <li>Conseils de sécurité</li>
                    </ul>
                    <div class="card-actions">
                        <a href="<?= $GLOBALS['basePath'] ?? '' ?>/resources" class="btn btn-secondary">Consulter</a>
                    </div>
                </div>
            </div>

            <?php if ($user->isAdmin()): ?>
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-primary mb-4">⚙️ Administration</h3>
                    <p class="text-base-content/70 mb-4">Gestion avancée du système.</p>
                    <div class="grid grid-cols-2 gap-4 my-4">
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">157</div>
                            <div class="stat-title text-sm text-base-content/60">Utilisateurs</div>
                        </div>
                        <div class="stat bg-base-200 rounded-lg p-4 text-center">
                            <div class="stat-value text-2xl font-bold text-primary">8</div>
                            <div class="stat-title text-sm text-base-content/60">Admins</div>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="<?= $GLOBALS['basePath'] ?? '' ?>/admin" class="btn btn-primary">Panel d'admin</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>