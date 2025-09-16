<?php
/**
 * Système de migration pour l'équipe
 * Parc National des Calanques
 */

class DatabaseMigrator {
    private $host = 'localhost';
    private $port = '3306';
    private $dbname = 'le-parc-national-des-calanques';
    private $username = 'root';
    private $password = '';
    private $pdo;
    
    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8";
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            echo "✓ Connexion à la base de données réussie\n";
        } catch(PDOException $e) {
            die("✗ Erreur de connexion : " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * Créer la table migrations si elle n'existe pas
     */
    public function createMigrationTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
        echo "✓ Table migrations créée ou vérifiée\n";
    }
    
    /**
     * Obtenir la liste des migrations déjà exécutées
     */
    public function getExecutedMigrations() {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY id");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtenir la liste des fichiers de migration
     */
    public function getMigrationFiles() {
        $migrations = [];
        $files = glob(__DIR__ . '/migrations/*.sql');
        
        foreach($files as $file) {
            $filename = basename($file);
            if(preg_match('/^\d{3}_/', $filename)) {
                $migrations[] = $filename;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Exécuter une migration
     */
    public function runMigration($filename) {
        $filepath = __DIR__ . '/migrations/' . $filename;
        
        if(!file_exists($filepath)) {
            throw new Exception("Fichier de migration introuvable : $filename");
        }
        
        $sql = file_get_contents($filepath);
        
        // Diviser le SQL en requêtes individuelles
        $queries = preg_split('/;\s*\n/', $sql);
        
        $this->pdo->beginTransaction();
        
        try {
            foreach($queries as $query) {
                $query = trim($query);
                if(!empty($query) && !preg_match('/^--/', $query)) {
                    $this->pdo->exec($query);
                }
            }
            
            // Marquer la migration comme exécutée
            $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
            $stmt->execute([$filename]);
            
            $this->pdo->commit();
            echo "✓ Migration exécutée : $filename\n";
            
        } catch(Exception $e) {
            $this->pdo->rollback();
            throw new Exception("Erreur lors de l'exécution de $filename : " . $e->getMessage());
        }
    }
    
    /**
     * Exécuter toutes les migrations en attente
     */
    public function migrate() {
        echo "=== SYSTÈME DE MIGRATION ===\n";
        echo "Base de données : {$this->dbname}\n";
        echo "=============================\n\n";
        
        $this->createMigrationTable();
        
        $executed = $this->getExecutedMigrations();
        $available = $this->getMigrationFiles();
        
        $pending = array_diff($available, $executed);
        
        if(empty($pending)) {
            echo "✓ Aucune migration en attente\n";
            echo "Base de données à jour !\n";
            return;
        }
        
        echo "Migrations en attente : " . count($pending) . "\n\n";
        
        foreach($pending as $migration) {
            try {
                $this->runMigration($migration);
            } catch(Exception $e) {
                echo "✗ Erreur : " . $e->getMessage() . "\n";
                exit(1);
            }
        }
        
        echo "\n✓ Toutes les migrations ont été exécutées avec succès !\n";
    }
    
    /**
     * Afficher le statut des migrations
     */
    public function status() {
        echo "=== STATUT DES MIGRATIONS ===\n\n";
        
        $this->createMigrationTable();
        
        $executed = $this->getExecutedMigrations();
        $available = $this->getMigrationFiles();
        
        echo "Migrations disponibles :\n";
        foreach($available as $migration) {
            $status = in_array($migration, $executed) ? "✓ Exécutée" : "⏳ En attente";
            echo "  $migration - $status\n";
        }
        
        echo "\nTotal : " . count($available) . " migrations\n";
        echo "Exécutées : " . count($executed) . "\n";
        echo "En attente : " . (count($available) - count($executed)) . "\n";
    }
}

// CLI Usage
if(php_sapi_name() === 'cli') {
    $migrator = new DatabaseMigrator();
    
    $command = $argv[1] ?? 'migrate';
    
    switch($command) {
        case 'migrate':
            $migrator->migrate();
            break;
        case 'status':
            $migrator->status();
            break;
        default:
            echo "Usage: php migrate.php [migrate|status]\n";
            echo "  migrate : Exécute les migrations en attente\n";
            echo "  status  : Affiche le statut des migrations\n";
    }
}