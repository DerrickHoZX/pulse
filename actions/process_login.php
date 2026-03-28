<?php
session_start();
require_once '../inc/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$pwd   = $_POST['pwd'] ?? '';

if (!$email || !$pwd) {
    header('Location: ../login.php?error=missing');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT user_id, fname, email, pwd_hash FROM users WHERE email = ?"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($user && password_verify($pwd, $user['pwd_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['fname']   = $user['fname'];
    $_SESSION['email']   = $user['email'];

    $redirect = $_GET['redirect'] ?? '../index.php';
    header('Location: ' . $redirect);
} else {
    header('Location: ../login.php?error=invalid');
}
exit;
?>
