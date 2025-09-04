# Modèle Conceptuel de Données (MCD)

## Entités principales

### Utilisateur
- id_utilisateur (PK)
- nom
- prenom
- email
- mot_de_passe
- type_utilisateur (visiteur, guide, administrateur)
- date_creation
- date_derniere_connexion

### Sentier
- id_sentier (PK)
- nom_sentier
- description
- difficulte
- duree_estimee
- distance
- point_depart
- point_arrivee
- statut (ouvert, ferme, maintenance)
- capacite_max

### Zone
- id_zone (PK)
- nom_zone
- type_zone (protegee, camping, observation)
- description
- superficie
- reglementation

### Reservation
- id_reservation (PK)
- id_utilisateur (FK)
- id_sentier (FK)
- date_reservation
- date_visite
- nombre_personnes
- statut (confirmee, en_attente, annulee)

### Camping
- id_camping (PK)
- id_zone (FK)
- nom_camping
- capacite
- equipements
- tarif_par_nuit

### RessourceNaturelle
- id_ressource (PK)
- id_zone (FK)
- nom_ressource
- type_ressource (faune, flore, geologie)
- description
- statut_protection

## Relations

- Un utilisateur peut avoir plusieurs réservations (1,N)
- Une réservation concerne un sentier (N,1)
- Une zone peut contenir plusieurs ressources naturelles (1,N)
- Une zone peut avoir un camping (1,0..1)