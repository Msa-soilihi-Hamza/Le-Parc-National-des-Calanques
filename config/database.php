<?php

class Database {
    private string $host = 'localhost';
    private string $port = '3308';
    private string $db_name = 'le-parc-national-des-calanques';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $pdo = null;

    public function getConnection(): ?PDO {
        $this->pdo = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
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