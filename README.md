# Parc National des Calanques

Application web de gestion du Parc National des Calanques développée en PHP avec une architecture MVC.

## Description

Cette application permet la gestion complète d'un parc national incluant :
- Gestion des utilisateurs et authentification
- Réservation de sentiers et zones de camping
- Gestion des ressources naturelles
- Système de notifications

## Structure du projet

- `config/` - Configuration de l'application et base de données
- `public/` - Point d'entrée et assets publics
- `src/` - Code source de l'application (Models, Controllers)
- `database/` - Migrations et données de test
- `tests/` - Tests unitaires et d'intégration
- `storage/` - Logs et fichiers temporaires
- `docs/` - Documentation du projet

## Installation

1. Cloner le repository
2. Configurer la base de données dans `config/database.php`
3. Exécuter les migrations dans `database/migrations/`
4. Lancer le serveur web

## Technologies utilisées

- PHP
- MySQL/
- Architecture MVC