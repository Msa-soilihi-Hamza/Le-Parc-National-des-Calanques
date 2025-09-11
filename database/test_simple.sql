-- Test simple de création de table
USE `le-parc-national-des-calanques`;

CREATE TABLE test_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50)
);

SHOW TABLES;

SELECT 'Test terminé' as resultat;