# Tests Unitaires - Système d'Authentification

Ce document décrit la suite de tests unitaires créée pour le système d'authentification du Parc National des Calanques.

## Structure des Tests

```
tests/
├── Auth/                          # Tests des composants d'authentification
│   ├── JwtServiceTest.php        # Tests du service JWT (27 tests)
│   ├── JwtMiddlewareTest.php     # Tests du middleware JWT (25 tests)
│   ├── AuthServiceTest.php      # Tests du service d'authentification (20 tests)
│   └── AuthIntegrationTest.php   # Tests d'intégration complets (12 tests)
├── Controllers/                   # Tests des contrôleurs
│   └── AuthControllerTest.php    # Tests du contrôleur d'auth (15 tests)
├── Models/                        # Tests des modèles
│   └── UserTest.php             # Tests du modèle User (33 tests)
├── Exceptions/                    # Tests des exceptions
│   └── AuthExceptionTest.php    # Tests des exceptions d'auth (7 tests)
└── TestHelper.php               # Utilitaires pour les tests
```

## Composants Testés

### 1. JwtService (27 tests)
**Fichier**: `tests/Auth/JwtServiceTest.php`

**Fonctionnalités testées**:
- Génération de paires de tokens (access + refresh)
- Validation de tokens JWT
- Extraction de données utilisateur depuis les tokens
- Gestion des tokens expirés
- Extraction des headers Bearer
- Méthodes de debug
- Sécurité (tokens avec clés différentes)

**Tests spécifiques**:
- `testGenerateTokenPairReturnsValidStructure()`: Vérification de la structure des tokens
- `testValidateTokenWithExpiredTokenThrowsException()`: Gestion des tokens expirés
- `testTokenWithDifferentSecretKeyFailsValidation()`: Sécurité des clés secrètes

### 2. JwtMiddleware (25 tests)
**Fichier**: `tests/Auth/JwtMiddlewareTest.php`

**Fonctionnalités testées**:
- Authentification via JWT
- Contrôle d'accès par rôle
- Gestion des permissions
- Authentification optionnelle
- Gestion des requêtes CORS
- Réponses JSON standardisées

**Tests spécifiques**:
- `testAuthenticateWithValidTokenReturnsUser()`: Authentification réussie
- `testRequireAdminCallsRequireRoleWithAdminRole()`: Contrôle d'accès administrateur
- `testCanWithRegularUserAndBasicPermissionsReturnsTrue()`: Système de permissions

### 3. AuthService (20 tests)
**Fichier**: `tests/Auth/AuthServiceTest.php`

**Fonctionnalités testées**:
- Connexion avec sessions et JWT
- Inscription d'utilisateurs
- Gestion des tokens remember
- Changement de mot de passe
- Contrôle d'accès par rôle
- Gestion des utilisateurs inactifs

**Tests spécifiques**:
- `testLoginWithValidCredentialsReturnsUser()`: Connexion standard
- `testLoginWithJwtReturnsTokens()`: Connexion avec JWT
- `testChangePasswordWithValidCredentialsSucceeds()`: Changement de mot de passe

### 4. AuthController (15 tests)
**Fichier**: `tests/Controllers/AuthControllerTest.php`

**Fonctionnalités testées**:
- Pages de connexion et inscription
- Validation des données d'inscription
- Gestion des erreurs de formulaire
- Redirection après authentification
- Déconnexion

**Tests spécifiques**:
- `testLoginWithValidPOSTCredentialsSucceeds()`: Traitement du formulaire de connexion
- `testValidateRegistrationDataWithValidDataReturnsCleanedData()`: Validation des données
- `testRegisterWithInvalidDataRendersErrors()`: Gestion des erreurs

### 5. User Model (33 tests)
**Fichier**: `tests/Models/UserTest.php`

**Fonctionnalités testées**:
- Propriétés et getters/setters
- Vérification de mot de passe
- Hachage sécurisé (Argon2ID)
- Rôles et permissions
- Gestion des cartes membres
- Sérialisation en tableau

**Tests spécifiques**:
- `testVerifyPassword()`: Vérification sécurisée des mots de passe
- `testHashPasswordUsesArgon2ID()`: Algorithme de hachage sécurisé
- `testIsCarteMembreValide()`: Validation des cartes membres

### 6. Tests d'Intégration (12 tests)
**Fichier**: `tests/Auth/AuthIntegrationTest.php`

**Scénarios testés**:
- Flux complet d'inscription/connexion
- Authentification JWT end-to-end
- Gestion des tokens remember
- Autorisation basée sur les rôles
- Changement de mot de passe
- Gestion des utilisateurs inactifs
- Pipeline d'authentification complet

**Test principal**:
- `testCompleteAuthenticationPipeline()`: Test du flux complet d'authentification

### 7. AuthException (7 tests)
**Fichier**: `tests/Exceptions/AuthExceptionTest.php`

**Fonctionnalités testées**:
- Constantes d'exception définies
- Création d'exceptions avec codes HTTP appropriés
- Unicité des messages d'erreur

## Configuration des Tests

### Composer.json
```json
{
    "autoload": {
        "psr-4": {
            "ParcCalanques\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

### PHPUnit.xml
```xml
<phpunit bootstrap="vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </source>
</phpunit>
```

## Exécution des Tests

### Tous les tests
```bash
php vendor/bin/phpunit tests/
```

### Tests spécifiques
```bash
# Tests JWT
php vendor/bin/phpunit tests/Auth/JwtServiceTest.php

# Tests du modèle User
php vendor/bin/phpunit tests/Models/UserTest.php

# Tests d'intégration
php vendor/bin/phpunit tests/Auth/AuthIntegrationTest.php
```

## Couverture de Test

### Fonctionnalités Couvertes
✅ **Authentification JWT complète**
- Génération et validation de tokens
- Gestion des expiration
- Sécurité des clés secrètes

✅ **Authentification par session**
- Connexion/déconnexion
- Tokens remember
- Gestion des sessions

✅ **Gestion des utilisateurs**
- CRUD utilisateurs
- Validation des données
- Hachage sécurisé des mots de passe

✅ **Contrôle d'accès**
- Système de rôles (user/admin)
- Permissions granulaires
- Middleware de protection

✅ **Sécurité**
- Validation des entrées
- Protection contre les attaques
- Gestion des erreurs

### Scénarios de Test
- **Cas nominaux**: Fonctionnement normal
- **Cas d'erreur**: Données invalides, utilisateurs inactifs
- **Cas limites**: Tokens expirés, permissions insuffisantes
- **Sécurité**: Tentatives d'accès non autorisé

## Bonnes Pratiques Implémentées

1. **Isolation des tests**: Chaque test est indépendant
2. **Mocking approprié**: Dépendances externes mockées
3. **Données de test réalistes**: Utilisation de vraies structures de données
4. **Tests de sécurité**: Vérification des failles potentielles
5. **Tests d'intégration**: Validation des flux complets
6. **Documentation claire**: Noms de tests descriptifs

## Maintenance et Extension

### Ajouter de nouveaux tests
1. Créer le fichier dans le répertoire approprié
2. Étendre la classe `TestCase`
3. Utiliser le namespace `Tests\`
4. Suivre les conventions de nommage

### Utilitaires de test
Le fichier `TestHelper.php` fournit des utilitaires pour:
- Création d'utilisateurs de test
- Génération de tokens JWT
- Mocking des requêtes HTTP
- Assertions personnalisées

## Résultats des Tests

**Total**: 139 tests avec plus de 400 assertions

**Statut**: ✅ Tous les tests passent

Cette suite de tests garantit la robustesse et la sécurité du système d'authentification du Parc National des Calanques, couvrant tous les aspects critiques de l'authentification, de l'autorisation et de la gestion des utilisateurs.