<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\Controllers;

use ParcCalanques\Admin\Services\UserManagementService;
use ParcCalanques\Admin\DTOs\CreateUserRequest;
use ParcCalanques\Admin\DTOs\UpdateUserRequest;
use ParcCalanques\Admin\Middleware\AdminMiddleware;
use ParcCalanques\Auth\Models\User;
use ParcCalanques\Shared\Exceptions\AuthException;

class UserController
{
    public function __construct(
        private UserManagementService $userManagementService,
        private AdminMiddleware $adminMiddleware
    ) {}

    public function index(?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = min((int) ($_GET['per_page'] ?? 20), 100);
        $search = $_GET['search'] ?? null;

        $response = $this->userManagementService->getAllUsers($page, $perPage, $search);

        return [
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $response->toArray()
        ];
    }

    public function show(int $id, ?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $user = $this->userManagementService->getUserById($id);

            return [
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user->toArray()
            ];
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function create(?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $request = CreateUserRequest::fromArray($input);

            $user = $this->userManagementService->createUser($request);

            return [
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->toArray()
            ];
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function update(int $id, ?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $request = UpdateUserRequest::fromArray($input);

            $user = $this->userManagementService->updateUser($id, $request);

            return [
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->toArray()
            ];
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function delete(int $id, ?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $success = $this->userManagementService->deleteUser($id);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully',
                    'data' => null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete user',
                    'data' => null
                ];
            }
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function activate(int $id, ?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $user = $this->userManagementService->activateUser($id);

            return [
                'success' => true,
                'message' => 'User activated successfully',
                'data' => $user->toArray()
            ];
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function deactivate(int $id, ?User $currentUser): array
    {
        $this->adminMiddleware->handle($currentUser);

        try {
            $user = $this->userManagementService->deactivateUser($id);

            return [
                'success' => true,
                'message' => 'User deactivated successfully',
                'data' => $user->toArray()
            ];
        } catch (AuthException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}