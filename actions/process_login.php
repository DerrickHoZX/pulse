<?php
session_start();
require_once '../inc/db.inc.php';
require_once '../inc/mail.inc.php';

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
$stmt = $conn->prepare("SELECT user_id, fname, lname, email, password, role, login_attempts, lockout_until FROM users WHERE email = ? AND is_deleted = 0");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    header('Location: ../login.php?error=invalid&remaining=3');
    exit;
}

// Check lockout
if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
    $minutes = ceil((strtotime($user['lockout_until']) - time()) / 60);
    $conn->close();
    header("Location: ../login.php?error=locked&minutes=$minutes");
    exit;
}

// Wrong password
if (!password_verify($pwd, $user['password'])) {
    $attempts = $user['login_attempts'] + 1;

    if ($attempts >= 3) {
        $lockout = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $upd = $conn->prepare("UPDATE users SET login_attempts = ?, lockout_until = ? WHERE user_id = ?");
        $upd->bind_param('isi', $attempts, $lockout, $user['user_id']);
        $upd->execute();
        $upd->close();
        $conn->close();
        header('Location: ../login.php?error=locked&minutes=5');
    } else {
        $remaining = 3 - $attempts;
        $upd = $conn->prepare("UPDATE users SET login_attempts = ? WHERE user_id = ?");
        $upd->bind_param('ii', $attempts, $user['user_id']);
        $upd->execute();
        $upd->close();
        $conn->close();
        header("Location: ../login.php?error=invalid&remaining=$remaining");
    }
    exit;
}

// Correct password — reset login attempts
$reset = $conn->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE user_id = ?");
$reset->bind_param('i', $user['user_id']);
$reset->execute();
$reset->close();

// ── ADMIN: generate OTP and redirect to verify page ──────────────────────────
if ($user['role'] === 'admin') {
    $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $upd = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE user_id = ?");
    $upd->bind_param('ssi', $otp, $expiresAt, $user['user_id']);
    $upd->execute();
    $upd->close();
    $conn->close();

    // Send OTP email
    $mail = buildAdminOtpMail($user['fname'], $otp);
    pulseSendMail(
        $user['email'],
        $user['fname'] . ' ' . $user['lname'],
        $mail['subject'],
        $mail['html'],
        $mail['text']
    );

    // Store only enough in session to identify the pending admin — NOT logged in yet
    session_regenerate_id(true);
    $_SESSION['otp_pending_id']    = $user['user_id'];
    $_SESSION['otp_pending_email'] = $user['email'];
    $_SESSION['otp_pending_fname'] = $user['fname'];

    header('Location: ../otp_verify.php');
    exit;
}

// ── REGULAR USER: log in straight away ───────────────────────────────────────
$conn->close();

session_regenerate_id(true);
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['fname']   = $user['fname'];
$_SESSION['lname']   = $user['lname'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

$redirect = $_GET['redirect'] ?? '../index.php';
header('Location: ' . $redirect);
exit;
?>