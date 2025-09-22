<?php

declare(strict_types=1);

namespace ParcCalanques\Sentiers\Services;

use ParcCalanques\Sentiers\Models\Sentier;
use ParcCalanques\Sentiers\Models\SentierRepository;

class SentierService
{
    public function __construct(private SentierRepository $sentierRepository) {}

    /**
     * Récupère tous les sentiers
     */
    public function getAllSentiers(): array
    {
        $sentiers = $this->sentierRepository->findAll();
        return array_map(fn(Sentier $sentier) => $sentier->toArray(), $sentiers);
    }

    /**
     * Récupère un sentier par son ID
     */
    public function getSentierById(int $id): ?array
    {
        $sentier = $this->sentierRepository->findById($id);
        return $sentier ? $sentier->toArray() : null;
    }

    /**
     * Récupère les sentiers filtrés
     */
    public function getSentiersWithFilters(?string $difficulty = null, ?int $zoneId = null): array
    {
        if ($difficulty && $zoneId) {
            // Si les deux filtres sont appliqués, on fait une requête personnalisée
            $sentiers = $this->filterByBoth($difficulty, $zoneId);
        } elseif ($difficulty) {
            $sentiers = $this->sentierRepository->findByDifficulty($difficulty);
        } elseif ($zoneId) {
            $sentiers = $this->sentierRepository->findByZone($zoneId);
        } else {
            $sentiers = $this->sentierRepository->findAll();
        }

        return array_map(fn(Sentier $sentier) => $sentier->toArray(), $sentiers);
    }

    /**
     * Récupère les options de filtrage disponibles
     */
    public function getFilterOptions(): array
    {
        return [
            'difficulties' => $this->sentierRepository->getAvailableDifficulties(),
            'zones' => $this->sentierRepository->getAvailableZones()
        ];
    }

    /**
     * Recherche de sentiers par nom
     */
    public function searchSentiers(string $query): array
    {
        $allSentiers = $this->sentierRepository->findAll();
        
        // Filtrage simple par nom (en attendant une recherche plus sophistiquée)
        $filteredSentiers = array_filter($allSentiers, function(Sentier $sentier) use ($query) {
            return stripos($sentier->getNom(), $query) !== false || 
                   stripos($sentier->getDescription() ?? '', $query) !== false;
        });

        return array_map(fn(Sentier $sentier) => $sentier->toArray(), $filteredSentiers);
    }

    /**
     * Statistiques des sentiers
     */
    public function getSentiersStats(): array
    {
        $allSentiers = $this->sentierRepository->findAll();
        
        $stats = [
            'total' => count($allSentiers),
            'by_difficulty' => [],
            'by_zone' => []
        ];

        foreach ($allSentiers as $sentier) {
            // Stats par difficulté
            $difficulty = $sentier->getNiveauDifficulte();
            $stats['by_difficulty'][$difficulty] = ($stats['by_difficulty'][$difficulty] ?? 0) + 1;

            // Stats par zone
            $zoneName = $sentier->getNomZone() ?? 'Zone inconnue';
            $stats['by_zone'][$zoneName] = ($stats['by_zone'][$zoneName] ?? 0) + 1;
        }

        return $stats;
    }

    private function filterByBoth(string $difficulty, int $zoneId): array
    {
        // Pour les filtres combinés, on filtre en PHP pour simplicité
        $sentiersZone = $this->sentierRepository->findByZone($zoneId);
        
        return array_filter($sentiersZone, function(Sentier $sentier) use ($difficulty) {
            return $sentier->getNiveauDifficulte() === $difficulty;
        });
    }
}


