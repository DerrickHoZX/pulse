<?php
// Secure the session BEFORE starting it
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1); // Uncomment only after HTTPS is active

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>