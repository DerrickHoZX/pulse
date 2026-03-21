<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'dev');
define('DB_PASS', 'Helloworld0996.');
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