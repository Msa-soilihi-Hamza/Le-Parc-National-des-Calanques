-- =====================================================
-- MIGRATION 008: Ajout du système d'abonnements et réductions
-- Auteur: Claude Code
-- Date: 2025-09-11
-- =====================================================

USE `le-parc-national-des-calanques`;

-- Désactiver les vérifications de clés étrangères temporairement
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. MODIFICATION DE LA TABLE PAIEMENT
-- =====================================================

-- Ajouter le champ type_paiement et rendre id_reservation optionnel
ALTER TABLE Paiement 
ADD COLUMN type_paiement ENUM('reservation', 'abonnement', 'amende') NOT NULL DEFAULT 'reservation' AFTER id_reservation,
MODIFY COLUMN id_reservation INT NULL COMMENT 'Optionnel - NULL pour les paiements d''abonnement';

-- Ajouter un index pour le type de paiement
ALTER TABLE Paiement 
ADD INDEX idx_type_paiement (type_paiement);

-- =====================================================
-- 2. TABLE TYPE_ABONNEMENT
-- =====================================================

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
    
    -- Index
    INDEX idx_actif (actif),
    INDEX idx_prix (prix),
    INDEX idx_duree (duree_mois)
);

-- =====================================================
-- 3. TABLE ABONNEMENT_UTILISATEUR
-- =====================================================

CREATE TABLE Abonnement_Utilisateur (
    id_abonnement_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_type_abonnement INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('actif', 'expire', 'suspendu', 'annule') DEFAULT 'actif',
    id_paiement INT COMMENT 'Lien vers le paiement d''abonnement',
    renouvellement_auto BOOLEAN DEFAULT FALSE,
    notes TEXT COMMENT 'Notes administratives',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clés étrangères
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_type_abonnement) REFERENCES Type_Abonnement(id_type_abonnement) ON DELETE RESTRICT,
    FOREIGN KEY (id_paiement) REFERENCES Paiement(id_paiement) ON DELETE SET NULL,
    
    -- Index
    INDEX idx_utilisateur_actif (id_utilisateur, statut),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut),
    INDEX idx_renouvellement (renouvellement_auto),
    INDEX idx_paiement (id_paiement)
);

-- =====================================================
-- 4. TABLE REDUCTION
-- =====================================================

CREATE TABLE Reduction (
    id_reduction INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL COMMENT 'Nom de la réduction',
    description TEXT,
    type_reduction ENUM('pourcentage', 'montant_fixe') NOT NULL,
    valeur DECIMAL(10,2) NOT NULL COMMENT 'Valeur de la réduction',
    conditions JSON COMMENT 'Conditions d''application (durée séjour, saison, etc.)',
    date_debut DATE COMMENT 'Date de début de validité',
    date_fin DATE COMMENT 'Date de fin de validité',
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index
    INDEX idx_actif (actif),
    INDEX idx_type (type_reduction),
    INDEX idx_dates_validite (date_debut, date_fin),
    INDEX idx_valeur (valeur)
);

-- =====================================================
-- 5. TABLE ABONNEMENT_REDUCTION (Table de liaison)
-- =====================================================

CREATE TABLE Abonnement_Reduction (
    id_type_abonnement INT NOT NULL,
    id_reduction INT NOT NULL,
    priorite INT DEFAULT 0 COMMENT 'Ordre d''application des réductions',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id_type_abonnement, id_reduction),
    FOREIGN KEY (id_type_abonnement) REFERENCES Type_Abonnement(id_type_abonnement) ON DELETE CASCADE,
    FOREIGN KEY (id_reduction) REFERENCES Reduction(id_reduction) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_priorite (priorite)
);

-- =====================================================
-- 6. MODIFICATION DE LA TABLE RESERVATION
-- =====================================================

-- Ajouter les champs pour tracer les réductions appliquées
ALTER TABLE Reservation 
ADD COLUMN prix_base DECIMAL(10,2) COMMENT 'Prix avant réduction' AFTER statut,
ADD COLUMN reduction_appliquee DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Montant de la réduction',
ADD COLUMN id_reduction INT COMMENT 'Réduction appliquée',
ADD COLUMN prix_final DECIMAL(10,2) COMMENT 'Prix final après réduction';

-- Ajouter les index et contraintes
ALTER TABLE Reservation
ADD INDEX idx_reduction (id_reduction),
ADD INDEX idx_prix_final (prix_final);

-- =====================================================
-- 7. MODIFICATION DE LA TABLE UTILISATEUR
-- =====================================================

-- Supprimer l'ancien champ abonnement boolean (sera remplacé par les relations)
-- Note: On le garde pour la compatibilité, mais il sera déprécié
ALTER TABLE Utilisateur 
ADD COLUMN abonnement_legacy BOOLEAN DEFAULT FALSE COMMENT 'DEPRECATED - Utiliser Abonnement_Utilisateur',
CHANGE COLUMN abonnement abonnement_legacy BOOLEAN DEFAULT FALSE;

-- =====================================================
-- 8. FONCTIONS UTILITAIRES
-- =====================================================

-- Vue pour les abonnements actifs
CREATE VIEW Vue_Abonnements_Actifs AS
SELECT 
    au.id_abonnement_utilisateur,
    u.id_utilisateur,
    u.nom,
    u.prenom,
    u.email,
    ta.nom as type_abonnement,
    ta.reduction_pourcentage,
    au.date_debut,
    au.date_fin,
    au.statut,
    CASE 
        WHEN au.date_fin >= CURDATE() AND au.statut = 'actif' THEN 'ACTIF'
        WHEN au.date_fin < CURDATE() THEN 'EXPIRE'
        ELSE au.statut
    END as statut_reel
FROM Abonnement_Utilisateur au
JOIN Utilisateur u ON au.id_utilisateur = u.id_utilisateur
JOIN Type_Abonnement ta ON au.id_type_abonnement = ta.id_type_abonnement
WHERE au.statut IN ('actif', 'expire');

-- =====================================================
-- 9. DONNÉES D'EXEMPLE POUR LES TYPES D'ABONNEMENTS
-- =====================================================

INSERT INTO Type_Abonnement (nom, description, duree_mois, prix, reduction_pourcentage, avantages, actif) VALUES
('Mensuel Standard', 'Abonnement mensuel avec réductions sur les réservations', 1, 15.00, 10.00, 
 JSON_ARRAY('10% de réduction sur toutes les réservations', 'Accès prioritaire aux nouveaux campings'), true),

('Annuel Standard', 'Abonnement annuel avec réductions avantageuses', 12, 150.00, 15.00,
 JSON_ARRAY('15% de réduction sur toutes les réservations', 'Accès prioritaire', '2 mois gratuits par rapport au mensuel'), true),

('Famille', 'Abonnement famille (jusqu''à 4 personnes)', 12, 250.00, 20.00,
 JSON_ARRAY('20% de réduction sur toutes les réservations', 'Valable pour 4 personnes', 'Accès aux zones familiales'), true),

('Étudiant', 'Tarif étudiant avec justificatif', 12, 100.00, 15.00,
 JSON_ARRAY('15% de réduction sur toutes les réservations', 'Tarif préférentiel étudiant'), true);

-- =====================================================
-- 10. DONNÉES D'EXEMPLE POUR LES RÉDUCTIONS
-- =====================================================

INSERT INTO Reduction (nom, description, type_reduction, valeur, conditions, actif) VALUES
('Réduction Abonné Standard', 'Réduction de base pour les abonnés', 'pourcentage', 10.00, 
 JSON_OBJECT('min_duree_sejour', 1, 'applicable_weekend', true), true),

('Réduction Longue Durée', 'Réduction supplémentaire pour séjours de plus de 7 jours', 'pourcentage', 5.00,
 JSON_OBJECT('min_duree_sejour', 7, 'cumulable', true), true),

('Réduction Basse Saison', 'Réduction pendant la basse saison', 'montant_fixe', 20.00,
 JSON_OBJECT('saison', 'basse', 'mois', JSON_ARRAY(11, 12, 1, 2, 3)), true),

('Réduction Famille Nombreuse', 'Réduction pour les familles avec abonnement famille', 'pourcentage', 5.00,
 JSON_OBJECT('type_abonnement', 'famille', 'cumulable', true), true);

-- =====================================================
-- 11. LIAISON ABONNEMENTS-RÉDUCTIONS
-- =====================================================

-- Associer les réductions aux types d'abonnements
INSERT INTO Abonnement_Reduction (id_type_abonnement, id_reduction, priorite) VALUES
-- Abonnement Mensuel Standard
(1, 1, 1), -- Réduction abonné standard

-- Abonnement Annuel Standard  
(2, 1, 1), -- Réduction abonné standard
(2, 2, 2), -- Réduction longue durée

-- Abonnement Famille
(3, 1, 1), -- Réduction abonné standard
(3, 2, 2), -- Réduction longue durée  
(3, 4, 3), -- Réduction famille nombreuse

-- Abonnement Étudiant
(4, 1, 1), -- Réduction abonné standard
(4, 3, 2); -- Réduction basse saison

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 12. VÉRIFICATIONS FINALES
-- =====================================================

-- Vérifier les nouvelles tables
SHOW TABLES LIKE '%abonnement%';
SHOW TABLES LIKE '%reduction%';

-- Vérifier la structure de la table Paiement modifiée
DESCRIBE Paiement;

-- Afficher un résumé des abonnements créés
SELECT 
    ta.nom,
    ta.duree_mois,
    ta.prix,
    ta.reduction_pourcentage,
    COUNT(ar.id_reduction) as nb_reductions
FROM Type_Abonnement ta
LEFT JOIN Abonnement_Reduction ar ON ta.id_type_abonnement = ar.id_type_abonnement
GROUP BY ta.id_type_abonnement
ORDER BY ta.prix;

-- =====================================================
-- FIN DE LA MIGRATION 008
-- =====================================================