<?php

// Database connection details
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'apexcharm'); // Your actual database name (e.g., order_db)
define('DB_USER', 'root'); // Your actual database username (e.g., root)
define('DB_PASS', ''); // Your actual database password (leave empty for XAMPP root if no password)

// Optional: Character set for database connection
define('DB_CHARSET', 'utf8mb4');

// Optional: PDO options for better error handling and default fetch mode
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

?>
