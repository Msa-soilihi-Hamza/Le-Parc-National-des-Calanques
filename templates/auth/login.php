<?php
// Inclusion des composants
require_once __DIR__ . '/../components/Navbar.php';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="cupcake">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Parc National des Calanques</title>
    <link href="/Le-Parc-National-des-Calanques/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-white">
    <!-- Navbar PHP conservée -->
    <?= renderParcNavbar(false) ?>
    
    <!-- Container pour le composant React -->
    <div id="login-form-react"></div>

    <!-- Scripts React -->
    <script src="/Le-Parc-National-des-Calanques/public/js/main.bundle.js"></script>
    <script>
        // Montage du composant React
        document.addEventListener('DOMContentLoaded', function() {
            const basePath = '<?= $GLOBALS['basePath'] ?? '/Le-Parc-National-des-Calanques' ?>';
            const error = <?= json_encode($error ?? null) ?>;
            const success = <?= json_encode($success ?? null) ?>;
            
            if (window.LoginForm && window.mountReactComponent) {
                // Fonction de soumission personnalisée (optionnelle)
                const handleSubmit = async (formData) => {
                    try {
                        const response = await window.api.login(formData.email, formData.password);
                        if (response.success) {
                            // Redirection vers le profil ou dashboard
                            window.location.href = basePath + '/profile';
                        }
                    } catch (err) {
                        console.error('Erreur de connexion:', err);
                        // En cas d'erreur, laisser la soumission PHP normale se faire
                        throw err;
                    }
                };

                window.mountReactComponent(window.LoginForm, 'login-form-react', {
                    error: error,
                    success: success,
                    basePath: basePath,
                    onSubmit: null // Utilise la soumission PHP pour l'instant
                });
            } else {
                console.error('Composants React non disponibles');
                
                // Fallback: afficher un message d'erreur
                const container = document.getElementById('login-form-react');
                if (container) {
                    container.innerHTML = `
                        <div class="flex items-center justify-center px-4 py-12">
                            <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                                ⚠️ Le composant React n'a pas pu être chargé. Veuillez actualiser la page.
                                <br><a href="${basePath}/login" class="underline">Retour à la version classique</a>
                            </div>
                        </div>
                    `;
                }
            }
        });
    </script>
</body>
</html>