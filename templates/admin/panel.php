<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel d'Administration - Parc National des Calanques</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f8f9fa; line-height: 1.6; }
        .header { background: #dc3545; color: white; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; display: flex; align-items: center; gap: 0.5rem; }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: white; text-decoration: none; transition: opacity 0.2s; }
        .nav-links a:hover { opacity: 0.8; }
        .user-info { background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 6px; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .admin-header { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .admin-header h1 { color: #dc3545; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .admin-header .warning { background: #fff3cd; color: #856404; padding: 1rem; border-radius: 6px; border: 1px solid #ffeaa7; margin-top: 1rem; }
        .stats-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .stat-card.users { border-left: 4px solid #007bff; }
        .stat-card.active { border-left: 4px solid #28a745; }
        .stat-card.admins { border-left: 4px solid #dc3545; }
        .stat-card.new { border-left: 4px solid #ffc107; }
        .stat-number { font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem; }
        .stat-label { color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card h3 { color: #495057; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .btn { padding: 0.75rem 1.5rem; background: #dc3545; color: white; text-decoration: none; border-radius: 6px; display: inline-block; transition: background 0.2s; font-weight: 500; }
        .btn:hover { background: #c82333; }
        .btn-primary { background: #007bff; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .action-list { list-style: none; }
        .action-list li { padding: 0.5rem 0; border-bottom: 1px solid #dee2e6; }
        .action-list li:last-child { border-bottom: none; }
        .action-list a { color: #007bff; text-decoration: none; }
        .action-list a:hover { text-decoration: underline; }
        .recent-activity { max-height: 300px; overflow-y: auto; }
        .activity-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #dee2e6; }
        .activity-item:last-child { border-bottom: none; }
        .activity-time { color: #6c757d; font-size: 0.8rem; }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                ⚙️ Panel d'Administration
            </div>
            <nav class="nav-links">
                <a href="/dashboard">← Retour au tableau de bord</a>
                <div class="user-info">
                    <?= htmlspecialchars($user->getFullName()) ?>
                    (Administrateur)
                </div>
                <a href="/logout">Déconnexion</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-header">
            <h1>🛡️ Panel d'Administration</h1>
            <p>Gestion avancée du système et des utilisateurs du Parc National des Calanques.</p>
            <div class="warning">
                <strong>⚠️ Attention :</strong> Vous êtes dans l'interface d'administration. 
                Les actions effectuées ici peuvent affecter l'ensemble du système.
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-card users">
                <div class="stat-number" style="color: #007bff;"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Utilisateurs</div>
            </div>
            <div class="stat-card active">
                <div class="stat-number" style="color: #28a745;"><?= $stats['active_users'] ?></div>
                <div class="stat-label">Utilisateurs Actifs</div>
            </div>
            <div class="stat-card admins">
                <div class="stat-number" style="color: #dc3545;"><?= $stats['admin_users'] ?></div>
                <div class="stat-label">Administrateurs</div>
            </div>
            <div class="stat-card new">
                <div class="stat-number" style="color: #ffc107;"><?= $stats['new_registrations_today'] ?></div>
                <div class="stat-label">Nouveaux Aujourd'hui</div>
            </div>
        </div>

        <div class="admin-grid">
            <div class="card">
                <h3>👥 Gestion des Utilisateurs</h3>
                <p style="margin-bottom: 1rem; color: #6c757d;">
                    Gérez les comptes utilisateurs, les rôles et les permissions.
                </p>
                <ul class="action-list">
                    <li><a href="/admin/users">📋 Voir tous les utilisateurs</a></li>
                    <li><a href="/admin/users/create">➕ Créer un nouvel utilisateur</a></li>
                    <li><a href="/admin/users/pending">⏳ Utilisateurs en attente</a></li>
                    <li><a href="/admin/users/inactive">🚫 Comptes inactifs</a></li>
                </ul>
                <a href="/admin/users" class="btn btn-primary">Gérer les utilisateurs</a>
            </div>

            <div class="card">
                <h3>🔐 Sécurité & Sessions</h3>
                <p style="margin-bottom: 1rem; color: #6c757d;">
                    Surveillez les connexions et la sécurité du système.
                </p>
                <ul class="action-list">
                    <li><a href="/admin/sessions">📊 Sessions actives</a></li>
                    <li><a href="/admin/logs/auth">📝 Logs d'authentification</a></li>
                    <li><a href="/admin/security/failed-logins">❌ Tentatives échouées</a></li>
                    <li><a href="/admin/security/settings">⚙️ Paramètres de sécurité</a></li>
                </ul>
                <a href="/admin/security" class="btn btn-warning">Sécurité</a>
            </div>

            <div class="card">
                <h3>🏞️ Contenu du Parc</h3>
                <p style="margin-bottom: 1rem; color: #6c757d;">
                    Gérez les informations et contenus du parc.
                </p>
                <ul class="action-list">
                    <li><a href="/admin/points-interest">📍 Points d'intérêt</a></li>
                    <li><a href="/admin/routes">🗺️ Itinéraires</a></li>
                    <li><a href="/admin/events">📅 Événements</a></li>
                    <li><a href="/admin/resources">📚 Ressources</a></li>
                </ul>
                <a href="/admin/content" class="btn btn-success">Gérer le contenu</a>
            </div>

            <div class="card">
                <h3>📊 Statistiques & Rapports</h3>
                <p style="margin-bottom: 1rem; color: #6c757d;">
                    Analyses et rapports d'utilisation du système.
                </p>
                <ul class="action-list">
                    <li><a href="/admin/stats/visits">👥 Statistiques de visites</a></li>
                    <li><a href="/admin/stats/popular-routes">📈 Itinéraires populaires</a></li>
                    <li><a href="/admin/reports/monthly">📋 Rapport mensuel</a></li>
                    <li><a href="/admin/analytics">📊 Analytics détaillés</a></li>
                </ul>
                <a href="/admin/stats" class="btn btn-primary">Voir les stats</a>
            </div>

            <div class="card">
                <h3>⚙️ Configuration Système</h3>
                <p style="margin-bottom: 1rem; color: #6c757d;">
                    Paramètres avancés et maintenance du système.
                </p>
                <ul class="action-list">
                    <li><a href="/admin/config/general">🔧 Paramètres généraux</a></li>
                    <li><a href="/admin/config/database">💾 Configuration BDD</a></li>
                    <li><a href="/admin/maintenance">🔧 Mode maintenance</a></li>
                    <li><a href="/admin/backup">💾 Sauvegarde système</a></li>
                </ul>
                <a href="/admin/config" class="btn">Configuration</a>
            </div>

            <div class="card">
                <h3>📱 Activité Récente</h3>
                <div class="recent-activity">
                    <div class="activity-item">
                        <span>Nouvel utilisateur inscrit: Marie Dupont</span>
                        <span class="activity-time">Il y a 5 min</span>
                    </div>
                    <div class="activity-item">
                        <span>Connexion admin: Jean Martin</span>
                        <span class="activity-time">Il y a 12 min</span>
                    </div>
                    <div class="activity-item">
                        <span>Mise à jour itinéraire: Calanque d'En-Vau</span>
                        <span class="activity-time">Il y a 1h</span>
                    </div>
                    <div class="activity-item">
                        <span>Nouveau point d'intérêt ajouté</span>
                        <span class="activity-time">Il y a 2h</span>
                    </div>
                    <div class="activity-item">
                        <span>Sauvegarde système effectuée</span>
                        <span class="activity-time">Il y a 4h</span>
                    </div>
                </div>
                <a href="/admin/logs" class="btn btn-primary">Voir tous les logs</a>
            </div>
        </div>
    </main>
</body>
</html>