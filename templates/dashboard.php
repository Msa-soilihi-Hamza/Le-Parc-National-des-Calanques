<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Parc National des Calanques</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f8f9fa; line-height: 1.6; }
        .header { background: #2c5282; color: white; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: white; text-decoration: none; transition: opacity 0.2s; }
        .nav-links a:hover { opacity: 0.8; }
        .user-info { background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 6px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .welcome-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .welcome-card h1 { color: #2c5282; margin-bottom: 1rem; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h3 { color: #2c5282; margin-bottom: 1rem; }
        .btn { padding: 0.75rem 1.5rem; background: #2c5282; color: white; text-decoration: none; border-radius: 6px; display: inline-block; transition: background 0.2s; }
        .btn:hover { background: #2a4f7a; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0; }
        .stat-item { text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 6px; }
        .stat-number { font-size: 2rem; font-weight: bold; color: #2c5282; }
        .stat-label { color: #6c757d; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">🏞️ Parc des Calanques</div>
            <nav class="nav-links">
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/dashboard">Tableau de bord</a>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/profile">Profil</a>
                <?php if ($user->isAdmin()): ?>
                    <a href="<?= $GLOBALS['basePath'] ?? '' ?>/admin">Administration</a>
                <?php endif; ?>
                <div class="user-info">
                    <?= htmlspecialchars($user->getFullName()) ?>
                    (<?= htmlspecialchars($user->getRole()) ?>)
                </div>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/logout">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (isset($welcome_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($welcome_message) ?>
            </div>
        <?php endif; ?>

        <div class="welcome-card">
            <h1>Tableau de bord</h1>
            <p>Bienvenue sur votre espace personnel du Parc National des Calanques.</p>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3>📍 Vos Visites</h3>
                <p>Planifiez et gérez vos visites dans le parc.</p>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Visites prévues</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Visites passées</div>
                    </div>
                </div>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/visits" class="btn">Gérer les visites</a>
            </div>

            <div class="card">
                <h3>🗺️ Itinéraires Favoris</h3>
                <p>Vos sentiers et points d'intérêt sauvegardés.</p>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Itinéraires</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">18</div>
                        <div class="stat-label">Points d'intérêt</div>
                    </div>
                </div>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/routes" class="btn">Voir les itinéraires</a>
            </div>

            <div class="card">
                <h3>📚 Ressources</h3>
                <p>Documentation et guides du parc.</p>
                <ul style="margin: 1rem 0; padding-left: 1.5rem;">
                    <li>Guide des sentiers</li>
                    <li>Faune et flore</li>
                    <li>Règlement du parc</li>
                    <li>Conseils de sécurité</li>
                </ul>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/resources" class="btn btn-secondary">Consulter</a>
            </div>

            <?php if ($user->isAdmin()): ?>
            <div class="card">
                <h3>⚙️ Administration</h3>
                <p>Gestion avancée du système.</p>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">157</div>
                        <div class="stat-label">Utilisateurs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">8</div>
                        <div class="stat-label">Admins</div>
                    </div>
                </div>
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/admin" class="btn">Panel d'admin</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>