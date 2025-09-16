<?php

declare(strict_types=1);

namespace ParcCalanques\Shared\Utils;

class EnvLoader
{
    private static bool $loaded = false;
    private static array $env = [];

    public static function load(string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            // Chercher le fichier .env à la racine du projet (4 niveaux au-dessus)
            $path = dirname(__DIR__, 4) . '/.env';
        }

        if (!file_exists($path)) {
            throw new \Exception("Le fichier .env n'existe pas : $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorer les commentaires
            if (strpos($line, '#') === 0) {
                continue;
            }

            // Parser les variables
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Supprimer les guillemets si présents
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                self::$env[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, string $default = null): ?string
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$env[$key] ?? $default;
    }

    public static function getRequired(string $key): string
    {
        $value = self::get($key);
        if ($value === null) {
            throw new \Exception("Variable d'environnement requise manquante : $key");
        }
        return $value;
    }
}