-- =====================================================
-- MIGRATION COMPLETE: Initialisation de la base de données
-- Parc National des Calanques
-- Auteur: Claude Code
-- Date: 2025-09-11
-- =====================================================

-- Utilisation de la base de données existante
USE `le-parc-national-des-calanques`;

-- Désactiver les vérifications de clés étrangères temporairement
SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer les tables existantes si elles existent (ordre inverse des dépendances)
DROP TABLE IF EXISTS Abonnement_Utilisateur;
DROP TABLE IF EXISTS Type_Abonnement;
DROP TABLE IF EXISTS Notification;
DROP TABLE IF EXISTS Paiement;
DROP TABLE IF EXISTS Reservation;
DROP TABLE IF EXISTS Disponibilite_Camping;
DROP TABLE IF EXISTS Ressource_Naturelle;
DROP TABLE IF EXISTS Point_Interet;
DROP TABLE IF EXISTS Sentier;
DROP TABLE IF EXISTS Camping;
DROP TABLE IF EXISTS Utilisateur;
DROP TABLE IF EXISTS Role;
DROP TABLE IF EXISTS Zone;
DROP TABLE IF EXISTS users;

-- =====================================================
-- TABLE ZONE
-- =====================================================
CREATE TABLE Zone (
    id_zone INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    superficie FLOAT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE ROLE
-- =====================================================
CREATE TABLE Role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    nom_role VARCHAR(50) NOT NULL,
    description TEXT,
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE UTILISATEUR (1,1 avec ROLE)
-- =====================================================
CREATE TABLE Utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    id_role INT,
    abonnement BOOLEAN DEFAULT FALSE,
    carte_membre_numero VARCHAR(100),
    carte_membre_date_validite DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Role (optionnelle)
    FOREIGN KEY (id_role) REFERENCES Role(id_role) ON DELETE SET NULL,
    
    -- Index pour optimisation  
    INDEX idx_email (email),
    INDEX idx_role_enum (role),
    INDEX idx_role_fk (id_role),
    INDEX idx_active (is_active),
    INDEX idx_remember_token (remember_token)
);

-- =====================================================
-- TABLE SENTIER (1,1 avec ZONE)
-- =====================================================
CREATE TABLE Sentier (
    id_sentier INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    niveau_difficulte VARCHAR(50) NOT NULL,
    description TEXT,
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Zone
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_zone (id_zone),
    INDEX idx_difficulte (niveau_difficulte)
);

-- =====================================================
-- TABLE POINT_INTERET (1,1 avec ZONE)
-- =====================================================
CREATE TABLE Point_Interet (
    id_point INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    latitude FLOAT,
    longitude FLOAT,
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Zone
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_zone (id_zone),
    INDEX idx_coordinates (latitude, longitude)
);

-- =====================================================
-- TABLE CAMPING
-- =====================================================
CREATE TABLE Camping (
    id_camping INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    capacite INT DEFAULT 0,
    localisation VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE DISPONIBILITE_CAMPING (1,1 avec CAMPING)
-- =====================================================
CREATE TABLE Disponibilite_Camping (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_camping INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(50) DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Camping
    FOREIGN KEY (id_camping) REFERENCES Camping(id_camping) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_camping (id_camping),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut)
);

-- =====================================================
-- TABLE RESERVATION (1,1 avec UTILISATEUR et CAMPING)
-- =====================================================
CREATE TABLE Reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    date_reservation DATE NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(50) DEFAULT 'en_attente',
    id_utilisateur INT NOT NULL,
    id_camping INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clés étrangères
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_camping) REFERENCES Camping(id_camping) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_camping (id_camping),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut)
);

-- =====================================================
-- TABLE PAIEMENT (1,1 avec RESERVATION)
-- =====================================================
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
    
    -- Clé étrangère vers Reservation (optionnelle)
    FOREIGN KEY (id_reservation) REFERENCES Reservation(id_reservation) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_reservation (id_reservation),
    INDEX idx_type_paiement (type_paiement),
    INDEX idx_statut (statut),
    INDEX idx_mode (mode),
    INDEX idx_date_paiement (date_paiement),
    UNIQUE KEY unique_reference (reference_transaction)
);

-- =====================================================
-- TABLE TYPE_ABONNEMENT
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
-- TABLE ABONNEMENT_UTILISATEUR
-- =====================================================
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
    
    -- Clés étrangères
    FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_type_abonnement) REFERENCES Type_Abonnement(id_type_abonnement) ON DELETE RESTRICT,
    FOREIGN KEY (id_paiement) REFERENCES Paiement(id_paiement) ON DELETE SET NULL,
    
    -- Index
    INDEX idx_utilisateur (id_utilisateur),
    INDEX idx_type_abonnement (id_type_abonnement),
    INDEX idx_statut (statut),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_paiement (id_paiement),
    
    -- Contrainte unique pour éviter les doublons actifs
    UNIQUE KEY unique_utilisateur_actif (id_utilisateur, id_type_abonnement, statut)
);

-- =====================================================
-- TABLE RESSOURCE_NATURELLE (1,1 avec ZONE)
-- =====================================================
CREATE TABLE Ressource_Naturelle (
    id_ressource INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    etat VARCHAR(100),
    id_zone INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Zone
    FOREIGN KEY (id_zone) REFERENCES Zone(id_zone) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_zone (id_zone),
    INDEX idx_type (type),
    INDEX idx_etat (etat)
);

-- =====================================================
-- TABLE NOTIFICATION (1,1 avec UTILISATEUR)
-- =====================================================
CREATE TABLE Notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT,
    date DATE NOT NULL,
    destinataire INT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers Utilisateur
    FOREIGN KEY (destinataire) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
    
    -- Index
    INDEX idx_destinataire (destinataire),
    INDEX idx_date (date),
    INDEX idx_lu (lu)
);

-- =====================================================
-- INSERTION DE DONNÉES DE TEST
-- =====================================================

-- Insérer des rôles par défaut
INSERT INTO Role (nom_role, description, permissions) VALUES
('admin', 'Administrateur du système', 'all'),
('guide', 'Guide du parc', 'view_zones,manage_reservations'),
('user', 'Utilisateur standard', 'view_zones,make_reservations');

-- Insérer quelques zones
INSERT INTO Zone (nom, superficie, description) VALUES
('Calanque de Sormiou', 150.5, 'Une des plus belles calanques du parc'),
('Calanque de Morgiou', 120.3, 'Calanque avec port de pêche traditionnel'),
('Calanque d''En-Vau', 95.7, 'Calanque aux eaux turquoise'),
('Massif des Calanques', 8500.0, 'Zone principale du parc national');

-- Insérer des types d'abonnement
INSERT INTO Type_Abonnement (nom, description, duree_mois, prix, reduction_pourcentage, avantages) VALUES
('Mensuel Basic', 'Abonnement mensuel avec avantages de base', 1, 15.00, 0.00, '["Accès prioritaire", "Newsletter"]'),
('Annuel Standard', 'Abonnement annuel avec réductions', 12, 150.00, 15.00, '["Accès prioritaire", "Réductions camping", "Visites guidées gratuites"]'),
('Famille Plus', 'Abonnement famille (2 adultes + enfants)', 12, 250.00, 20.00, '["Accès famille", "Activités enfants", "Camping gratuit 2 nuits"]');

-- Insérer un utilisateur admin par défaut
INSERT INTO Utilisateur (nom, prenom, email, password_hash, role, id_role, is_active) VALUES
('Admin', 'Système', 'admin@calanques.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, TRUE);

-- Insérer quelques campings
INSERT INTO Camping (nom, capacite, localisation) VALUES
('Camping Sormiou', 50, 'Calanque de Sormiou'),
('Camping Morgiou', 35, 'Calanque de Morgiou'),
('Camping En-Vau', 25, 'Calanque d''En-Vau');

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- VÉRIFICATION DES CONTRAINTES
-- =====================================================

-- Afficher toutes les relations créées
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'le-parc-national-des-calanques'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- =====================================================
-- INFORMATIONS SUR LES TABLES CRÉÉES
-- =====================================================
SHOW TABLES;

-- Message de confirmation
SELECT 'Base de données créée avec succès ! Toutes les tables ont été initialisées.' AS Status;