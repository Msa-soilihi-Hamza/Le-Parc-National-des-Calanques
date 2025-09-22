-- Données de test pour les sentiers
-- Ajout de quelques sentiers dans les zones existantes

INSERT INTO Sentier (nom, niveau_difficulte, description, id_zone, created_at, updated_at) VALUES
(
    'Sentier des Goudes', 
    'facile', 
    'Un sentier côtier facile offrant une vue magnifique sur la Méditerranée. Parfait pour les familles et les débutants. Le sentier longe la côte rocheuse et permet d\'observer la faune marine locale.',
    1, 
    NOW(), 
    NOW()
),
(
    'Calanque de Sormiou - Circuit découverte', 
    'moyen', 
    'Découvrez les merveilles de la calanque de Sormiou par ce sentier de difficulté moyenne. Vous traverserez la garrigue méditerranéenne avant d\'atteindre les eaux turquoise de cette calanque emblématique.',
    1, 
    NOW(), 
    NOW()
),
(
    'Falaises de Morgiou', 
    'difficile', 
    'Sentier technique réservé aux randonneurs expérimentés. Escalade le long des falaises calcaires avec des passages exposés. Vue exceptionnelle sur le port de pêche de Morgiou en récompense.',
    2, 
    NOW(), 
    NOW()
),
(
    'Port de Morgiou - Balade familiale', 
    'facile', 
    'Promenade tranquille jusqu\'au charmant port de pêche de Morgiou. Idéal pour découvrir l\'histoire maritime locale et déguster les spécialités dans les cabanons traditionnels.',
    2, 
    NOW(), 
    NOW()
),
(
    'Calanque d\'En-Vau - Randonnée sportive', 
    'difficile', 
    'L\'un des sentiers les plus spectaculaires du parc ! Descente technique vers la plus belle calanque des Calanques. Eaux cristallines et falaises vertigineuses garanties. Prévoir de l\'eau en abondance.',
    3, 
    NOW(), 
    NOW()
),
(
    'Belvédère d\'En-Vau', 
    'moyen', 
    'Montée jusqu\'au point de vue dominant la calanque d\'En-Vau. Panorama exceptionnel sur les falaises blanches et la mer turquoise. Accessible aux randonneurs ayant une bonne condition physique.',
    3, 
    NOW(), 
    NOW()
),
(
    'Grande traversée du Massif', 
    'difficile', 
    'Randonnée de plusieurs heures à travers tout le massif des Calanques. Itinéraire pour randonneurs très expérimentés uniquement. Découverte de la biodiversité unique du parc national.',
    4, 
    NOW(), 
    NOW()
),
(
    'Sentier botanique du Massif', 
    'moyen', 
    'Circuit éducatif à travers la flore typique du massif des Calanques. Panneaux explicatifs sur la végétation méditerranéenne et les espèces endémiques. Idéal pour les amoureux de la nature.',
    4, 
    NOW(), 
    NOW()
),
(
    'Chemin des Douaniers', 
    'facile', 
    'Ancien chemin utilisé par les douaniers, aujourd\'hui aménagé pour la randonnée. Parcours panoramique le long de la côte avec de nombreux points de vue. Histoire et nature au rendez-vous.',
    1, 
    NOW(), 
    NOW()
),
(
    'Grotte de la Cosquer - Approche', 
    'moyen', 
    'Sentier menant aux abords de la célèbre grotte Cosquer (accès à la grotte interdit). Découverte géologique passionnante et explications sur cette merveille préhistorique unique.',
    2, 
    NOW(), 
    NOW()
);
