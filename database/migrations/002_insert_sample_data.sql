-- Migration 002: Insertion de données d'exemple
-- Date: 2025-09-04

-- Insertion de zones d'exemple
INSERT INTO Zone (nom, superficie, description) VALUES
('Zone des Calanques', 520.5, 'Zone principale comprenant les calanques de Marseille'),
('Zone de Port-Miou', 145.2, 'Calanque de Port-Miou avec ses falaises calcaires'),
('Zone de Cassis', 280.7, 'Secteur de Cassis incluant plusieurs calanques'),
('Zone de La Ciotat', 195.3, 'Zone protégée autour de La Ciotat');

-- Insertion d'utilisateurs d'exemple
INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, role, abonnement, carte_membre_numero, carte_membre_date_validite) VALUES
('Admin', 'Système', 'admin@parc-calanques.fr', '$2y$10$example_hash_admin', 'administrateur', FALSE, NULL, NULL),
('Dupont', 'Marie', 'marie.dupont@email.com', '$2y$10$example_hash_marie', 'visiteur', TRUE, 'CM2025001', '2025-12-31'),
('Martin', 'Pierre', 'pierre.martin@email.com', '$2y$10$example_hash_pierre', 'visiteur', FALSE, NULL, NULL),
('Bernard', 'Sophie', 'sophie.bernard@email.com', '$2y$10$example_hash_sophie', 'visiteur', TRUE, 'CM2025002', '2025-06-30');

-- Insertion de sentiers d'exemple
INSERT INTO Sentier (nom, niveau_difficulte, description, points_interet, coordonnees, id_zone) VALUES
('Sentier des Calanques', 'moyen', 'Parcours panoramique le long des calanques avec vues exceptionnelles', 'Belvédère, Faune marine, Flore méditerranéenne', 'POINT(5.4474 43.2109)', 1),
('Chemin de Port-Miou', 'facile', 'Promenade familiale dans la calanque de Port-Miou', 'Port de plaisance, Carrières antiques', 'POINT(5.5186 43.2151)', 2),
('Randonnée de Cassis', 'difficile', 'Sentier exigeant avec dénivelé important', 'Sommet panoramique, Végétation endémique', 'POINT(5.5380 43.2143)', 3),
('Circuit de La Ciotat', 'moyen', 'Boucle découverte de la biodiversité locale', 'Observation ornithologique, Géologie', 'POINT(5.6084 43.1748)', 4);

-- Insertion de campings d'exemple
INSERT INTO Camping (nom, capacite, localisation) VALUES
('Camping des Pins', 50, 'Route des Calanques - Marseille'),
('Bivouac Port-Miou', 20, 'Calanque de Port-Miou - Cassis'),
('Aire naturelle Cassis', 30, 'Collines de Cassis'),
('Camping La Ciotat', 40, 'Baie de La Ciotat');

-- Insertion de disponibilités camping
INSERT INTO Disponibilite_Camping (id_camping, date_debut, date_fin, statut) VALUES
(1, '2025-09-01', '2025-09-30', 'disponible'),
(1, '2025-10-01', '2025-10-31', 'disponible'),
(2, '2025-09-01', '2025-09-15', 'indisponible'),
(2, '2025-09-16', '2025-09-30', 'disponible'),
(3, '2025-09-01', '2025-09-30', 'disponible'),
(4, '2025-09-01', '2025-09-30', 'disponible');

-- Insertion de réservations d'exemple
INSERT INTO Reservation (date_reservation, date_debut, date_fin, statut, id_utilisateur, id_camping) VALUES
('2025-09-01', '2025-09-15', '2025-09-17', 'confirmee', 2, 1),
('2025-09-02', '2025-09-20', '2025-09-22', 'en_attente', 3, 3),
('2025-09-03', '2025-09-25', '2025-09-27', 'confirmee', 4, 4);

-- Insertion de ressources naturelles
INSERT INTO Ressource_Naturelle (type, nom, etat, id_zone) VALUES
('animal', 'Goéland leucophée', 'bon', 1),
('animal', 'Lézard des murailles', 'bon', 1),
('plante', 'Pin d\'Alep', 'moyen', 1),
('plante', 'Thym de Provence', 'bon', 2),
('animal', 'Faucon pèlerin', 'critique', 3),
('plante', 'Lavande sauvage', 'bon', 3),
('autre', 'Formation calcaire', 'bon', 4),
('animal', 'Posidonie océanique', 'moyen', 4);

-- Insertion de notifications d'exemple
INSERT INTO Notification (titre, contenu, date, destinataire, lu) VALUES
('Confirmation de réservation', 'Votre réservation pour le camping des Pins est confirmée du 15 au 17 septembre 2025.', '2025-09-01', 2, TRUE),
('Nouvelle réglementation', 'Mise en place de nouvelles règles de protection dans la zone des Calanques.', '2025-09-02', 2, FALSE),
('Réservation en attente', 'Votre demande de réservation est en cours de traitement.', '2025-09-02', 3, FALSE),
('Information météo', 'Conditions météorologiques favorables prévues ce week-end.', '2025-09-03', 4, TRUE);