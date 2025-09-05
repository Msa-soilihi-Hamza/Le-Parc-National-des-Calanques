-- Migration 003: Création de la table express_point_interet
-- Date: 2025-09-04
-- Objectif: Permettre d'associer plusieurs points d'intérêt à un sentier

CREATE TABLE express_point_interet (
    id_point_interet INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    type_point VARCHAR(100) NOT NULL COMMENT 'Type: panorama, cascade, grotte, faune, flore, patrimoine, etc.',
    coordonnees_gps VARCHAR(255) COMMENT 'Coordonnées GPS du point d''intérêt',
    altitude INT COMMENT 'Altitude en mètres',
    accessibilite VARCHAR(100) COMMENT 'Niveau d''accessibilité: facile, modéré, difficile',
    duree_arret_min INT DEFAULT 0 COMMENT 'Temps d''arrêt recommandé en minutes',
    photo_url VARCHAR(255) COMMENT 'URL vers la photo du point d''intérêt',
    ordre_sur_sentier INT DEFAULT 1 COMMENT 'Ordre d''apparition sur le sentier',
    id_sentier INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Clé étrangère vers la table Sentier
    FOREIGN KEY (id_sentier) REFERENCES Sentier(id_sentier) ON DELETE CASCADE,
    
    -- Index pour optimiser les requêtes
    INDEX idx_sentier (id_sentier),
    INDEX idx_type (type_point),
    INDEX idx_ordre (ordre_sur_sentier),
    
    -- Index composé pour ordonner les points par sentier
    INDEX idx_sentier_ordre (id_sentier, ordre_sur_sentier)
);