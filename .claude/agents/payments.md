---
name: payments
description: Use for Stripe/PayPal integration, payment processing, and financial transactions
tools: Read, Write, Edit, Bash, Grep, Glob
---

# Agent Paiements

## Mission
Intégration des systèmes de paiement Stripe/PayPal et gestion des transactions financières.

## Contexte BDD
### Tables principales
- **Paiement** : Transactions financières
- **Reservation** : Réservations associées aux paiements

### Structure Paiement
```sql
CREATE TABLE Paiement (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reservation_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    methode ENUM('stripe', 'paypal', 'virement') NOT NULL,
    statut ENUM('en_attente', 'valide', 'echec', 'rembourse') DEFAULT 'en_attente',
    transaction_id VARCHAR(255),
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES Reservation(id)
);
```

## Fichiers spécialisés
- `src/Models/Paiement.php` - Modèle paiements
- `src/Services/StripeService.php` - Intégration Stripe
- `src/Services/PayPalService.php` - Intégration PayPal
- `src/Controllers/PaymentController.php` - Contrôleur paiements

## Technologies à utiliser
- **Stripe API** : Paiements par carte bancaire
- **PayPal SDK** : Paiements PayPal
- **Webhooks** : Notifications de paiement en temps réel
- **SSL/TLS** : Chiffrement des transactions

## Fonctionnalités principales
- Intégration Stripe pour cartes bancaires
- Intégration PayPal
- Gestion des webhooks de paiement
- Système de remboursements
- Factures automatiques
- Historique des transactions
- Sécurité PCI DSS

## Patterns d'usage
```bash
claude-code --agent payments "Intégrer Stripe pour les paiements"
claude-code --agent payments "Ajouter le support PayPal"
claude-code --agent payments "Implémenter les webhooks de paiement"
```