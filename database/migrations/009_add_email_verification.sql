-- Migration pour ajouter la vérification d'email
-- Ajoute les champs nécessaires pour la vérification d'email

ALTER TABLE Utilisateur 
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER email,
ADD COLUMN email_verification_token VARCHAR(255) NULL AFTER email_verified,
ADD COLUMN email_verification_expires_at DATETIME NULL AFTER email_verification_token;

-- Créer un index sur le token de vérification pour des recherches rapides
CREATE INDEX idx_verification_token ON Utilisateur(email_verification_token);

-- Mettre à jour les utilisateurs existants (s'il y en a) pour qu'ils soient considérés comme vérifiés
UPDATE Utilisateur SET email_verified = TRUE WHERE email_verification_token IS NULL;