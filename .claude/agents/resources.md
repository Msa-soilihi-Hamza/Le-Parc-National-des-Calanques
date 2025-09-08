# Agent Ressources

## Mission
Gestion de la faune, flore et ressources naturelles du Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Ressource_Naturelle** : Inventaire de la biodiversité
- **Zone** : Localisation des ressources

### Structure Ressource_Naturelle
```sql
CREATE TABLE Ressource_Naturelle (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    nom_scientifique VARCHAR(150),
    type ENUM('faune', 'flore', 'minerale') NOT NULL,
    description TEXT,
    zone_id INT,
    statut_conservation ENUM('non_menace', 'vulnerable', 'menace', 'protege'),
    saison_observation VARCHAR(50),
    photo_url VARCHAR(255),
    FOREIGN KEY (zone_id) REFERENCES Zone(id)
);
```

## Fichiers spécialisés
- `src/Models/RessourceNaturelle.php` - Modèle ressources
- `src/Controllers/ResourceController.php` - Contrôleur ressources
- `src/Services/BiodiversityService.php` - Services biodiversité

## Fonctionnalités principales
- Inventaire de la faune et flore
- Classification scientifique
- Statuts de conservation
- Cartographie des habitats
- Base de données photos/observations
- Rapports de biodiversité
- Suivi des espèces protégées

## Patterns d'usage
```bash
claude-code --agent resources "Créer l'inventaire de la faune marine"
claude-code --agent resources "Ajouter le suivi des espèces protégées"
claude-code --agent resources "Implémenter la cartographie des habitats"
```