<?php

declare(strict_types=1);

use ParcCalanques\Admin\Controllers\UserController;
use ParcCalanques\Admin\Services\UserManagementService;
use ParcCalanques\Admin\Middleware\AdminMiddleware;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Auth\Services\JwtService;
use ParcCalanques\Core\Database;

return function($app) {
    $pdo = Database::getInstance()->getConnection();
    $userRepository = new UserRepository($pdo);
    $userManagementService = new UserManagementService($userRepository);
    $adminMiddleware = new AdminMiddleware();
    $userController = new UserController($userManagementService, $adminMiddleware);
    $jwtService = new JwtService();

    $getCurrentUser = function() use ($jwtService, $userRepository) {
        $token = null;
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            return null;
        }

        try {
            $payload = $jwtService->validateToken($token);
            return $userRepository->findById($payload['user_id']);
        } catch (Exception $e) {
            return null;
        }
    };

    // Routes pour la gestion des utilisateurs
    $app->get('/admin/users', function() use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->index($currentUser);
            http_response_code($response['success'] ? 200 : 400);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->get('/admin/users/{id}', function($id) use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->show((int)$id, $currentUser);
            http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->post('/admin/users', function() use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->create($currentUser);
            http_response_code($response['success'] ? 201 : 400);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->put('/admin/users/{id}', function($id) use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->update((int)$id, $currentUser);
            http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->delete('/admin/users/{id}', function($id) use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->delete((int)$id, $currentUser);
            http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->patch('/admin/users/{id}/activate', function($id) use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->activate((int)$id, $currentUser);
            http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });

    $app->patch('/admin/users/{id}/deactivate', function($id) use ($userController, $getCurrentUser) {
        header('Content-Type: application/json');
        $currentUser = $getCurrentUser();

        try {
            $response = $userController->deactivate((int)$id, $currentUser);
            http_response_code($response['success'] ? 200 : ($response['message'] === 'User not found' ? 404 : 400));
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'data' => null
            ]);
        }
    });
};