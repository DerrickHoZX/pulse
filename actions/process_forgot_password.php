<?php
session_start();
require_once '../inc/db.inc.php';
require_once '../inc/mail.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot_password.php');
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../forgot_password.php?status=invalid');
    exit;
}

$conn = getDBConnection();

// Check if email exists and is not deleted
$stmt = $conn->prepare("SELECT user_id, fname FROM users WHERE email = ? AND is_deleted = 0");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    // Generate secure token
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Save token to DB
    $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?");
    $upd->bind_param('ssi', $token, $expires, $user['user_id']);
    $upd->execute();
    $upd->close();

    // Build reset link
    $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/reset_password.php?token=' . $token;

    // Send email
    $mailContent = buildPasswordResetMail($user['fname'], $resetLink);
    pulseSendMail(
        MAIL_FROM_ADDRESS,
        MAIL_FROM_NAME,
        $mailContent['subject'],
        $mailContent['html'],
        $mailContent['text']
    );
}

$conn->close();

if (!$user) {
    header('Location: ../forgot_password.php?status=notfound');
    exit;
}

header('Location: ../forgot_password.php?status=sent');
exit;