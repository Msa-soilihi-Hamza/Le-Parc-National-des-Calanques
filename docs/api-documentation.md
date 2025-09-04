# Documentation API - Parc National des Calanques

## Authentification

### POST /auth/login
Connexion utilisateur
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

### POST /auth/register
Inscription utilisateur
```json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "email": "jean.dupont@example.com",
  "password": "password"
}
```

## Utilisateurs

### GET /utilisateurs
Liste des utilisateurs (admin seulement)

### GET /utilisateurs/{id}
Profil d'un utilisateur

### PUT /utilisateurs/{id}
Modification du profil

## Sentiers

### GET /sentiers
Liste des sentiers disponibles

### GET /sentiers/{id}
Détails d'un sentier

### POST /sentiers
Création d'un sentier (admin/guide)

### PUT /sentiers/{id}
Modification d'un sentier

## Réservations

### POST /reservations
Nouvelle réservation
```json
{
  "id_sentier": 1,
  "date_visite": "2024-06-15",
  "nombre_personnes": 4
}
```

### GET /reservations
Réservations de l'utilisateur

### PUT /reservations/{id}
Modification/annulation réservation

## Notifications

### GET /notifications
Notifications de l'utilisateur

### PUT /notifications/{id}/read
Marquer comme lu

## Codes de réponse

- 200: Succès
- 201: Créé
- 400: Requête invalide
- 401: Non authentifié
- 403: Accès refusé
- 404: Non trouvé
- 500: Erreur serveur