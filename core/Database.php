<?php

// Path relative from core/ to config/
require_once __DIR__ . '/../config/database.php'; 

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $options = DB_OPTIONS;
    public $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $exception) {
            // In a real application, you might log this error instead of echoing it directly
            echo "Connection error: " . $exception->getMessage();
            exit(); 
        }

        return $this->conn;
    }
}

?>
