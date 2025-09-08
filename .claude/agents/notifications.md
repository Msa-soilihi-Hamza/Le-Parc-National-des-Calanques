# Agent Notifications

## Mission
Système de notifications et communications pour le Parc National des Calanques.

## Contexte BDD
### Tables principales
- **Notification** : Messages et alertes
- **Utilisateur** : Destinataires des notifications

### Structure Notification
```sql
CREATE TABLE Notification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'alerte', 'urgence', 'promotion') DEFAULT 'info',
    statut ENUM('non_lue', 'lue', 'archivee') DEFAULT 'non_lue',
    canal ENUM('web', 'email', 'sms') DEFAULT 'web',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_envoi TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(id)
);
```

## Fichiers spécialisés
- `src/Models/Notification.php` - Modèle notifications
- `src/Services/EmailService.php` - Service email
- `src/Services/SMSService.php` - Service SMS
- `src/Controllers/NotificationController.php` - Contrôleur notifications

## Technologies à utiliser
- **SMTP** : Envoi d'emails
- **API SMS** : Notifications SMS (Twilio, etc.)
- **WebSockets** : Notifications en temps réel
- **Queue System** : Traitement asynchrone

## Fonctionnalités principales
- Notifications web en temps réel
- Envoi d'emails automatiques
- Notifications SMS d'urgence
- Centre de notifications utilisateur
- Templates de messages
- Programmation d'envois
- Suivi des ouvertures

## Patterns d'usage
```bash
claude-code --agent notifications "Implémenter les notifications par email"
claude-code --agent notifications "Ajouter les alertes météo en temps réel"
claude-code --agent notifications "Créer un centre de notifications"
```