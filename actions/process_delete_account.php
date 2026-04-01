<?php
session_start();
require_once '../inc/db.inc.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 2. Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit;
}

// 3. SECURE: CSRF Token Validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Security token validation failed. Unauthorized request.");
}

$delete_pwd = $_POST['delete_pwd'] ?? '';
$user_id    = $_SESSION['user_id'];
$conn       = getDBConnection();

// Fetch current password hash
$stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($delete_pwd, $row['password'])) {
    $conn->close();
    header('Location: ../profile.php?error=deletewrong');
    exit;
}

// Delete the account (Soft Delete)
$stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

// Destroy session and redirect
session_unset();
session_destroy();
header('Location: ../index.php');
exit;
?>