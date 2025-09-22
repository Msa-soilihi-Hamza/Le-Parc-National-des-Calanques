<?php

declare(strict_types=1);

namespace ParcCalanques\Sentiers\Models;

use PDO;
use DateTime;

class SentierRepository
{
    public function __construct(private PDO $pdo) {}

    public function findAll(): array
    {
        $sql = "
            SELECT 
                s.id_sentier,
                s.nom,
                s.niveau_difficulte,
                s.description,
                s.id_zone,
                s.created_at,
                s.updated_at,
                z.nom as nom_zone
            FROM Sentier s
            LEFT JOIN Zone z ON s.id_zone = z.id_zone
            ORDER BY s.nom ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $sentiers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sentiers[] = $this->createSentierFromRow($row);
        }
        
        return $sentiers;
    }

    public function findById(int $id): ?Sentier
    {
        $sql = "
            SELECT 
                s.id_sentier,
                s.nom,
                s.niveau_difficulte,
                s.description,
                s.id_zone,
                s.created_at,
                s.updated_at,
                z.nom as nom_zone
            FROM Sentier s
            LEFT JOIN Zone z ON s.id_zone = z.id_zone
            WHERE s.id_sentier = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->createSentierFromRow($row) : null;
    }

    public function findByZone(int $zoneId): array
    {
        $sql = "
            SELECT 
                s.id_sentier,
                s.nom,
                s.niveau_difficulte,
                s.description,
                s.id_zone,
                s.created_at,
                s.updated_at,
                z.nom as nom_zone
            FROM Sentier s
            LEFT JOIN Zone z ON s.id_zone = z.id_zone
            WHERE s.id_zone = :zone_id
            ORDER BY s.nom ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':zone_id', $zoneId, PDO::PARAM_INT);
        $stmt->execute();
        
        $sentiers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sentiers[] = $this->createSentierFromRow($row);
        }
        
        return $sentiers;
    }

    public function findByDifficulty(string $difficulty): array
    {
        $sql = "
            SELECT 
                s.id_sentier,
                s.nom,
                s.niveau_difficulte,
                s.description,
                s.id_zone,
                s.created_at,
                s.updated_at,
                z.nom as nom_zone
            FROM Sentier s
            LEFT JOIN Zone z ON s.id_zone = z.id_zone
            WHERE s.niveau_difficulte = :difficulty
            ORDER BY s.nom ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':difficulty', $difficulty, PDO::PARAM_STR);
        $stmt->execute();
        
        $sentiers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sentiers[] = $this->createSentierFromRow($row);
        }

        return $sentiers;
    }

    public function getAvailableDifficulties(): array
    {
        $sql = "SELECT DISTINCT niveau_difficulte FROM Sentier ORDER BY niveau_difficulte";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAvailableZones(): array
    {
        $sql = "
            SELECT DISTINCT z.id_zone, z.nom 
            FROM Zone z 
            INNER JOIN Sentier s ON z.id_zone = s.id_zone 
            ORDER BY z.nom
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createSentierFromRow(array $row): Sentier
    {
        return new Sentier(
            idSentier: (int) $row['id_sentier'],
            nom: $row['nom'],
            niveauDifficulte: $row['niveau_difficulte'],
            description: $row['description'],
            idZone: (int) $row['id_zone'],
            createdAt: $row['created_at'] ? new DateTime($row['created_at']) : null,
            updatedAt: $row['updated_at'] ? new DateTime($row['updated_at']) : null,
            nomZone: $row['nom_zone'] ?? null
        );
    }
}
