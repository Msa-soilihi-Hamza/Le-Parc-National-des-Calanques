<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\DTOs;

use ParcCalanques\Auth\Models\User;

class UserListResponse
{
    public function __construct(
        public readonly array $users,
        public readonly int $total,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $totalPages
    ) {}

    public static function create(array $users, int $total, int $page, int $perPage): self
    {
        $totalPages = (int) ceil($total / $perPage);

        return new self(
            users: array_map(fn(User $user) => $user->toArray(), $users),
            total: $total,
            page: $page,
            perPage: $perPage,
            totalPages: $totalPages
        );
    }

    public function toArray(): array
    {
        return [
            'data' => $this->users,
            'pagination' => [
                'total' => $this->total,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'total_pages' => $this->totalPages,
                'has_next' => $this->page < $this->totalPages,
                'has_prev' => $this->page > 1
            ]
        ];
    }
}