<?php
// Database connection — InfinityFree (single source of truth for the whole project)
$sql_host = getenv('DB_HOST') ?: 'sql209.infinityfree.com';
$sql_user = getenv('DB_USER') ?: 'if0_42069228';
$sql_pass = getenv('DB_PASS') ?: 'GmAiL_007JniCEjNeFuNGO?!'; // vPanel login password
$sql_db   = getenv('DB_NAME') ?: 'if0_42069228_Czarchive';

define('DB_HOST', $sql_host);
define('DB_NAME', $sql_db);
define('DB_USER', $sql_user);
define('DB_PASS', $sql_pass);
define('DB_PORT', getenv('DB_PORT') ?: '3306');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $this->db_name)) {
                throw new PDOException('Invalid database name.');
            }

            $dsn = 'mysql:host=' . $this->host . ';port=' . DB_PORT . ';dbname=' . $this->db_name . ';charset=utf8mb4';
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec('set names utf8mb4');
        } catch (PDOException $exception) {
            error_log('Database connection error: ' . $exception->getMessage());
            if (ini_get('display_errors')) {
                echo 'Connection error: ' . htmlspecialchars($exception->getMessage()) . '<br>';
                if ($this->password === 'YOUR_VPANEL_PASSWORD') {
                    echo 'Set your vPanel password in config/database.php ($sql_pass).';
                } else {
                    echo 'Check $sql_host, $sql_user, $sql_pass, $sql_db and that tables are imported in phpMyAdmin.';
                }
            }
        }

        return $this->conn;
    }
}
?>
