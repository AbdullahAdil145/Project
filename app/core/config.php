<?php
// Database configuration
define('DB_HOST', '8ijr5.h.filess.io'); // Database host
define('DB_DATABASE', 'cosc4806_blackthan'); // Database name
define('DB_USER', 'cosc4806_blackthan'); // Database username
define('DB_PASS', getenv('db_pass')); // Database password from environment variable
define('DB_PORT', 61000); // Custom database port

// Global database connection
$db = null;

function db_connect() {
    try { 
        // Create new PDO connection using defined constants
        $dbh = new PDO('mysql:host=' . DB_HOST . ';port='. DB_PORT . ';dbname=' . DB_DATABASE, DB_USER, DB_PASS);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set PDO to throw exceptions on error
        return $dbh; // Return the database handle
    } catch (PDOException $e) {
        // Log error and show generic message
        error_log("DB connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Initialize global connection
try {
    $db = db_connect(); // Attempt to connect to the database
} catch (Exception $e) {
    error_log("Failed to initialize database: " . $e->getMessage()); // Log initialization failure
}
