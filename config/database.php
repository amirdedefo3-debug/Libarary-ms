<?php
/**
 * Database Configuration — PDO Connection
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'library_ms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }
        return self::$instance;
    }

    // Prevent cloning / unserializing
    private function __clone() {}
    public function __wakeup() {}
}
