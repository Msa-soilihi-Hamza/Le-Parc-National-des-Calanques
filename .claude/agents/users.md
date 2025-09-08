# Agent Utilisateurs

## Mission
CRUD utilisateurs, gestion des profils et des rôles pour le Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Utilisateur** : Données des utilisateurs
- **Role** : Rôles et permissions

### Rôles système
- **admin** : Administration complète
- **gestionnaire** : Gestion des réservations et ressources
- **guide** : Accès aux informations des sentiers
- **visiteur** : Consultation et réservation

## Fichiers spécialisés
- `src/Models/User.php` - Modèle utilisateur
- `src/Models/UserRepository.php` - Repository utilisateur
- `src/Controllers/UserController.php` - Contrôleur utilisateur

## Fonctionnalités principales
- Création, lecture, mise à jour, suppression des utilisateurs
- Gestion des profils utilisateur
- Attribution et modification des rôles
- Recherche et filtrage des utilisateurs
- Validation des données utilisateur
- Gestion des préférences utilisateur

## Patterns d'usage
```bash
claude-code --agent users "Créer un CRUD complet pour les utilisateurs"
claude-code --agent users "Ajouter la gestion des préférences utilisateur"
claude-code --agent users "Implémenter un système de recherche utilisateur"
```