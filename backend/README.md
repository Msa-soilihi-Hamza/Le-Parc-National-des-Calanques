# Backend - Parc National des Calanques

## 📁 Structure du Backend

```
backend/
├── public/
│   └── index.php              # Point d'entrée unique API
├── config/
│   ├── database.php           # Configuration base de données
│   └── bootstrap.php          # Bootstrap application
├── src/
│   ├── Auth/                  # 🔐 Domaine Authentification
│   │   ├── AuthBootstrap.php
│   │   ├── AuthService.php
│   │   ├── JwtService.php
│   │   ├── AuthGuard.php
│   │   ├── AuthMiddleware.php
│   │   ├── JwtMiddleware.php
│   │   └── SessionManager.php
│   ├── Users/                 # 👥 Domaine Utilisateurs
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   └── UserRepository.php
│   │   └── Controllers/
│   │       ├── AuthController.php
│   │       └── UserApiController.php
│   ├── Shared/                # Code partagé
│   │   ├── Services/
│   │   │   └── EmailService.php
│   │   ├── Utils/
│   │   │   └── EnvLoader.php
│   │   └── Exceptions/
│   │       └── AuthException.php
│   └── Core/                  # Framework basique
│       ├── Router.php
│       ├── ApiResponse.php
│       ├── Request.php
│       └── Controllers/
│           ├── ApiController.php
│           ├── AuthApiController.php
│           └── HealthApiController.php
└── routes/
    └── api.php                # Routes API centralisées
```

## 🚀 Utilisation

### Point d'entrée
```
http://localhost/Le-Parc-National-des-Calanques/backend/public/index.php
```

### Test API
```
http://localhost/Le-Parc-National-des-Calanques/backend/public/index.php?test=1
```

### Routes disponibles

#### Authentification
- `POST /api/auth/login` - Connexion utilisateur
- `POST /api/auth/register` - Inscription utilisateur
- `POST /api/auth/refresh` - Rafraîchir token JWT
- `POST /api/auth/logout` - Déconnexion
- `GET /api/auth/me` - Informations utilisateur connecté
- `POST /api/auth/validate` - Valider token

#### Utilisateurs
- `GET /api/profile` - Profil utilisateur
- `PUT /api/profile` - Modifier profil
- `GET /api/users` - Liste utilisateurs

#### Système
- `GET /api/health` - État de santé API

## 🎯 Avantages de cette structure

1. **Organisation claire** : Code organisé par domaine métier
2. **Navigation facile** : Chaque fonctionnalité a sa place
3. **Maintenance simple** : Structure logique et prévisible
4. **Évolutivité** : Facile d'ajouter de nouveaux domaines

## 📝 Pour ajouter un nouveau domaine

1. Créer le dossier `src/NouveauDomaine/`
2. Ajouter les sous-dossiers `Controllers/`, `Models/`, `Services/`
3. Ajouter les routes dans `routes/api.php`
4. Mettre à jour ce README

## 🔧 Configuration

Les configurations sont centralisées dans :
- `.env` - Variables d'environnement
- `config/database.php` - Base de données
- `config/bootstrap.php` - Bootstrap application