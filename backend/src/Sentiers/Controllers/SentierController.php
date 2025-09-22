<?php

declare(strict_types=1);

namespace ParcCalanques\Sentiers\Controllers;

use ParcCalanques\Sentiers\Services\SentierService;
use ParcCalanques\Core\Request;

class SentierController
{
    public function __construct(private SentierService $sentierService) {}

    /**
     * GET /api/sentiers
     * Récupère la liste des sentiers avec filtres optionnels
     */
    public function index(Request $request): void
    {
        try {
            $difficulty = $request->getQueryParam('difficulty');
            $zoneId = $request->getQueryParam('zone_id');
            $search = $request->getQueryParam('search');

            if ($search) {
                $sentiers = $this->sentierService->searchSentiers($search);
            } else {
                $sentiers = $this->sentierService->getSentiersWithFilters(
                    $difficulty, 
                    $zoneId ? (int) $zoneId : null
                );
            }

            $this->sendJsonResponse([
                'success' => true,
                'data' => $sentiers,
                'message' => 'Sentiers récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            $this->sendJsonError('Erreur lors de la récupération des sentiers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Envoie une réponse JSON de succès
     */
    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Envoie une réponse JSON d'erreur
     */
    private function sendJsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $statusCode
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /api/sentiers/{id}
     * Récupère un sentier spécifique
     */
    public function show(Request $request): void
    {
        try {
            $id = $request->getRouteParam('id');
            
            if (!$id || !is_numeric($id)) {
                $this->sendJsonError('ID de sentier invalide', 400);
                return;
            }

            $sentier = $this->sentierService->getSentierById((int) $id);
            
            if (!$sentier) {
                $this->sendJsonError('Sentier non trouvé', 404);
                return;
            }

            $this->sendJsonResponse([
                'success' => true,
                'data' => $sentier,
                'message' => 'Sentier récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            $this->sendJsonError('Erreur lors de la récupération du sentier: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/sentiers/filters
     * Récupère les options de filtrage disponibles
     */
    public function filters(Request $request): void
    {
        try {
            $filters = $this->sentierService->getFilterOptions();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $filters,
                'message' => 'Options de filtrage récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            $this->sendJsonError('Erreur lors de la récupération des filtres: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/sentiers/stats
     * Récupère les statistiques des sentiers
     */
    public function stats(Request $request): void
    {
        try {
            $stats = $this->sentierService->getSentiersStats();
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ]);

        } catch (\Exception $e) {
            $this->sendJsonError('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
