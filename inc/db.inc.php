<?php
// Securely parse the hidden configuration file
$config = parse_ini_file('/var/www/config.ini');

define('DB_HOST', 'localhost');
define('DB_USER', 'dev');
define('DB_PASS', $config['db_pass']);
define('DB_NAME', 'project');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>