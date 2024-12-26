<?php
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_USER', getenv('DB_USER') ?: 'msfelzhel');
define('DB_PASS', getenv('DB_PASS') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'auth_system');

function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

session_start();