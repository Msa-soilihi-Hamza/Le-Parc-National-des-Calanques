<?php

declare(strict_types=1);

namespace ParcCalanques\Controllers\Api;

use ParcCalanques\Core\ApiResponse;
use ParcCalanques\Core\Request;
use ParcCalanques\Models\UserRepository;

class UserApiController
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * GET /api/users/profile
     */
    public function getProfile(): void
    {
        $user = Request::getAuthenticatedUser();
        
        ApiResponse::success([
            'user' => $user->toArray()
        ]);
    }

    /**
     * PUT /api/users/profile
     */
    public function updateProfile(): void
    {
        $user = Request::getAuthenticatedUser();
        $data = Request::getJsonInput();
        
        // Valider les données
        Request::validate($data, [
            'prenom' => 'string|max:50',
            'nom' => 'string|max:50',
            'telephone' => 'string|max:20',
            'date_naissance' => 'string' // TODO: Ajouter validation de date
        ]);
        
        // Filtrer les champs autorisés
        $allowedFields = ['prenom', 'nom', 'telephone', 'date_naissance'];
        $updates = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            ApiResponse::error('Aucun champ valide à mettre à jour', 400);
            return;
        }
        
        try {
            // TODO: Implémenter updateUser dans UserRepository
            // $updatedUser = $this->userRepository->updateUser($user->getId(), $updates);
            
            // Pour l'instant, simulation
            ApiResponse::updated([
                'user' => array_merge($user->toArray(), $updates),
                'updated_fields' => array_keys($updates)
            ]);
            
        } catch (\Exception $e) {
            ApiResponse::error('Erreur lors de la mise à jour du profil', 500);
        }
    }

    /**
     * GET /api/users (Admin seulement)
     */
    public function list(): void
    {
        Request::requireAdmin();
        
        // Paramètres de pagination
        $page = (int) Request::get('page', 1);
        $perPage = min((int) Request::get('per_page', 20), 100); // Max 100
        $search = Request::get('search', '');
        $role = Request::get('role', '');
        
        try {
            // TODO: Implémenter la recherche paginée dans UserRepository
            // $result = $this->userRepository->searchUsers($search, $role, $page, $perPage);
            
            // Simulation pour l'instant
            $mockUsers = [
                [
                    'id' => 1,
                    'email' => 'hamza@hamza.fr',
                    'prenom' => 'Hamza',
                    'nom' => 'Test',
                    'role' => 'admin',
                    'created_at' => '2024-01-01 10:00:00'
                ]
            ];
            
            ApiResponse::paginated($mockUsers, 1, $page, $perPage, [
                'search' => $search,
                'role' => $role
            ]);
            
        } catch (\Exception $e) {
            ApiResponse::error('Erreur lors de la récupération des utilisateurs', 500);
        }
    }

    /**
     * GET /api/users/{id} (Admin seulement)
     */
    public function show(array $params): void
    {
        Request::requireAdmin();
        
        $userId = (int) $params['id'];
        
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                ApiResponse::notFound('Utilisateur non trouvé');
                return;
            }
            
            ApiResponse::success([
                'user' => $user->toArray()
            ]);
            
        } catch (\Exception $e) {
            ApiResponse::error('Erreur lors de la récupération de l\'utilisateur', 500);
        }
    }

    /**
     * PUT /api/users/{id} (Admin seulement)
     */
    public function update(array $params): void
    {
        Request::requireAdmin();
        
        $userId = (int) $params['id'];
        $data = Request::getJsonInput();
        
        // Valider les données
        Request::validate($data, [
            'email' => 'email|max:255',
            'prenom' => 'string|max:50',
            'nom' => 'string|max:50',
            'role' => 'in:user,admin,moderator',
            'telephone' => 'string|max:20'
        ]);
        
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                ApiResponse::notFound('Utilisateur non trouvé');
                return;
            }
            
            // TODO: Implémenter updateUser dans UserRepository
            // $updatedUser = $this->userRepository->updateUser($userId, $data);
            
            ApiResponse::updated([
                'user' => array_merge($user->toArray(), $data),
                'message' => 'Utilisateur mis à jour avec succès'
            ]);
            
        } catch (\Exception $e) {
            ApiResponse::error('Erreur lors de la mise à jour de l\'utilisateur', 500);
        }
    }

    /**
     * DELETE /api/users/{id} (Admin seulement)
     */
    public function delete(array $params): void
    {
        Request::requireAdmin();
        
        $userId = (int) $params['id'];
        $currentUser = Request::getAuthenticatedUser();
        
        // Empêcher la suppression de son propre compte
        if ($userId === $currentUser->getId()) {
            ApiResponse::error('Vous ne pouvez pas supprimer votre propre compte', 400);
            return;
        }
        
        try {
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                ApiResponse::notFound('Utilisateur non trouvé');
                return;
            }
            
            // TODO: Implémenter deleteUser dans UserRepository
            // $this->userRepository->deleteUser($userId);
            
            ApiResponse::deleted('Utilisateur supprimé avec succès');
            
        } catch (\Exception $e) {
            ApiResponse::error('Erreur lors de la suppression de l\'utilisateur', 500);
        }
    }
}