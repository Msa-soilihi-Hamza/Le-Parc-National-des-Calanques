<?php

declare(strict_types=1);

namespace ParcCalanques\Admin\Middleware;

use ParcCalanques\Auth\Models\User;
use ParcCalanques\Shared\Exceptions\AuthException;

class AdminMiddleware
{
    public function handle(?User $user): void
    {
        if (!$user) {
            throw new AuthException('Authentication required', 401);
        }

        if (!$user->isAdmin()) {
            throw new AuthException('Admin access required', 403);
        }

        if (!$user->isActive()) {
            throw new AuthException('Account inactive', 403);
        }
    }
}