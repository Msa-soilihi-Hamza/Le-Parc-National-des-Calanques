<?php

// Routes API centralisées pour la nouvelle structure backend

return [
    // Routes Authentification
    'POST /api/auth/login' => 'AuthApiController@login',
    'POST /api/auth/register' => 'AuthApiController@register',
    'POST /api/auth/refresh' => 'AuthApiController@refresh',
    'POST /api/auth/logout' => 'AuthApiController@logout',
    'POST /api/auth/validate' => 'AuthApiController@validateToken',
    'GET /api/auth/me' => 'AuthApiController@me',

    // Routes Utilisateurs
    'GET /api/profile' => 'UserApiController@profile',
    'PUT /api/profile' => 'UserApiController@updateProfile',
    'GET /api/users' => 'UserApiController@users',

    // Routes Système
    'GET /api/health' => 'HealthApiController@health',
]