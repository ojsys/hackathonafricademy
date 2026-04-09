<?php
// ─── Driver selection ─────────────────────────────────────
// 'sqlite' for local development (no server needed)
// 'mysql'  for production on cPanel / Hostinger / shared hosting
//
// DEPLOYMENT: Change this to 'mysql' and fill in the MySQL credentials below
define('DB_DRIVER', 'sqlite');

// ─── SQLite settings (development) ────────────────────────
define('SQLITE_PATH', __DIR__ . '/../database/lms.sqlite');

// ─── MySQL settings (production) ──────────────────────────
// 1. Create a MySQL database in your cPanel > MySQL Databases
// 2. Create a database user and assign it ALL PRIVILEGES on the database
// 3. Fill in the credentials below
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_cpanel_username_lms');  // e.g. hackafrica_lms
define('DB_USER', 'your_cpanel_username_user'); // e.g. hackafrica_admin
define('DB_PASS', '');                           // the password you set in cPanel
define('DB_CHARSET', 'utf8mb4');

// ─── PDO connection (singleton) ───────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        if (DB_DRIVER === 'sqlite') {
            $pdo = new PDO('sqlite:' . SQLITE_PATH);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->exec("SET sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);

    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        die('Database connection error. Please check your config/database.php credentials.');
    }

    return $pdo;
}
