<?php
// Inclusion des composants DaisyUI
require_once __DIR__ . '/../components/Button.php';
require_once __DIR__ . '/../components/GoogleButton.php';
require_once __DIR__ . '/../components/Badge.php';
require_once __DIR__ . '/../components/Avatar.php';
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
<body class="bg-base-200">
    <!-- Navbar -->
    <?= renderParcNavbar(false) ?>
    
    <div class="flex items-center justify-center px-4 py-12" style="min-height: calc(100vh - 4rem);">
        <div class="max-w-md w-full">
            
            <!-- Formulaire de connexion -->
            <div class="card bg-white shadow-xl border-0 w-full">
                <div class="card-body p-8">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Connexion</h1>
                        <p class="text-gray-600">Accédez à votre compte</p>
                    </div>

                    <!-- Alertes -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire -->
                    <form method="POST" action="<?= $GLOBALS['basePath'] ?? '' ?>/login" class="space-y-6">
                        <div class="form-control">
                            <label class="label" for="email">
                                <span class="label-text font-medium">Adresse email</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required 
                                autocomplete="email"
                                placeholder="votre@email.com"
                                class="input input-bordered w-full focus:input-primary"
                            >
                        </div>

                        <div class="form-control">
                            <label class="label" for="password">
                                <span class="label-text font-medium">Mot de passe</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Votre mot de passe"
                                class="input input-bordered w-full focus:input-primary"
                            >
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="remember" value="1" class="checkbox checkbox-primary checkbox-sm">
                                <span class="label-text">Se souvenir de moi</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">Se connecter</button>
                    </form>

                    <!-- Divider -->
                    <div class="divider my-6">ou</div>

                    <!-- Google Button -->
                    <div class="text-center mb-6">
                        <?= renderGoogleButton('Se connecter avec Google', ['class' => 'w-full']) ?>
                    </div>

                    <!-- Comptes de démonstration -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3 text-center">🔐 Comptes de test</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" 
                                    onclick="fillLoginForm('admin@calanques.fr', 'admin123')"
                                    class="btn btn-outline btn-error btn-sm">
                                Admin
                            </button>
                            <button type="button" 
                                    onclick="fillLoginForm('user@calanques.fr', 'user123')"
                                    class="btn btn-outline btn-primary btn-sm">
                                Utilisateur
                            </button>
                        </div>
                    </div>

                    <!-- Liens -->
                    <div class="text-center space-y-2 text-sm">
                        <p>
                            <a href="<?= $GLOBALS['basePath'] ?? '' ?>/forgot-password" class="link link-primary">
                                Mot de passe oublié ?
                            </a>
                        </p>
                        <p class="text-gray-600">
                            Pas encore de compte ? 
                            <a href="<?= $GLOBALS['basePath'] ?? '' ?>/register" class="link link-primary font-medium">
                                S'inscrire
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        function fillLoginForm(email, password) {
            // Remplir les champs du formulaire
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Animation visuelle pour montrer que les champs sont remplis
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            // Ajouter une classe pour l'animation
            emailField.classList.add('input-success');
            passwordField.classList.add('input-success');
            
            // Retirer l'animation après un délai
            setTimeout(() => {
                emailField.classList.remove('input-success');
                passwordField.classList.remove('input-success');
            }, 1000);
        }
    </script>

    <style>
        .input-success {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
            transition: all 0.3s ease !important;
        }
    </style>
</body>
</html>