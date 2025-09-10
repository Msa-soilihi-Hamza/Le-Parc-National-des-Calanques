<!DOCTYPE html>
<html lang="fr" data-theme="parc">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Parc National des Calanques</title>
    <link href="/Le-Parc-National-des-Calanques/public/css/output.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen font-sans">
    <div class="flex items-center justify-center min-h-screen px-4 py-12">
        <div class="card bg-base-100 shadow-xl max-w-md w-full">
            <div class="card-body p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-base-content mb-2">Inscription</h1>
                    <p class="text-base-content/60">Créez votre compte</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors) && is_array($errors)): ?>
                    <div class="alert alert-error mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $field => $message): ?>
                                <li><?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= $GLOBALS['basePath'] ?? '' ?>/register" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label" for="first_name">
                                <span class="label-text font-medium">Prénom</span>
                            </label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                required 
                                autocomplete="given-name"
                                placeholder="Votre prénom"
                                class="input input-bordered w-full"
                            >
                        </div>

                        <div class="form-control">
                            <label class="label" for="last_name">
                                <span class="label-text font-medium">Nom</span>
                            </label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                required 
                                autocomplete="family-name"
                                placeholder="Votre nom"
                                class="input input-bordered w-full"
                            >
                        </div>
                    </div>

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
                            class="input input-bordered w-full"
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
                            autocomplete="new-password"
                            placeholder="Choisissez un mot de passe"
                            minlength="8"
                            class="input input-bordered w-full"
                        >
                        <div class="bg-base-200 border border-base-300 rounded-lg p-3 mt-2">
                            <p class="text-sm font-medium text-base-content/60 mb-2">Le mot de passe doit contenir au moins :</p>
                            <ul class="text-xs text-base-content/60 space-y-1">
                                <li>• 8 caractères</li>
                                <li>• 1 lettre majuscule</li>
                                <li>• 1 lettre minuscule</li>
                                <li>• 1 chiffre</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label" for="password_confirmation">
                            <span class="label-text font-medium">Confirmer le mot de passe</span>
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password"
                            placeholder="Répétez votre mot de passe"
                            minlength="8"
                            class="input input-bordered w-full"
                        >
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="terms" value="1" required class="checkbox checkbox-primary">
                            <span class="label-text text-sm">
                                J'accepte les <a href="<?= $GLOBALS['basePath'] ?? '' ?>/terms" target="_blank" class="link link-primary">conditions d'utilisation</a>
                            </span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="newsletter" value="1" class="checkbox checkbox-primary">
                            <span class="label-text text-sm">
                                Je souhaite recevoir la newsletter
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-full">
                        Créer mon compte
                    </button>
                </form>

                <div class="divider my-6"></div>

                <div class="text-center">
                    <p class="text-sm text-base-content/60">
                        Déjà un compte ? 
                        <a href="<?= $GLOBALS['basePath'] ?? '' ?>/login" class="link link-primary font-medium">Se connecter</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>