<?php

require_once 'config/database.php';

class Migration {
    private ?PDO $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
        
        if (!$this->pdo) {
            die("Impossible de se connecter à la base de données");
        }
    }

    public function runMigration(string $migrationFile): bool {
        try {
            echo "Exécution de la migration: " . $migrationFile . "\n";
            
            $sql = file_get_contents($migrationFile);
            
            if ($sql === false) {
                throw new Exception("Impossible de lire le fichier de migration: " . $migrationFile);
            }

            // Nettoyer le SQL des commentaires
            $lines = explode("\n", $sql);
            $cleanedLines = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                // Ignorer les lignes vides et les commentaires
                if (!empty($line) && !preg_match('/^\s*--/', $line)) {
                    $cleanedLines[] = $line;
                }
            }
            
            $cleanedSql = implode(' ', $cleanedLines);
            
            // Diviser le SQL en requêtes individuelles
            $queries = array_filter(
                array_map('trim', explode(';', $cleanedSql)),
                function($query) {
                    return !empty($query);
                }
            );

            $this->pdo->beginTransaction();

            foreach ($queries as $query) {
                if (!empty(trim($query))) {
                    echo "Exécution: " . substr(trim($query), 0, 80) . "...\n";
                    try {
                        $result = $this->pdo->exec($query);
                        echo "  ✅ Requête exécutée avec succès\n";
                    } catch (PDOException $e) {
                        echo "  ❌ Erreur SQL: " . $e->getMessage() . "\n";
                        echo "  Requête complète: " . $query . "\n";
                        throw $e;
                    }
                }
            }

            $this->pdo->commit();
            echo "Migration terminée avec succès: " . $migrationFile . "\n\n";

        } catch (Exception $e) {
            $this->pdo->rollback();
            echo "Erreur lors de la migration " . $migrationFile . ": " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }

    public function runAllMigrations(): bool {
        $migrationDir = 'database/migrations/';
        $migrationFiles = glob($migrationDir . '*.sql');
        sort($migrationFiles);

        echo "=== DÉBUT DES MIGRATIONS ===\n";
        echo "Base de données: le-parc-national-des-calanques\n";
        echo "Port: 3306\n\n";

        foreach ($migrationFiles as $migrationFile) {
            if (!$this->runMigration($migrationFile)) {
                echo "Arrêt des migrations en raison d'une erreur.\n";
                return false;
            }
        }

        echo "=== TOUTES LES MIGRATIONS TERMINÉES ===\n";
        return true;
    }
}

// Exécution des migrations
$migration = new Migration();
$migration->runAllMigrations();