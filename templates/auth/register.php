<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Parc National des Calanques</title>
    <link href="<?= $GLOBALS['basePath'] ?? '' ?>/css/auth.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Inscription</h1>
                <p>Créez votre compte</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors) && is_array($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $field => $message): ?>
                            <li><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $GLOBALS['basePath'] ?? '' ?>/register" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                            required 
                            autocomplete="given-name"
                            placeholder="Votre prénom"
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                            required 
                            autocomplete="family-name"
                            placeholder="Votre nom"
                        >
                    </div>
                </div>

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
                        autocomplete="new-password"
                        placeholder="Choisissez un mot de passe"
                        minlength="8"
                    >
                    <div class="password-requirements">
                        <p>Le mot de passe doit contenir au moins :</p>
                        <ul>
                            <li>8 caractères</li>
                            <li>1 lettre majuscule</li>
                            <li>1 lettre minuscule</li>
                            <li>1 chiffre</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="Répétez votre mot de passe"
                        minlength="8"
                    >
                </div>

                <div class="form-group form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" value="1" required>
                        <span class="checkmark"></span>
                        J'accepte les <a href="<?= $GLOBALS['basePath'] ?? '' ?>/terms" target="_blank">conditions d'utilisation</a>
                    </label>
                </div>

                <div class="form-group form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="newsletter" value="1">
                        <span class="checkmark"></span>
                        Je souhaite recevoir la newsletter
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    Créer mon compte
                </button>
            </form>

            <div class="auth-links">
                <p>
                    Déjà un compte ? 
                    <a href="<?= $GLOBALS['basePath'] ?? '' ?>/login">Se connecter</a>
                </p>
            </div>
        </div>

        <div class="auth-info">
            <h2>Rejoignez-nous</h2>
            <p>En créant un compte, vous pourrez :</p>
            <ul>
                <li>Planifier vos visites</li>
                <li>Sauvegarder vos itinéraires favoris</li>
                <li>Accéder à des contenus exclusifs</li>
                <li>Participer à la préservation du parc</li>
            </ul>
        </div>
    </div>

    <script src="<?= $GLOBALS['basePath'] ?? '' ?>/js/auth.js"></script>
</body>
</html>