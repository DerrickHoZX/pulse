<?php
session_start();
require_once '../inc/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot_password.php');
    exit;
}

$token       = trim($_POST['token'] ?? '');
$new_pwd     = $_POST['new_pwd'] ?? '';
$new_confirm = $_POST['new_pwd_confirm'] ?? '';

if (!$token) {
    header('Location: ../forgot_password.php');
    exit;
}

$redirect = '../reset_password.php?token=' . urlencode($token);

// Validate password
if (strlen($new_pwd) < 8) {
    header("Location: $redirect&error=shortpwd"); exit;
}
if (!preg_match('/[A-Z]/', $new_pwd)) {
    header("Location: $redirect&error=pwdupper"); exit;
}
if (!preg_match('/[0-9]/', $new_pwd)) {
    header("Location: $redirect&error=pwdnumber"); exit;
}
if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};\':",.<>?\/\\\\|`~]/', $new_pwd)) {
    header("Location: $redirect&error=pwdspecial"); exit;
}
if ($new_pwd !== $new_confirm) {
    header("Location: $redirect&error=pwdmatch"); exit;
}

$conn = getDBConnection();

// Check token is valid and not expired
$stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND is_deleted = 0");
$stmt->bind_param('s', $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    header('Location: ../reset_password.php?error=invalid');
    exit;
}

// Update password and clear token
$hash = password_hash($new_pwd, PASSWORD_BCRYPT);
$upd  = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL, login_attempts = 0, lockout_until = NULL WHERE user_id = ?");
$upd->bind_param('si', $hash, $user['user_id']);
$upd->execute();
$upd->close();
$conn->close();

header('Location: ../login.php?success=pwdreset');
exit;
?>