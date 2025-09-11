-- =====================================================
-- MIGRATION 002: Système d'utilisateurs
-- Parc National des Calanques
-- =====================================================

USE `le-parc-national-des-calanques`;

SET FOREIGN_KEY_CHECKS = 0;

-- Supprimer la table si elle existe
DROP TABLE IF EXISTS Utilisateur;

-- Table Utilisateur
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
    
    FOREIGN KEY (id_role) REFERENCES Role(id_role) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_role_enum (role),
    INDEX idx_role_fk (id_role),
    INDEX idx_active (is_active),
    INDEX idx_remember_token (remember_token)
);

-- Insérer l'utilisateur admin par défaut
INSERT INTO Utilisateur (nom, prenom, email, password_hash, role, id_role, is_active) VALUES
('Admin', 'Système', 'admin@calanques.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, TRUE);

SET FOREIGN_KEY_CHECKS = 1;