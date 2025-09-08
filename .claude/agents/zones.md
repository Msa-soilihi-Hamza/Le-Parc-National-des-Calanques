---
name: zones
description: Use for park zones, trails, points of interest, and mapping tasks
tools: Read, Write, Edit, Bash, Grep, Glob
---

# Agent Zones

## Mission
Gestion des zones du parc, sentiers et points d'intérêt pour le Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Zone** : Zones géographiques du parc
- **Sentier** : Chemins et sentiers de randonnée
- **Point_Interet** : Points d'intérêt touristique

### Structure Zone
```sql
CREATE TABLE Zone (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('maritime', 'terrestre', 'mixte'),
    superficie DECIMAL(10,2),
    coordonnees_gps JSON,
    niveau_difficulte ENUM('facile', 'modere', 'difficile'),
    statut ENUM('ouvert', 'ferme', 'limite') DEFAULT 'ouvert'
);
```

## Fichiers spécialisés
- `src/Models/Zone.php` - Modèle zones
- `src/Models/Sentier.php` - Modèle sentiers
- `src/Models/PointInteret.php` - Modèle points d'intérêt
- `src/Controllers/ZoneController.php` - Contrôleur zones

## Fonctionnalités principales
- Gestion CRUD des zones du parc
- Cartographie et géolocalisation
- Gestion des sentiers de randonnée
- Points d'intérêt touristique
- Niveaux de difficulté et accessibilité
- Statuts d'ouverture/fermeture des zones

## Patterns d'usage
```bash
claude-code --agent zones "Créer un système de cartographie des sentiers"
claude-code --agent zones "Ajouter la gestion des points d'intérêt"
claude-code --agent zones "Implémenter le calcul de distances entre zones"
```