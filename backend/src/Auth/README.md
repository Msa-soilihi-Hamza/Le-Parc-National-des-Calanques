# 🔐 Domaine Auth - Authentification et Sécurité

## Vue d'ensemble

Ce domaine gère l'authentification, l'autorisation et la sécurité des utilisateurs du Parc National des Calanques.

## Architecture

```
Auth/
├── Controllers/        # Points d'entrée API
├── Services/          # Logique métier
├── Models/           # Entités et repositories
├── Middleware/       # Middleware de sécurité
├── Routes/           # Définition des routes
├── Tests/            # Tests unitaires
└── DTOs/             # Data Transfer Objects
```

## Fonctionnalités

### 🔑 Authentification
- **Login/Logout** : Connexion et déconnexion utilisateur
- **JWT** : Génération et validation de tokens JWT
- **Sessions** : Gestion des sessions utilisateur
- **Mot de passe oublié** : Récupération de mot de passe

### 🛡️ Sécurité
- **Middleware d'authentification** : Vérification des permissions
- **Hashage des mots de passe** : Sécurisation avec password_hash()
- **Validation des tokens** : Vérification JWT
- **Protection CSRF** : (à implémenter)

### 👤 Gestion Profil
- **Profil utilisateur** : Lecture et modification
- **Changement de mot de passe** : Mise à jour sécurisée
- **Vérification email** : Validation des comptes

## Utilisation

### Connexion
```php
POST /api/auth/login
{
    "email": "user@example.com",
    "password": "motdepasse",
    "remember_me": false
}
```

### Récupération du profil
```php
GET /api/auth/profile
Authorization: Bearer <jwt-token>
```

### Rafraîchir le token
```php
POST /api/auth/refresh
Authorization: Bearer <jwt-token>
```

## Services Principaux

- **AuthService** : Logique d'authentification
- **JwtService** : Gestion des tokens JWT
- **SessionService** : Gestion des sessions

## Middleware

- **AuthMiddleware** : Vérification de l'authentification
- **JwtMiddleware** : Validation des tokens JWT

## Tests

Lancer les tests du domaine Auth :
```bash
php vendor/bin/phpunit backend/src/Auth/Tests/
```

## Sécurité

- Mots de passe hashés avec `password_hash()`
- Tokens JWT avec expiration
- Validation stricte des entrées
- Protection contre les injections SQL
- Rate limiting (à implémenter)

## Configuration

Variables d'environnement requises :
```env
JWT_SECRET=your-secret-key
JWT_EXPIRATION=3600
SESSION_LIFETIME=86400
```