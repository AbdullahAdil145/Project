
<?php
// Database configuration
define('DB_HOST', '8ijr5.h.filess.io');
define('DB_DATABASE', 'cosc4806_blackthan');
define('DB_USER', 'cosc4806_blackthan');
define('DB_PASS', getenv('db_pass'));
define('DB_PORT', 61000);

// Global database connection
$db = null;

function db_connect() {
    try { 
        $dbh = new PDO('mysql:host=' . DB_HOST . ';port='. DB_PORT . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbh;
    } catch (PDOException $e) {
        error_log("DB connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Initialize global connection
try {
    $db = db_connect();
} catch (Exception $e) {
    error_log("Failed to initialize database: " . $e->getMessage());
}
