<?php
// Version simple pour tester sans dépendances
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parc National des Calanques</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .status { padding: 20px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .links { margin: 30px 0; }
        .links a { display: inline-block; margin: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .links a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏔️ Parc National des Calanques</h1>
        
        <div class="status success">
            <strong>✅ Système initialisé avec succès !</strong>
            <p>Le système d'authentification JWT a été intégré.</p>
        </div>
        
        <div class="status info">
            <strong>📡 APIs disponibles :</strong>
            <ul>
                <li><code>POST /api/auth/login</code> - Connexion JWT</li>
                <li><code>GET /api/auth/me</code> - Informations utilisateur</li>
                <li><code>POST /api/auth/refresh</code> - Rafraîchir token</li>
                <li><code>GET /api/health</code> - Statut de l'API</li>
            </ul>
        </div>
        
        <div class="links">
            <h2>🔧 Outils de diagnostic :</h2>
            <a href="debug.php">Diagnostic Système</a>
            <a href="api.php?test=1">Test API</a>
            <a href="JWT_INTEGRATION_GUIDE.md">Guide JWT</a>
        </div>
        
        <div class="status info">
            <strong>🏗️ Pour utiliser le système complet :</strong>
            <ol>
                <li>Vérifiez que MySQL est démarré (port 3308)</li>
                <li>Exécutez les migrations : <code>php migrate.php</code></li>
                <li>Testez la connexion avec debug.php</li>
            </ol>
        </div>
        
        <hr>
        <p><small>Serveur: <?php echo $_SERVER['SERVER_SOFTWARE']; ?> | PHP: <?php echo PHP_VERSION; ?></small></p>
    </div>
</body>
</html>