-- =====================================================
-- MIGRATION 004: Système de paiement
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer la table si elle existe
DROP TABLE IF EXISTS Paiement;

-- Table Paiement
CREATE TABLE Paiement (
    id_paiement INT AUTO_INCREMENT PRIMARY KEY,
    id_reservation INT NULL COMMENT 'Optionnel - NULL pour les paiements d''abonnement',
    type_paiement ENUM('reservation', 'abonnement', 'amende') NOT NULL DEFAULT 'reservation',
    montant FLOAT NOT NULL,
    mode VARCHAR(50) NOT NULL COMMENT 'ex: carte, paypal, especes',
    statut VARCHAR(50) DEFAULT 'en_attente' COMMENT 'paye, echec, rembourse',
    date_paiement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reference_transaction VARCHAR(100) COMMENT 'identifiant du paiement',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_reservation) REFERENCES Reservation(id_reservation) ON DELETE CASCADE,
    INDEX idx_reservation (id_reservation),
    INDEX idx_type_paiement (type_paiement),
    INDEX idx_statut (statut),
    INDEX idx_mode (mode),
    INDEX idx_date_paiement (date_paiement),
    UNIQUE KEY unique_reference (reference_transaction)
);

SET FOREIGN_KEY_CHECKS = 1;