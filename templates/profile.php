<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Parc National des Calanques</title>
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
        .container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        .profile-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .profile-header { text-align: center; margin-bottom: 2rem; }
        .profile-avatar { width: 100px; height: 100px; background: #2c5282; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; margin: 0 auto 1rem; }
        .profile-name { font-size: 2rem; color: #2c5282; margin-bottom: 0.5rem; }
        .profile-role { background: #e3f2fd; color: #1976d2; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 500; display: inline-block; }
        .profile-role.admin { background: #fff3e0; color: #f57c00; }
        .profile-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; }
        .detail-section { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; }
        .detail-section h3 { color: #2c5282; margin-bottom: 1rem; font-size: 1.2rem; }
        .detail-item { margin-bottom: 1rem; }
        .detail-label { font-weight: 600; color: #495057; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .detail-value { color: #212529; margin-top: 0.25rem; }
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500; }
        .status-active { background: #d4edda; color: #155724; }
        .status-verified { background: #cce5ff; color: #004085; }
        .status-unverified { background: #f8d7da; color: #721c24; }
        .btn { padding: 0.75rem 1.5rem; background: #2c5282; color: white; text-decoration: none; border-radius: 6px; display: inline-block; transition: background 0.2s; border: none; cursor: pointer; }
        .btn:hover { background: #2a4f7a; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-group { text-align: center; margin-top: 2rem; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .membership-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 1rem; margin-top: 1rem; }
        .membership-active { border-color: #28a745; background: #f8fff9; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">🏞️ Parc des Calanques</div>
            <nav class="nav-links">
                <a href="<?= $GLOBALS['basePath'] ?? '' ?>/profile">Profil</a>
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
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user->getFirstName(), 0, 1) . substr($user->getLastName(), 0, 1)) ?>
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($user->getFullName()) ?></h1>
                <div class="profile-role <?= $user->isAdmin() ? 'admin' : '' ?>">
                    <?php if ($user->isAdmin()): ?>
                        👑 Administrateur
                    <?php else: ?>
                        👤 Utilisateur
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-section">
                    <h3>📧 Informations de compte</h3>
                    
                    <div class="detail-item">
                        <div class="detail-label">Adresse email</div>
                        <div class="detail-value"><?= htmlspecialchars($user->getEmail()) ?></div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Statut du compte</div>
                        <div class="detail-value">
                            <?php if ($user->isActive()): ?>
                                <span class="status-badge status-active">✓ Actif</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">✗ Inactif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Email vérifié</div>
                        <div class="detail-value">
                            <?php if ($user->isEmailVerified()): ?>
                                <span class="status-badge status-verified">✓ Vérifié</span>
                                <br><small>Le <?= $user->getEmailVerifiedAt()->format('d/m/Y à H:i') ?></small>
                            <?php else: ?>
                                <span class="status-badge status-unverified">✗ Non vérifié</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Rôle utilisateur</div>
                        <div class="detail-value">
                            <strong><?= ucfirst($user->getRole()) ?></strong>
                            <?php if ($user->isAdmin()): ?>
                                <br><small>Accès complet à l'administration</small>
                            <?php else: ?>
                                <br><small>Accès utilisateur standard</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>📅 Informations de membre</h3>
                    
                    <div class="detail-item">
                        <div class="detail-label">Membre depuis</div>
                        <div class="detail-value">
                            <?php if ($user->getCreatedAt()): ?>
                                <?= $user->getCreatedAt()->format('d/m/Y') ?>
                                <br><small><?= $user->getCreatedAt()->format('H:i') ?></small>
                            <?php else: ?>
                                Non disponible
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Dernière mise à jour</div>
                        <div class="detail-value">
                            <?php if ($user->getUpdatedAt()): ?>
                                <?= $user->getUpdatedAt()->format('d/m/Y à H:i') ?>
                            <?php else: ?>
                                Non disponible
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Abonnement</div>
                        <div class="detail-value">
                            <?php if ($user->hasAbonnement()): ?>
                                <span class="status-badge status-active">✓ Actif</span>
                            <?php else: ?>
                                <span class="status-badge">Aucun abonnement</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($user->getCarteMembreNumero()): ?>
                    <div class="membership-card <?= $user->isCarteMembreValide() ? 'membership-active' : '' ?>">
                        <div class="detail-item">
                            <div class="detail-label">Carte de membre</div>
                            <div class="detail-value">
                                <strong><?= htmlspecialchars($user->getCarteMembreNumero()) ?></strong>
                                <?php if ($user->getCarteMembreDateValidite()): ?>
                                    <br><small>
                                        Valide jusqu'au <?= $user->getCarteMembreDateValidite()->format('d/m/Y') ?>
                                        <?php if ($user->isCarteMembreValide()): ?>
                                            <span class="status-badge status-verified">✓ Valide</span>
                                        <?php else: ?>
                                            <span class="status-badge status-unverified">✗ Expirée</span>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn" onclick="alert('Fonctionnalité à venir')">
                    Modifier le profil
                </button>
                <button type="button" class="btn btn-secondary" onclick="alert('Fonctionnalité à venir')">
                    Changer le mot de passe
                </button>
            </div>
        </div>

        <!-- Section données JSON pour développement -->
        <div class="profile-card" style="background: #f8f9fa; border: 1px solid #dee2e6;">
            <h3 style="color: #6c757d; margin-bottom: 1rem;">🔧 Données utilisateur (JSON)</h3>
            <pre style="background: #ffffff; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.9rem; border: 1px solid #dee2e6;"><?= json_encode($user->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
        </div>
    </main>
</body>
</html>