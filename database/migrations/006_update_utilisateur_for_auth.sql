-- Migration 006: Mise à jour de la table Utilisateur pour l'authentification
-- Date: 2025-09-05

-- Ajouter les colonnes manquantes pour l'authentification
ALTER TABLE Utilisateur 
ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE,
ADD COLUMN email_verified_at TIMESTAMP NULL,
ADD COLUMN remember_token VARCHAR(100) NULL,
ADD INDEX idx_remember_token (remember_token),
ADD INDEX idx_active (is_active);

-- Mettre à jour les rôles existants pour correspondre à notre système
UPDATE Utilisateur SET role = 'admin' WHERE role = 'administrateur';
UPDATE Utilisateur SET role = 'user' WHERE role = 'visiteur';

-- Modifier la colonne role pour utiliser ENUM avec nos valeurs
ALTER TABLE Utilisateur 
MODIFY COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user';

-- Renommer la colonne mot_passe en password_hash pour plus de clarté
ALTER TABLE Utilisateur 
CHANGE COLUMN mot_passe password_hash VARCHAR(255) NOT NULL;

-- Mettre à jour les mots de passe avec des hash sécurisés pour les comptes de test
-- Note: Ces mots de passe sont pour les tests uniquement
UPDATE Utilisateur 
SET password_hash = '$2y$12$LQv3c1yqBn/VYhRGEjqy9O.GvJTxv1w7O8C3rJGkfLLz9fKjJvKdO',
    email_verified_at = CURRENT_TIMESTAMP
WHERE email = 'admin@parc-calanques.fr';

-- Créer un utilisateur admin de test avec notre format
INSERT INTO Utilisateur (nom, prenom, email, password_hash, role, abonnement, is_active, email_verified_at) 
VALUES (
    'Calanques',
    'Admin',
    'admin@calanques.fr',
    '$2y$12$LQv3c1yqBn/VYhRGEjqy9O.GvJTxv1w7O8C3rJGkfLLz9fKjJvKdO', -- password: admin123
    'admin',
    FALSE,
    TRUE,
    CURRENT_TIMESTAMP
) ON DUPLICATE KEY UPDATE 
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    is_active = VALUES(is_active),
    email_verified_at = VALUES(email_verified_at);

-- Créer un utilisateur standard de test
INSERT INTO Utilisateur (nom, prenom, email, password_hash, role, abonnement, is_active, email_verified_at) 
VALUES (
    'Test',
    'Utilisateur',
    'user@calanques.fr',
    '$2y$12$LQv3c1yqBn/VYhRGEjqy9O.GvJTxv1w7O8C3rJGkfLLz9fKjJvKdO', -- password: user123
    'user',
    FALSE,
    TRUE,
    CURRENT_TIMESTAMP
) ON DUPLICATE KEY UPDATE 
    password_hash = VALUES(password_hash),
    role = VALUES(role),
    is_active = VALUES(is_active),
    email_verified_at = VALUES(email_verified_at);