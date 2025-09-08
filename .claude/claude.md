# Configuration Claude Code - Parc National des Calanques

## Architecture Agents

Ce projet utilise un système d'agents spécialisés pour organiser le développement par domaine fonctionnel.

### Utilisation
```bash
claude-code --agent [nom_agent] "votre demande"
```

### Agents disponibles

#### 🔐 Agent `auth`
**Mission** : Authentification JWT, sécurité, middleware  
**Tables** : Utilisateur, Role  
**Focus** : JWT, sessions, sécurité, guards  

#### 👥 Agent `users`  
**Mission** : CRUD utilisateurs, profils, rôles  
**Tables** : Utilisateur, Role  
**Focus** : Gestion utilisateurs, permissions  

#### 🗺️ Agent `zones`
**Mission** : Zones du parc, sentiers, points d'intérêt  
**Tables** : Zone, Sentier, Point_Interet  
**Focus** : Cartographie, géolocalisation  

#### 🏕️ Agent `reservations`
**Mission** : Réservations camping  
**Tables** : Camping, Reservation, Utilisateur  
**Focus** : Planning, disponibilités  

#### 💳 Agent `payments`
**Mission** : Paiements Stripe/PayPal  
**Tables** : Paiement, Reservation  
**Focus** : Transactions, webhooks, facturation  

#### 🌿 Agent `resources`
**Mission** : Faune, flore, biodiversité  
**Tables** : Ressource_Naturelle, Zone  
**Focus** : Conservation, inventaire, habitats  

#### 🔔 Agent `notifications`
**Mission** : Notifications, alertes  
**Tables** : Notification, Utilisateur  
**Focus** : Email, SMS, temps réel  

## Base de données

### Tables principales
- `Utilisateur` - Comptes utilisateurs
- `Role` - Rôles et permissions  
- `Zone` - Zones géographiques
- `Sentier` - Sentiers de randonnée
- `Point_Interet` - Points touristiques
- `Camping` - Emplacements camping
- `Reservation` - Réservations
- `Paiement` - Transactions
- `Ressource_Naturelle` - Biodiversité
- `Notification` - Messages/alertes

## Technologies
- **Backend** : PHP (Architecture MVC)
- **Base de données** : MySQL
- **Authentification** : JWT + Sessions
- **Paiements** : Stripe, PayPal
- **Notifications** : SMTP, WebSockets

## Structure du projet
```
src/
├── Auth/          # Authentification et sécurité
├── Models/        # Modèles de données
├── Controllers/   # Contrôleurs MVC
└── Services/      # Services métier
```