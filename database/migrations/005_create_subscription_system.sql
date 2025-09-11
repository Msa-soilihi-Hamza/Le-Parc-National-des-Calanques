-- =====================================================
-- MIGRATION 005: Système d'abonnements
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables si elles existent
DROP TABLE IF EXISTS Abonnement_Utilisateur;
DROP TABLE IF EXISTS Type_Abonnement;

-- Table Type_Abonnement
CREATE TABLE Type_Abonnement (
    id_type_abonnement INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL COMMENT 'Ex: Mensuel, Annuel, Famille',
    description TEXT,
    duree_mois INT NOT NULL COMMENT 'Durée en mois',
    prix DECIMAL(10,2) NOT NULL COMMENT 'Prix de l''abonnement',
    reduction_pourcentage DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Réduction par défaut (%)',
    avantages JSON COMMENT 'Liste des avantages spécifiques',
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_actif (actif),
    INDEX idx_prix (prix),
    INDEX idx_duree (duree_mois)
);

-- Table Abonnement_Utilisateur
CREATE TABLE Abonnement_Utilisateur (
    id_abonnement_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_type_abonnement INT NOT NULL,
    id_paiement INT NULL COMMENT 'Référence vers le paiement',
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('actif', 'expire', 'suspendu', 'annule') DEFAULT 'actif',
    auto_renouvellement BOOLEAN DEFAULT FALSE,
    reduction_appliquee DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Réduction spéciale appliquée (%)',
    code_promo VARCHAR(50) NULL COMMENT 'Code promo utilisé',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_type_abonnement) REFERENCES Type_Abonnement(id_type_abonnement) ON DELETE RESTRICT,
    FOREIGN KEY (id_paiement) REFERENCES Paiement(id_paiement) ON DELETE SET NULL,
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_type_abonnement (id_type_abonnement),
    INDEX idx_statut (statut),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_paiement (id_paiement)
);

-- Insérer les types d'abonnement
INSERT INTO Type_Abonnement (nom, description, duree_mois, prix, reduction_pourcentage, avantages) VALUES
('Mensuel Basic', 'Abonnement mensuel avec avantages de base', 1, 15.00, 0.00, '["Accès prioritaire", "Newsletter"]'),
('Annuel Standard', 'Abonnement annuel avec réductions', 12, 150.00, 15.00, '["Accès prioritaire", "Réductions camping", "Visites guidées gratuites"]'),
('Famille Plus', 'Abonnement famille (2 adultes + enfants)', 12, 250.00, 20.00, '["Accès famille", "Activités enfants", "Camping gratuit 2 nuits"]');

SET FOREIGN_KEY_CHECKS = 1;