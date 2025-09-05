# Guide du Système d'Authentification - Parc National des Calanques

## Vue d'ensemble

Ce système d'authentification robuste implémente les meilleures pratiques de sécurité PHP avec typage strict. Il comprend :

- **Deux rôles** : `user` et `admin`
- **Authentification sécurisée** avec hashage Argon2ID
- **Gestion des sessions** avec validation IP et User-Agent
- **Système "Se souvenir de moi"** avec tokens sécurisés
- **Middlewares d'autorisation** flexibles
- **Interface utilisateur moderne** et responsive

## Structure du Projet

```
src/
├── Auth/
│   ├── AuthBootstrap.php     # Initialisation du système
│   ├── AuthService.php       # Service principal d'authentification
│   ├── AuthMiddleware.php    # Middlewares d'autorisation
│   ├── AuthGuard.php         # Helper statique d'authentification
│   └── SessionManager.php    # Gestion des sessions
├── Models/
│   ├── User.php              # Modèle utilisateur
│   └── UserRepository.php    # Repository utilisateur
├── Controllers/
│   └── AuthController.php    # Contrôleur d'authentification
└── Exceptions/
    └── AuthException.php     # Exceptions personnalisées

templates/auth/
├── login.php                 # Formulaire de connexion
└── register.php              # Formulaire d'inscription

database/migrations/
└── 001_create_users_table.sql # Migration de la table users
```

## Installation et Configuration

### 1. Exécuter les migrations

```bash
php migrate.php
```

### 2. Configurer l'autoloader

Le fichier `autoload.php` est déjà configuré pour charger automatiquement les classes.

### 3. Initialiser l'authentification

```php
require_once 'autoload.php';
use ParcCalanques\Auth\AuthBootstrap;

// Initialise le système d'authentification
$authService = AuthBootstrap::init();
```

## Utilisation

### Authentification basique

```php
use ParcCalanques\Auth\AuthGuard;

// Vérifier si l'utilisateur est connecté
if (AuthGuard::check()) {
    $user = AuthGuard::user();
    echo "Bonjour " . $user->getFullName();
}

// Exiger une authentification
$user = AuthGuard::require(); // Lance une exception si non connecté

// Vérifier les rôles
if (AuthGuard::isAdmin()) {
    // Code pour les admins
}

// Exiger un rôle spécifique
$admin = AuthGuard::requireAdmin(); // Redirige si pas admin
```

### Utilisation avec middlewares

```php
use ParcCalanques\Auth\AuthBootstrap;

$middleware = AuthBootstrap::middleware();

// Page nécessitant une authentification
$user = $middleware->requireAuthentication();

// Page admin uniquement
$admin = $middleware->requireAdmin();

// Page pour visiteurs non connectés uniquement
$middleware->requireGuest();

// Authentification optionnelle
$user = $middleware->optionalAuth(); // Peut être null
```

### Connexion et inscription

```php
use ParcCalanques\Auth\AuthService;
use ParcCalanques\Exceptions\AuthException;

try {
    // Connexion
    $user = $authService->login($email, $password, $remember = false);
    
    // Inscription
    $user = $authService->register([
        'email' => 'user@example.com',
        'password' => 'motdepasse123',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'role' => 'user' // ou 'admin'
    ]);
    
} catch (AuthException $e) {
    echo "Erreur d'authentification : " . $e->getMessage();
}
```

### Permissions personnalisées

```php
use ParcCalanques\Auth\AuthGuard;

// Système de permissions
if (AuthGuard::can('manage_users')) {
    // Gérer les utilisateurs
}

if (AuthGuard::can('edit_user_profile', $userId)) {
    // Éditer le profil d'un utilisateur spécifique
}
```

## Fonctionnalités de Sécurité

### 1. Hashage des mots de passe
- **Algorithme** : Argon2ID (plus sécurisé que bcrypt)
- **Paramètres** : memory_cost=65536, time_cost=4, threads=3

### 2. Sessions sécurisées
- **Timeout** : 2 heures d'inactivité
- **Validation IP** : Détection de changement d'IP (optionnel)
- **User-Agent** : Validation du navigateur
- **Cookies sécurisés** : HttpOnly, Secure, SameSite=Strict

### 3. "Se souvenir de moi"
- **Tokens aléatoires** : 64 bytes, hashés en SHA-256
- **Expiration** : 30 jours
- **Renouvellement** : Nouveau token à chaque utilisation

### 4. Protection CSRF
- **Régénération de session** lors de la connexion
- **Validation des tokens** pour les formulaires

## Comptes de Démonstration

Le système crée automatiquement deux comptes de test :

**Administrateur :**
- Email: `admin@calanques.fr`
- Mot de passe: `admin123`
- Rôle: `admin`

**Utilisateur :**
- Email: `user@calanques.fr`
- Mot de passe: `user123`
- Rôle: `user`

## Routes Disponibles

- `/` - Redirection selon l'état d'authentification
- `/login` - Page de connexion
- `/register` - Page d'inscription
- `/logout` - Déconnexion
- `/dashboard` - Tableau de bord utilisateur
- `/admin` - Panel d'administration (admin uniquement)
- `/profile` - Profil utilisateur
- `/api/auth/check` - API : statut d'authentification
- `/api/auth/user` - API : données utilisateur

## Personnalisation

### Ajouter de nouveaux rôles

1. Modifier l'ENUM dans `001_create_users_table.sql`
2. Ajouter les constantes dans `User.php`
3. Mettre à jour les méthodes de vérification

### Permissions personnalisées

Modifier la méthode `can()` dans `AuthGuard.php` :

```php
public static function can(string $permission, ...$args): bool
{
    $user = self::user();
    
    if (!$user) {
        return false;
    }

    return match($permission) {
        'view_admin_panel' => $user->isAdmin(),
        'manage_users' => $user->isAdmin(),
        'your_custom_permission' => $user->isAdmin() || $user->hasSpecialFlag(),
        // ... autres permissions
        default => false
    };
}
```

### Templates personnalisés

Les templates sont dans `/templates/` et peuvent être modifiés :
- CSS personnalisé dans `/public/css/auth.css`
- JavaScript dans `/public/js/auth.js`

## Débogage et Logs

Le système utilise `error_log()` pour les événements de sécurité :
- Tentatives de connexion échouées
- Changements d'IP suspects
- Erreurs d'authentification

## Exemple d'Utilisation Complète

```php
<?php
require_once 'autoload.php';

use ParcCalanques\Auth\AuthBootstrap;
use ParcCalanques\Auth\AuthGuard;

// Initialisation
$authService = AuthBootstrap::init();

// Page nécessitant une authentification
try {
    $user = AuthGuard::require();
    
    echo "<h1>Bienvenue {$user->getFullName()}</h1>";
    echo "<p>Votre rôle : {$user->getRole()}</p>";
    
    if (AuthGuard::isAdmin()) {
        echo '<a href="/admin">Administration</a>';
    }
    
    echo '<a href="/logout">Déconnexion</a>';
    
} catch (AuthException $e) {
    // L'utilisateur sera redirigé vers /login
    // Cette exception ne devrait pas être attrapée normalement
}
?>
```

Ce système d'authentification offre une base solide et sécurisée pour votre application, avec une architecture modulaire permettant une extension facile selon vos besoins spécifiques.