# 🏔️ Parc National des Calanques - Système d'Authentification

## 📧 **Fonctionnalité Principale : Vérification d'Email**

Système d'authentification complet avec vérification d'email obligatoire lors de l'inscription.

## 🚀 **Technologies Utilisées**

- **Backend** : PHP 8.3 (Architecture MVC)
- **Frontend** : React 18 + Tailwind CSS
- **Base de données** : MySQL
- **Email** : PHPMailer avec Gmail SMTP
- **Authentification** : JWT + Sessions
- **Build** : Webpack + PostCSS

## 📁 **Structure du Projet**

```
src/
├── Auth/                    # Système d'authentification
│   ├── AuthService.php      # Service principal d'auth
│   ├── JwtService.php       # Gestion des tokens JWT
│   └── SessionManager.php   # Gestion des sessions
├── Models/
│   ├── User.php            # Modèle utilisateur
│   └── UserRepository.php  # Accès aux données
├── Services/
│   └── EmailService.php    # Envoi d'emails (PHPMailer)
└── react/
    ├── components/
    │   ├── SignupForm.jsx   # Formulaire d'inscription
    │   └── LoginForm.jsx    # Formulaire de connexion
    └── App.jsx             # Application principale

database/migrations/        # Migrations de base de données
tests/                     # Tests unitaires
```

## ✨ **Fonctionnalités Implémentées**

### 🔐 **Authentification**
- [x] Inscription avec validation (12+ caractères)
- [x] Connexion sécurisée
- [x] Tokens JWT (Access + Refresh)
- [x] Remember me
- [x] Déconnexion sécurisée

### 📧 **Vérification d'Email**
- [x] Email automatique à l'inscription
- [x] Token de vérification sécurisé (24h)
- [x] Page de vérification élégante
- [x] Email de bienvenue post-vérification
- [x] Gestion des erreurs SMTP

### 🎨 **Interface Utilisateur**
- [x] Formulaires React avec validation temps réel
- [x] Barres de progression visuelles (rouge/vert)
- [x] Design responsive avec Tailwind CSS
- [x] Messages d'erreur contextuels
- [x] UX optimisée pour la vérification d'email

### 🛡️ **Sécurité**
- [x] Hachage Argon2ID des mots de passe
- [x] Validation CSRF
- [x] Tokens JWT sécurisés
- [x] Sessions HTTP-only
- [x] Validation des entrées

## 🔧 **Configuration**

### Base de Données
```sql
-- Table Utilisateur avec champs de vérification
ALTER TABLE Utilisateur ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE Utilisateur ADD COLUMN email_verification_token VARCHAR(255);
ALTER TABLE Utilisateur ADD COLUMN email_verification_expires_at DATETIME;
```

### Email SMTP
```php
// Configuration dans EmailService.php
Host: smtp.gmail.com
Port: 587
Username: hamza.msa-soilihi@laplateforme.io
Password: [App Password]
```

## 🎯 **Points Forts pour la Présentation**

1. **Architecture MVC propre** avec séparation des responsabilités
2. **Sécurité moderne** (JWT, Argon2ID, CSRF)
3. **UX soignée** avec validation temps réel
4. **Gestion d'erreur robuste** 
5. **Code maintenable** avec tests unitaires
6. **Email professionnel** avec templates HTML

## 🧪 **Tests**

```bash
# Lancer les tests
./vendor/bin/phpunit

# Build React
npm run build-react

# Build CSS
npm run build-css-prod
```

## 📝 **Démonstration**

1. **Inscription** → Saisie des données avec validation
2. **Email envoyé** → Vérification automatique de la boîte
3. **Activation** → Clic sur le lien de vérification
4. **Connexion** → Login avec les identifiants
5. **Session** → Navigation authentifiée

---

*Projet réalisé avec expertise technique et attention aux détails de sécurité.*