<?php

// Routes API centralisées pour la nouvelle structure backend
// Note: Les routes d'authentification sont gérées dans src/Auth/Routes/auth-routes.php

return [
    // Routes Système
    'GET /api/health' => 'HealthApiController@health',

    // Routes Utilisateurs (à créer plus tard)
    // 'GET /api/users' => 'UserController@index',
    // 'GET /api/users/{id}' => 'UserController@show',
    // 'PUT /api/users/{id}' => 'UserController@update',
    // 'DELETE /api/users/{id}' => 'UserController@delete',

    // Routes Zones (à créer plus tard)
    // 'GET /api/zones' => 'ZoneController@index',
    // 'GET /api/zones/{id}' => 'ZoneController@show',

    // Routes Réservations (à créer plus tard)
    // 'GET /api/reservations' => 'ReservationController@index',
    // 'POST /api/reservations' => 'ReservationController@create',
];