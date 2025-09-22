-- Données complètes des sentiers du Parc National des Calanques
-- Basé sur les sentiers les plus emblématiques et populaires

-- Supprimer les données existantes pour repartir à zéro
DELETE FROM Sentier;

-- Réinitialiser l'auto-increment
ALTER TABLE Sentier AUTO_INCREMENT = 1;

-- Insérer tous les sentiers du Parc National des Calanques
INSERT INTO Sentier (nom, niveau_difficulte, description, id_zone, created_at, updated_at) VALUES

-- ZONE 1: Calanques de Marseille (Sormiou, Morgiou, Sugiton)
(
    'Calanque de Sormiou - Accès principal', 
    'facile', 
    'Sentier principal menant à la calanque de Sormiou. Accès facile depuis le col de Sormiou. Parfait pour les familles et les débutants. Plage de sable et eaux turquoise.',
    1, 
    NOW(), 
    NOW()
),
(
    'Calanque de Sormiou - Circuit des crêtes', 
    'moyen', 
    'Circuit panoramique sur les crêtes dominant Sormiou. Vue exceptionnelle sur la Méditerranée et les îles du Frioul. Passage par le col de Sormiou.',
    1, 
    NOW(), 
    NOW()
),
(
    'Calanque de Morgiou - Port de pêche', 
    'facile', 
    'Accès facile au charmant port de pêche de Morgiou. Sentier aménagé avec escaliers. Découverte de l\'histoire maritime et des cabanons traditionnels.',
    1, 
    NOW(), 
    NOW()
),
(
    'Calanque de Morgiou - Falaises', 
    'difficile', 
    'Sentier technique le long des falaises de Morgiou. Réservé aux randonneurs expérimentés. Passages exposés et vue spectaculaire sur la mer.',
    1, 
    NOW(), 
    NOW()
),
(
    'Calanque de Sugiton', 
    'moyen', 
    'Sentier vers la calanque de Sugiton, l\'une des plus belles du parc. Passage par la garrigue méditerranéenne. Eaux cristallines et rochers calcaires.',
    1, 
    NOW(), 
    NOW()
),
(
    'Sentier des Goudes', 
    'facile', 
    'Promenade côtière depuis le village des Goudes. Sentier plat le long de la mer. Idéal pour observer la faune marine et profiter des points de vue.',
    1, 
    NOW(), 
    NOW()
),

-- ZONE 2: Calanques de Cassis (En-Vau, Port-Pin, Port-Miou)
(
    'Calanque d\'En-Vau - Descente', 
    'difficile', 
    'L\'un des sentiers les plus spectaculaires ! Descente technique vers la plus belle calanque. Eaux cristallines et falaises vertigineuses. Très fréquenté en été.',
    2, 
    NOW(), 
    NOW()
),
(
    'Calanque d\'En-Vau - Belvédère', 
    'moyen', 
    'Montée jusqu\'au point de vue dominant En-Vau. Panorama exceptionnel sur les falaises blanches. Accessible aux randonneurs en bonne condition physique.',
    2, 
    NOW(), 
    NOW()
),
(
    'Calanque de Port-Pin', 
    'facile', 
    'Accès facile à la calanque de Port-Pin. Sentier court et ombragé. Plage de sable fin et eaux peu profondes, parfait pour les enfants.',
    2, 
    NOW(), 
    NOW()
),
(
    'Calanque de Port-Miou', 
    'facile', 
    'Sentier vers Port-Miou, ancien port de pêche. Accès le plus facile depuis Cassis. Découverte de l\'histoire maritime et des anciens entrepôts.',
    2, 
    NOW(), 
    NOW()
),
(
    'Sentier des Crêtes - Cassis', 
    'difficile', 
    'Randonnée sur les crêtes entre Cassis et En-Vau. Vue panoramique sur toute la côte. Sentier technique avec passages exposés.',
    2, 
    NOW(), 
    NOW()
),

-- ZONE 3: Calanques de La Ciotat (Figuerolles, Mugel)
(
    'Calanque de Figuerolles', 
    'moyen', 
    'Sentier vers la calanque de Figuerolles depuis La Ciotat. Passage par la garrigue et les pinèdes. Eaux turquoise et rochers calcaires.',
    3, 
    NOW(), 
    NOW()
),
(
    'Calanque de Mugel', 
    'facile', 
    'Accès facile à la calanque de Mugel. Sentier aménagé depuis le parking. Plage de galets et eaux cristallines. Idéal pour les familles.',
    3, 
    NOW(), 
    NOW()
),
(
    'Sentier du Cap Canaille', 
    'moyen', 
    'Randonnée sur le Cap Canaille, les plus hautes falaises maritimes d\'Europe. Vue exceptionnelle sur La Ciotat et les calanques.',
    3, 
    NOW(), 
    NOW()
),
(
    'Circuit des Calanques de La Ciotat', 
    'difficile', 
    'Grande randonnée reliant toutes les calanques de La Ciotat. Itinéraire complet pour découvrir la diversité du littoral.',
    3, 
    NOW(), 
    NOW()
),

-- ZONE 4: Massif des Calanques (Sentiers transversaux et découverte)
(
    'Grande Traversée des Calanques', 
    'difficile', 
    'Randonnée de plusieurs jours à travers tout le massif. Itinéraire pour randonneurs très expérimentés. Découverte complète du parc national.',
    4, 
    NOW(), 
    NOW()
),
(
    'Sentier Botanique du Massif', 
    'moyen', 
    'Circuit éducatif à travers la flore typique des Calanques. Panneaux explicatifs sur la végétation méditerranéenne et les espèces endémiques.',
    4, 
    NOW(), 
    NOW()
),
(
    'Chemin des Douaniers', 
    'facile', 
    'Ancien chemin de surveillance des douanes. Parcours panoramique le long de la côte. Histoire et nature au rendez-vous.',
    4, 
    NOW(), 
    NOW()
),
(
    'Sentier de la Grotte Cosquer', 
    'moyen', 
    'Approche de la célèbre grotte Cosquer (accès interdit). Découverte géologique et explications sur cette merveille préhistorique unique.',
    4, 
    NOW(), 
    NOW()
),
(
    'Circuit des Sources', 
    'moyen', 
    'Randonnée à la découverte des sources et points d\'eau du massif. Découverte de la géologie et de l\'hydrologie des Calanques.',
    4, 
    NOW(), 
    NOW()
),
(
    'Sentier des Oiseaux', 
    'facile', 
    'Circuit d\'observation ornithologique. Découverte de la faune aviaire des Calanques. Idéal pour les amoureux de la nature.',
    4, 
    NOW(), 
    NOW()
),
(
    'Randonnée Nocturne', 
    'moyen', 
    'Sentier spécialement aménagé pour les randonnées nocturnes. Découverte de la vie nocturne du massif et observation des étoiles.',
    4, 
    NOW(), 
    NOW()
),
(
    'Circuit des Anciens', 
    'facile', 
    'Sentier historique retraçant l\'occupation humaine des Calanques. Découverte des vestiges archéologiques et de l\'histoire locale.',
    4, 
    NOW(), 
    NOW()
),
(
    'Sentier des Géologues', 
    'difficile', 
    'Randonnée technique pour découvrir la géologie complexe des Calanques. Passages par les formations rocheuses les plus remarquables.',
    4, 
    NOW(), 
    NOW()
),
(
    'Circuit de la Biodiversité', 
    'moyen', 
    'Sentier éducatif sur la biodiversité exceptionnelle des Calanques. Découverte des espèces endémiques et de la flore méditerranéenne.',
    4, 
    NOW(), 
    NOW()
);

-- Vérification du nombre de sentiers insérés
SELECT COUNT(*) as total_sentiers FROM Sentier;
SELECT niveau_difficulte, COUNT(*) as nombre FROM Sentier GROUP BY niveau_difficulte;
SELECT id_zone, COUNT(*) as nombre FROM Sentier GROUP BY id_zone;
