# Agent Authentification

## Mission
Spécialisé dans l'authentification JWT, sécurité, gestion des sessions et middleware de sécurité pour le Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Utilisateur** : Gestion des comptes utilisateurs
- **Role** : Système de rôles et permissions

### Structure Utilisateur
```sql
CREATE TABLE Utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    role_id INT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    FOREIGN KEY (role_id) REFERENCES Role(id)
);
```

## Fichiers spécialisés
- `src/Auth/AuthService.php` - Service principal d'authentification
- `src/Auth/AuthGuard.php` - Protection des routes
- `src/Auth/AuthMiddleware.php` - Middleware d'authentification
- `src/Auth/SessionManager.php` - Gestion des sessions
- `src/Controllers/AuthController.php` - Contrôleur d'authentification
- `src/Exceptions/AuthException.php` - Exceptions d'authentification

## Technologies à utiliser
- **JWT** : JSON Web Tokens pour l'authentification stateless
- **bcrypt** : Hachage sécurisé des mots de passe
- **Session PHP** : Gestion des sessions côté serveur
- **CSRF Protection** : Protection contre les attaques CSRF

## Fonctionnalités principales
- Login/Logout utilisateur
- Génération et validation des tokens JWT
- Middleware d'authentification des routes
- Gestion des sessions utilisateur
- Récupération de mot de passe
- Validation et sécurisation des données
- Protection CSRF
- Rate limiting sur les tentatives de connexion

## Sécurité
- Validation stricte des entrées utilisateur
- Hachage sécurisé des mots de passe avec bcrypt
- Protection contre les attaques par force brute
- Validation des tokens JWT avec signature secrète
- Gestion sécurisée des cookies de session
- Headers de sécurité appropriés

## Patterns d'usage
```bash
claude-code --agent auth "Implémenter la récupération de mot de passe"
claude-code --agent auth "Ajouter un middleware de rate limiting"
claude-code --agent auth "Sécuriser l'endpoint de login"
```