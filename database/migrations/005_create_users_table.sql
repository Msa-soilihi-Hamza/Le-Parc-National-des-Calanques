-- Migration: Création de la table users avec système d'authentification et rôles
-- Créé le: 2025-09-05

-- Création de la table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email(191)),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Insertion d'un utilisateur admin par défaut
INSERT INTO users (email, password_hash, role, first_name, last_name, is_active, email_verified_at) 
VALUES (
    'admin@calanques.fr',
    '$2y$12$LQv3c1yqBn/VYhRGEjqy9O.GvJTxv1w7O8C3rJGkfLLz9fKjJvKdO', -- password: admin123
    'admin',
    'Admin',
    'Calanques',
    TRUE,
    CURRENT_TIMESTAMP
);

-- Insertion d'un utilisateur standard pour test
INSERT INTO users (email, password_hash, role, first_name, last_name, is_active, email_verified_at) 
VALUES (
    'user@calanques.fr',
    '$2y$12$LQv3c1yqBn/VYhRGEjqy9O.GvJTxv1w7O8C3rJGkfLLz9fKjJvKdO', -- password: user123
    'user',
    'Utilisateur',
    'Test',
    TRUE,
    CURRENT_TIMESTAMP
);