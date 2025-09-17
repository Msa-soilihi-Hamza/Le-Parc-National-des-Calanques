<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\Services;

use ParcCalanques\Auth\Models\User;
use ParcCalanques\Auth\Models\UserRepository;
use ParcCalanques\Admin\DTOs\CreateUserRequest;
use ParcCalanques\Admin\DTOs\UpdateUserRequest;
use ParcCalanques\Admin\DTOs\UserListResponse;
use ParcCalanques\Shared\Exceptions\AuthException;

class UserManagementService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function getAllUsers(int $page = 1, int $perPage = 20, ?string $search = null): UserListResponse
    {
        $offset = ($page - 1) * $perPage;

        if ($search) {
            $users = $this->userRepository->search($search, $perPage, $offset);
            $total = $this->userRepository->getSearchCount($search);
        } else {
            $users = $this->userRepository->getAllUsers($perPage, $offset);
            $total = $this->userRepository->getUserCount();
        }

        return UserListResponse::create($users, $total, $page, $perPage);
    }

    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new AuthException('User not found', 404);
        }

        return $user;
    }

    public function createUser(CreateUserRequest $request): User
    {
        $errors = $request->validate();
        if (!empty($errors)) {
            throw new AuthException('Validation failed: ' . implode(', ', $errors), 400);
        }

        if ($this->userRepository->emailExists($request->email)) {
            throw new AuthException('Email already exists', 409);
        }

        return $this->userRepository->create($request->toArray());
    }

    public function updateUser(int $id, UpdateUserRequest $request): User
    {
        if (!$request->hasData()) {
            throw new AuthException('No data provided for update', 400);
        }

        $errors = $request->validate();
        if (!empty($errors)) {
            throw new AuthException('Validation failed: ' . implode(', ', $errors), 400);
        }

        $existingUser = $this->getUserById($id);

        if ($request->email && $request->email !== $existingUser->getEmail()) {
            if ($this->userRepository->emailExists($request->email)) {
                throw new AuthException('Email already exists', 409);
            }
        }

        $success = $this->userRepository->update($id, $request->toArray());

        if (!$success) {
            throw new AuthException('Failed to update user', 500);
        }

        return $this->getUserById($id);
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);

        if ($user->isAdmin()) {
            $adminCount = $this->getAdminCount();
            if ($adminCount <= 1) {
                throw new AuthException('Cannot delete the last admin user', 400);
            }
        }

        return $this->userRepository->delete($id);
    }

    public function activateUser(int $id): User
    {
        $success = $this->userRepository->updateActiveStatus($id, true);

        if (!$success) {
            throw new AuthException('Failed to activate user', 500);
        }

        return $this->getUserById($id);
    }

    public function deactivateUser(int $id): User
    {
        $user = $this->getUserById($id);

        if ($user->isAdmin()) {
            $activeAdminCount = $this->getActiveAdminCount();
            if ($activeAdminCount <= 1) {
                throw new AuthException('Cannot deactivate the last active admin user', 400);
            }
        }

        $success = $this->userRepository->updateActiveStatus($id, false);

        if (!$success) {
            throw new AuthException('Failed to deactivate user', 500);
        }

        return $this->getUserById($id);
    }

    private function getAdminCount(): int
    {
        $admins = $this->userRepository->search('', PHP_INT_MAX, 0);
        return count(array_filter($admins, fn(User $user) => $user->isAdmin()));
    }

    private function getActiveAdminCount(): int
    {
        $admins = $this->userRepository->search('', PHP_INT_MAX, 0);
        return count(array_filter($admins, fn(User $user) => $user->isAdmin() && $user->isActive()));
    }
}