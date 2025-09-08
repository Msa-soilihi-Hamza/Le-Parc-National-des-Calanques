# Guide d'Intégration JWT - Parc National des Calanques

## Vue d'ensemble

Le système d'authentification a été étendu pour supporter les tokens JWT en complément du système de sessions existant. Cette implémentation permet d'utiliser :

- **Sessions PHP** : Pour l'interface web traditionnelle
- **JWT Tokens** : Pour les API REST et applications mobiles

## Architecture

### Nouveaux Composants

```
src/Auth/
├── JwtService.php          # Service principal JWT
├── JwtMiddleware.php       # Middleware d'authentification JWT
├── AuthService.php         # Étendu avec support JWT
└── AuthBootstrap.php       # Étendu avec initialisation JWT

src/Controllers/
└── ApiController.php       # Contrôleur API avec endpoints JWT

api.php                     # Point d'entrée des API
```

## Utilisation

### 1. Connexion avec JWT

**Endpoint :** `POST /api/auth/login`

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@calanques.fr",
    "password": "admin123"
  }'
```

**Réponse :**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "email": "admin@calanques.fr",
    "role": "admin",
    "first_name": "Admin",
    "last_name": "Calanques"
  },
  "tokens": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "expires_at": 1694187234
  }
}
```

### 2. Utilisation du Token

Incluez le token dans l'en-tête `Authorization` :

```bash
curl -X GET http://localhost/api/auth/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### 3. Rafraîchissement du Token

**Endpoint :** `POST /api/auth/refresh`

```bash
curl -X POST http://localhost/api/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }'
```

## Endpoints API Disponibles

| Endpoint | Méthode | Auth | Description |
|----------|---------|------|-------------|
| `/api/auth/login` | POST | Non | Connexion avec JWT |
| `/api/auth/refresh` | POST | Non | Rafraîchir le token |
| `/api/auth/me` | GET | Oui | Informations utilisateur |
| `/api/auth/logout` | POST | Oui | Déconnexion |
| `/api/auth/validate` | POST | Non | Valider un token |
| `/api/profile` | GET/PUT | Oui | Profil utilisateur |
| `/api/users` | GET | Admin | Liste des utilisateurs |
| `/api/health` | GET | Non | Statut de l'API |

## Configuration JWT

### Variables d'Environnement

Créez un fichier `.env` (recommandé pour la production) :

```env
JWT_SECRET=votre-cle-secrete-tres-longue-et-complexe-ici
JWT_ISSUER=parc-calanques.com
```

### Durées des Tokens

- **Access Token** : 1 heure (3600 secondes)
- **Refresh Token** : 30 jours (2592000 secondes)

## Sécurité

### Meilleures Pratiques Implémentées

1. **Algorithme sécurisé** : HS256 avec clé secrète forte
2. **Expiration** : Tokens access courts, refresh tokens longs
3. **Validation stricte** : Vérification de signature, expiration, format
4. **CORS configuré** : Headers appropriés pour les requêtes cross-origin
5. **Content-Type strict** : Application/JSON obligatoire pour les API

### Headers de Sécurité

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

## Exemples d'Utilisation

### JavaScript/Frontend

```javascript
// Connexion
async function login(email, password) {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('access_token', data.tokens.access_token);
    localStorage.setItem('refresh_token', data.tokens.refresh_token);
  }
  
  return data;
}

// Requête authentifiée
async function getProfile() {
  const token = localStorage.getItem('access_token');
  
  const response = await fetch('/api/profile', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  return await response.json();
}

// Rafraîchissement automatique
async function refreshToken() {
  const refreshToken = localStorage.getItem('refresh_token');
  
  const response = await fetch('/api/auth/refresh', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ refresh_token: refreshToken })
  });
  
  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('access_token', data.tokens.access_token);
    localStorage.setItem('refresh_token', data.tokens.refresh_token);
  }
  
  return data;
}
```

### PHP (Client)

```php
// Connexion
$response = file_get_contents('http://localhost/api/auth/login', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'email' => 'user@example.com',
            'password' => 'password123'
        ])
    ]
]));

$data = json_decode($response, true);
$token = $data['tokens']['access_token'];

// Requête authentifiée
$profile = file_get_contents('http://localhost/api/profile', false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $token"
    ]
]));
```

## Intégration avec le Système Existant

### Double Authentification

Le système supporte maintenant deux modes d'authentification :

```php
use ParcCalanques\Auth\AuthBootstrap;

// Initialize system
AuthBootstrap::init();

// Web authentication (sessions)
$authService = AuthBootstrap::getAuthService();
$user = $authService->login($email, $password); // Session-based

// API authentication (JWT)
$result = $authService->loginWithJwt($email, $password); // JWT-based
```

### Middleware Usage

```php
use ParcCalanques\Auth\AuthBootstrap;

// JWT Middleware
$jwtMiddleware = AuthBootstrap::jwtMiddleware();
$user = $jwtMiddleware->authenticate(); // Throws exception if invalid

// Traditional Middleware
$middleware = AuthBootstrap::middleware();
$user = $middleware->requireAuthentication(); // Redirects if invalid
```

## Debugging

### Validation d'un Token

```bash
curl -X POST http://localhost/api/auth/validate \
  -H "Content-Type: application/json" \
  -d '{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }'
```

### Health Check

```bash
curl http://localhost/api/health
```

**Réponse :**
```json
{
  "status": "OK",
  "timestamp": "2024-09-08 15:30:00",
  "version": "1.0.0",
  "jwt_enabled": true
}
```

## Migration et Compatibilité

- **Compatibilité ascendante** : Le système de sessions existant continue de fonctionner
- **Migration progressive** : Vous pouvez migrer vers JWT progressivement
- **Double support** : Un utilisateur peut être connecté via session ET avoir un token JWT

## Limitations Actuelles

1. **Blacklist des tokens** : Non implémentée (placeholder existant)
2. **Rotation des clés** : Une seule clé secrète utilisée
3. **Cache Redis** : Non implémenté pour le stockage des tokens

## Prochaines Étapes Recommandées

1. **Configurer une clé secrète forte** en production
2. **Implémenter la blacklist des tokens** pour la déconnexion sécurisée
3. **Ajouter des tests unitaires** pour les nouvelles fonctionnalités
4. **Configurer un cache Redis** pour les performances
5. **Implémenter la rotation des clés** pour plus de sécurité

## Support et Débogage

En cas de problème :

1. Vérifiez les logs Apache/PHP
2. Testez avec `/api/health` pour confirmer que l'API fonctionne
3. Utilisez `/api/auth/validate` pour déboguer les tokens
4. Vérifiez les headers CORS si vous avez des problèmes cross-origin

Le système JWT est maintenant intégré et prêt à l'utilisation pour vos API et applications mobiles !