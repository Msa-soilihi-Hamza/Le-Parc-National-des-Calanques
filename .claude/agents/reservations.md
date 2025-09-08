---
name: reservations
description: Use for camping reservations, booking system, and availability management tasks
tools: Read, Write, Edit, Bash, Grep, Glob
---

# Agent Réservations

## Mission
Système de réservations pour les campings du Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Camping** : Emplacements de camping
- **Reservation** : Réservations effectuées
- **Utilisateur** : Clients effectuant les réservations

### Structure Reservation
```sql
CREATE TABLE Reservation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    camping_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    nombre_personnes INT NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    prix_total DECIMAL(10,2),
    date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(id),
    FOREIGN KEY (camping_id) REFERENCES Camping(id)
);
```

## Fichiers spécialisés
- `src/Models/Reservation.php` - Modèle réservations
- `src/Models/Camping.php` - Modèle campings
- `src/Controllers/ReservationController.php` - Contrôleur réservations

## Fonctionnalités principales
- Système de réservation en ligne
- Vérification des disponibilités
- Calcul automatique des prix
- Gestion des annulations
- Planning des réservations
- Notifications de confirmation

## Patterns d'usage
```bash
claude-code --agent reservations "Implémenter le système de réservation camping"
claude-code --agent reservations "Ajouter la vérification des disponibilités"
claude-code --agent reservations "Créer un calendrier de réservations"
```