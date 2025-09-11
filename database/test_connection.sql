USE `le-parc-national-des-calanques`;
SHOW TABLES;
SELECT COUNT(*) as nombre_tables FROM information_schema.tables WHERE table_schema = 'le-parc-national-des-calanques';
SELECT 'Migration terminée avec succès!' as status;