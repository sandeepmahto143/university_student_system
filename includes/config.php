<?php
/**
 * ---------------------------------------------------------------
 *  Application Configuration
 * ---------------------------------------------------------------
 *  Change the values below to match your XAMPP / WAMP MySQL setup.
 *  Default XAMPP credentials are: user = "root", password = "".
 */

/*
 * Optional local override (NOT required for XAMPP).
 * If a file named config.local.php exists in this folder it is loaded first
 * and may define any of the constants below (for example to run a quick
 * demo with SQLite instead of MySQL). Constants it defines are not
 * redefined here.
 */
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

defined('APP_NAME')  || define('APP_NAME', 'University Student Information System');
defined('APP_SHORT') || define('APP_SHORT', 'UniSIS');

// ---- MySQL connection settings ----
defined('DB_DRIVER') || define('DB_DRIVER', 'mysql');     // 'mysql' for XAMPP / WAMP
defined('DB_HOST')   || define('DB_HOST', 'localhost');
defined('DB_PORT')   || define('DB_PORT', '3306');
defined('DB_NAME')   || define('DB_NAME', 'university_db');
defined('DB_USER')   || define('DB_USER', 'root');
defined('DB_PASS')   || define('DB_PASS', '');

// ---- XML document that stores student records ----
defined('XML_FILE')  || define('XML_FILE', __DIR__ . '/../xml/students.xml');

// ---- Time zone used for timestamps (e.g. XML 'generated' attribute) ----
date_default_timezone_set('Asia/Kolkata');

// ---- Master lists used by forms and validation ----
$COURSES   = ['BCA', 'BBA', 'B.Com', 'B.Sc', 'BA', 'MCA', 'MBA', 'M.Com'];
$SEMESTERS = range(1, 8);
