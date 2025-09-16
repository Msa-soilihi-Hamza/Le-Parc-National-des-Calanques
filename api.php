<?php

declare(strict_types=1);

// Redirection intelligente vers la nouvelle structure backend
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Extraire le path après api.php
$path = parse_url($requestUri, PHP_URL_PATH);
$apiPath = '';

if (strpos($path, '/api.php') !== false) {
    $parts = explode('/api.php', $path, 2);
    $apiPath = isset($parts[1]) ? $parts[1] : '';
}

// Construire la nouvelle URL
$newUrl = 'backend/public/index.php' . $apiPath;
if ($queryString) {
    $newUrl .= '?' . $queryString;
}

header('Location: ' . $newUrl);
exit;