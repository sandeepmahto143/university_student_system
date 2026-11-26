<?php
/**
 * ---------------------------------------------------------------
 *  Database Connection (PHP <-> MySQL connectivity)
 * ---------------------------------------------------------------
 *  Uses PDO (PHP Data Objects) with prepared statements, which protects
 *  the application from SQL-injection attacks.
 *
 *  Usage in any page:   $pdo = db();
 */

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;          // create the connection only once per request
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows as arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared stmts
    ];

    try {
        if (DB_DRIVER === 'sqlite') {
            // Demo mode only (see config.local.php.example)
            require_once __DIR__ . '/sqlite_demo.php';
            $pdo = sqlite_demo_connect($options);
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
                 . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:24px;'
           . 'border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:8px">'
           . '<h2 style="margin-top:0">Database connection failed</h2>'
           . '<p>Make sure MySQL is running in XAMPP/WAMP and that you have imported '
           . '<code>database/university_db.sql</code> in phpMyAdmin.</p>'
           . '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p></div>';
        exit;
    }

    return $pdo;
}
