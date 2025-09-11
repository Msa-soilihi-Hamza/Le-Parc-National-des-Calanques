<?php
// Détection automatique du chemin de base
$basePath = '';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptPath !== '/') {
    $basePath = $scriptPath;
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="parc">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parc National des Calanques</title>
    <link href="<?= $basePath ?>/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-base-100 min-h-screen font-sans">
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