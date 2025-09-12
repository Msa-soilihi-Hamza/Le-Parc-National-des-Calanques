<?php
// Détection automatique du chemin de base
$basePath = '';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptPath !== '/') {
    $basePath = $scriptPath;
}

// Debug info (remove in production)
// error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'undefined'));
// error_log("scriptPath: " . $scriptPath);
// error_log("basePath: " . $basePath);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parc National des Calanques</title>
    <!-- CSS loaded from: <?= $basePath ?>/public/css/output.css -->
    <link href="<?= $basePath ?>/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-background text-foreground min-h-screen font-sans">
    <div id="root"></div>
    
    <script src="<?= $basePath ?>/public/js/main.bundle.js"></script>
    <script>
        // Monter l'application React principale
        if (window.mountReactApp) {
            window.mountReactApp();
        } else {
            console.error('Function mountReactApp not found');
        }
    </script>
</body>
</html>