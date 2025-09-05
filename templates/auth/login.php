<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Parc National des Calanques</title>
    <link href="<?= $GLOBALS['basePath'] ?? '' ?>/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Connexion</h1>
                <p>Accédez à votre compte</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $GLOBALS['basePath'] ?? '' ?>/login" class="auth-form">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required 
                        autocomplete="email"
                        placeholder="votre@email.com"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Votre mot de passe"
                    >
                </div>

                <div class="form-group form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Se connecter
                </button>
            </form>

            <div class="auth-links">
                <p>
                    <a href="<?= $GLOBALS['basePath'] ?? '' ?>/forgot-password">Mot de passe oublié ?</a>
                </p>
                <p>
                    Pas encore de compte ? 
                    <a href="<?= $GLOBALS['basePath'] ?? '' ?>/register">S'inscrire</a>
                </p>
            </div>
        </div>

        <div class="auth-info">
            <h2>Parc National des Calanques</h2>
            <p>Découvrez les merveilles naturelles des Calanques de Marseille et gérez votre expérience personnalisée.</p>
            
            <div class="demo-accounts">
                <h3>Comptes de démonstration</h3>
                <div class="demo-account">
                    <strong>Admin :</strong><br>
                    Email: admin@calanques.fr<br>
                    Mot de passe: admin123
                </div>
                <div class="demo-account">
                    <strong>Utilisateur :</strong><br>
                    Email: user@calanques.fr<br>
                    Mot de passe: user123
                </div>
            </div>
        </div>
    </div>

    <script src="<?= $GLOBALS['basePath'] ?? '' ?>/js/auth.js"></script>
</body>
</html>