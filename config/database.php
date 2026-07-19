<?php
// ============================================
// CONNEXION À LA BASE DE DONNÉES
// ============================================

$env_file = __DIR__ . '/../.env';
$env = [];

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

$is_local = ($env['APP_ENV'] ?? 'local') === 'local';

if ($is_local) {
    define('DB_HOST', $env['DB_HOST_LOCAL'] ?? 'localhost');
    define('DB_NAME', $env['DB_NAME_LOCAL'] ?? 'newsletter_guede');
    define('DB_USER', $env['DB_USER_LOCAL'] ?? 'root');
    define('DB_PASS', $env['DB_PASS_LOCAL'] ?? '');
    define('APP_URL', $env['APP_URL_LOCAL'] ?? 'http://localhost/newsletter_automatique');
} else {
    define('DB_HOST', $env['DB_HOST_PROD'] ?? 'localhost');
    define('DB_NAME', $env['DB_NAME_PROD'] ?? 'devsione_newsletter_guede');
    define('DB_USER', $env['DB_USER_PROD'] ?? 'devsione_newsletter');
    define('DB_PASS', $env['DB_PASS_PROD'] ?? '');
    define('APP_URL', $env['APP_URL_PROD'] ?? 'https://ange.devsione.ci');
}

define('DB_CHARSET', 'utf8mb4');
define('GOOGLE_CLIENT_ID', $env['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $env['GOOGLE_CLIENT_SECRET'] ?? '');
define('MAIL_USERNAME', $env['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $env['MAIL_PASSWORD'] ?? '');

if (!function_exists('getDB')) {
    function getDB() {
        static $pdo = null;

        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        return $pdo;
    }
}
?>