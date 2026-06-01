<?php
// Database configuration
// Database configuration - use Railway MySQL service variables
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'secret_plan_db');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Validate database name (only allow alphanumeric, underscore, and hyphen)
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $this->db_name)) {
                throw new PDOException("Invalid database name. Only alphanumeric characters, underscores, and hyphens are allowed.");
            }
            
            // First, try to connect without database to check if server is available
            $temp_conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . DB_PORT . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if database exists (using prepared statement for the database name check)
            $stmt = $temp_conn->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$this->db_name]);
            $database_exists = $stmt->fetch();
            
            if (!$database_exists) {
                // Database doesn't exist, try to create it (backticks protect against reserved words)
                $temp_conn->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $this->db_name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            // Now connect to the actual database
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . DB_PORT . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            // Only show error in development - in production, log it instead
            error_log("Database connection error: " . $exception->getMessage());
            if (ini_get('display_errors')) {
                echo "Connection error: " . $exception->getMessage() . "<br>";
                echo "Please ensure MySQL is running and the database can be created, or import database.sql manually via phpMyAdmin.";
            }
        }
        
        return $this->conn;
    }
}
?>
