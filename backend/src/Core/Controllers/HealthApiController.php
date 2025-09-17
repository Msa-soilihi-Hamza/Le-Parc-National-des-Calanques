<?php

declare(strict_types=1);

namespace ParcCalanques\Controllers\Api;

use ParcCalanques\Core\ApiResponse;

class HealthApiController
{
    /**
     * GET /api/health
     */
    public function check(): void
    {
        $startTime = microtime(true);
        
        // Vérifications de santé
        $checks = [
            'php' => $this->checkPhp(),
            'database' => $this->checkDatabase(),
            'jwt' => $this->checkJwt(),
            'storage' => $this->checkStorage()
        ];
        
        $allHealthy = array_reduce($checks, fn($carry, $check) => $carry && $check['status'] === 'ok', true);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $response = [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'version' => '2.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'production',
            'execution_time' => $executionTime . 'ms',
            'checks' => $checks
        ];
        
        ApiResponse::success($response, $allHealthy ? 200 : 503);
    }
    
    private function checkPhp(): array
    {
        return [
            'status' => 'ok',
            'version' => PHP_VERSION,
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true))
        ];
    }
    
    private function checkDatabase(): array
    {
        try {
            // Test simple de connexion
            $config = require __DIR__ . '/../../../config/database.php';
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Test d'une requête simple
            $stmt = $pdo->query('SELECT 1');
            $result = $stmt->fetch();
            
            return [
                'status' => 'ok',
                'connection' => 'active',
                'driver' => $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME),
                'version' => $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION)
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function checkJwt(): array
    {
        try {
            // Vérifier que JWT est configuré
            $jwtSecret = $_ENV['JWT_SECRET'] ?? 'parc-calanques-secret-key-2025-dev-mode-change-in-production';
            
            return [
                'status' => 'ok',
                'configured' => !empty($jwtSecret),
                'algorithm' => 'HS256'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'JWT service error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function checkStorage(): array
    {
        try {
            $uploadDir = __DIR__ . '/../../../uploads';
            $logsDir = __DIR__ . '/../../../logs';
            
            return [
                'status' => 'ok',
                'uploads_writable' => is_writable($uploadDir),
                'logs_writable' => is_writable($logsDir),
                'disk_free' => $this->formatBytes(disk_free_space(__DIR__))
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}