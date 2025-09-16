<?php

require_once dirname(__DIR__) . '/src/Utils/EnvLoader.php';

use ParcCalanques\Utils\EnvLoader;

class Database {
    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;
    private string $charset;
    private ?PDO $pdo = null;

    public function __construct() {
        // Charger les variables d'environnement
        EnvLoader::load();

        // Configuration depuis le fichier .env
        $this->host = EnvLoader::getRequired('DB_HOST');
        $this->port = EnvLoader::getRequired('DB_PORT');
        $this->db_name = EnvLoader::getRequired('DB_NAME');
        $this->username = EnvLoader::getRequired('DB_USERNAME');
        $this->password = EnvLoader::get('DB_PASSWORD', '');
        $this->charset = EnvLoader::get('DB_CHARSET', 'utf8mb4');
    }

    public function getConnection(): ?PDO {
        $this->pdo = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Erreur de connexion DB: " . $exception->getMessage());
            // Ne pas faire echo ici car cela peut casser les headers JSON pour l'API
            // echo "Erreur de connexion: " . $exception->getMessage();
        }

        return $this->pdo;
    }

    public function closeConnection(): void {
        $this->pdo = null;
    }
}