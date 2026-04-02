<?php
session_start();
require_once '../inc/db.inc.php';
require_once '../inc/mail.inc.php';

// Must have a pending OTP session
if (empty($_SESSION['otp_pending_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../otp_verify.php');
    exit;
}

$userId = (int) $_SESSION['otp_pending_id'];
$action = $_POST['action'] ?? 'verify';

$conn = getDBConnection();

// ── RESEND ────────────────────────────────────────────────────────────────────
if ($action === 'resend') {
    $stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ? AND role = 'admin' AND is_deleted = 0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$admin) {
        $conn->close();
        session_destroy();
        header('Location: ../login.php');
        exit;
    }

    $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $upd = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE user_id = ?");
    $upd->bind_param('ssi', $otp, $expiresAt, $userId);
    $upd->execute();
    $upd->close();
    $conn->close();

    $mail = buildAdminOtpMail($admin['fname'], $otp);
    pulseSendMail(
        $admin['email'],
        $admin['fname'] . ' ' . $admin['lname'],
        $mail['subject'],
        $mail['html'],
        $mail['text']
    );

    header('Location: ../otp_verify.php?resent=1');
    exit;
}

// ── VERIFY ────────────────────────────────────────────────────────────────────
$inputOtp = trim($_POST['otp'] ?? '');

if (!$inputOtp) {
    $conn->close();
    header('Location: ../otp_verify.php?error=missing');
    exit;
}

$stmt = $conn->prepare("SELECT user_id, fname, lname, email, role, otp_code, otp_expires FROM users WHERE user_id = ? AND role = 'admin' AND is_deleted = 0");
$stmt->bind_param('i', $userId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin || !$admin['otp_code']) {
    $conn->close();
    header('Location: ../otp_verify.php?error=expired');
    exit;
}

// Check expiry
if (strtotime($admin['otp_expires']) < time()) {
    // Clear expired OTP
    $clr = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE user_id = ?");
    $clr->bind_param('i', $userId);
    $clr->execute();
    $clr->close();
    $conn->close();
    header('Location: ../otp_verify.php?error=expired');
    exit;
}

// Check code
if ($inputOtp !== $admin['otp_code']) {
    $conn->close();
    header('Location: ../otp_verify.php?error=invalid');
    exit;
}

// ── OTP correct — clear it and fully log the admin in ────────────────────────
$clr = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expires = NULL WHERE user_id = ?");
$clr->bind_param('i', $userId);
$clr->execute();
$clr->close();
$conn->close();

unset($_SESSION['otp_pending_id'], $_SESSION['otp_pending_email'], $_SESSION['otp_pending_fname']);
session_regenerate_id(true);

$_SESSION['user_id'] = $admin['user_id'];
$_SESSION['fname']   = $admin['fname'];
$_SESSION['lname']   = $admin['lname'];
$_SESSION['email']   = $admin['email'];
$_SESSION['role']    = $admin['role'];

header('Location: ../admin/admin.php');
exit;
?>