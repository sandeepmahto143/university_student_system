<?php
/**
 * ---------------------------------------------------------------
 *  SQLite DEMO MODE  (not used with XAMPP / MySQL)
 * ---------------------------------------------------------------
 *  Lets the project run on a machine without MySQL (e.g. for a quick
 *  preview). It creates the same tables as database/university_db.sql
 *  inside data/university_demo.sqlite and seeds the same sample data.
 *  Enable it by copying includes/config.local.php.example to
 *  includes/config.local.php.
 */

function sqlite_demo_connect(array $options): PDO
{
    $file  = defined('SQLITE_FILE') ? SQLITE_FILE : __DIR__ . '/../data/university_demo.sqlite';
    $isNew = !file_exists($file);
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    $pdo = new PDO('sqlite:' . $file, null, null, $options);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($isNew) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name  VARCHAR(100) NOT NULL,
                username   VARCHAR(50)  NOT NULL UNIQUE,
                email      VARCHAR(100) NOT NULL UNIQUE,
                password   VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS students (
                id             INTEGER PRIMARY KEY AUTOINCREMENT,
                reg_no         VARCHAR(20)  NOT NULL UNIQUE,
                name           VARCHAR(100) NOT NULL,
                course         VARCHAR(30)  NOT NULL,
                semester       INTEGER NOT NULL CHECK (semester BETWEEN 1 AND 8),
                email          VARCHAR(100) NOT NULL UNIQUE,
                contact_number VARCHAR(15)  NOT NULL,
                created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Load the same seed rows that the MySQL script uses.
        $sql = file_get_contents(__DIR__ . '/../database/university_db.sql');
        if (preg_match_all('/^INSERT INTO .*?;\s*$/ms', $sql, $m)) {
            foreach ($m[0] as $insert) {
                $pdo->exec($insert);
            }
        }
    }

    return $pdo;
}
