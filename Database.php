<?php

declare(strict_types=1);

namespace FitConnect\Config;

use PDO;
use PDOException;

/**
 * Connexion PDO centralisée (singleton).
 * Charger via Database::getInstance().
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn  = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $_ENV['DB_HOST']    ?? 'localhost',
                $_ENV['DB_NAME']    ?? 'fitconnect',
                $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $_ENV['DB_USER'] ?? 'root',
                    $_ENV['DB_PASS'] ?? '',
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                // En production, ne jamais exposer les détails de connexion
                error_log('[FitConnect] Erreur PDO : ' . $e->getMessage());
                throw new \RuntimeException('Impossible de se connecter à la base de données.', 0, $e);
            }
        }

        return self::$instance;
    }
}
