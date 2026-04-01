<?php
session_start();
require_once '../inc/db.inc.php';

// 1. Authorization: Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// 2. Protocol: Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit;
}

// 3. SECURITY: CSRF Token Validation
// This stops unauthorized third-party sites from submitting forms on behalf of the user.
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Security token validation failed. Unauthorized request.");
}

$action  = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$conn    = getDBConnection();

// ── Update Personal Details ──────────────────────────────────────────────────
if ($action === 'details') {
    $fname = trim(htmlspecialchars($_POST['fname'] ?? ''));
    $lname = trim(htmlspecialchars($_POST['lname'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));

    // Validation: Name format
    if (!preg_match("/^[a-zA-Z\s'-]{1,45}$/", $fname) || !preg_match("/^[a-zA-Z\s'-]{1,45}$/", $lname)) {
        header('Location: ../profile.php?error=invalidname'); exit;
    }
    // Validation: Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        header('Location: ../profile.php?error=invalidemail'); exit;
    }

    // Check email isn't taken by a different account (Soft-delete aware)
    $chk = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ? AND is_deleted = 0");
    $chk->bind_param('si', $email, $user_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $chk->close(); $conn->close();
        header('Location: ../profile.php?error=emailtaken'); exit;
    }
    $chk->close();

    // Prepared Statement for SQL Injection prevention
    $stmt = $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param('sssi', $fname, $lname, $email, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Update session state for UI consistency
    $_SESSION['fname'] = $fname;
    $_SESSION['lname'] = $lname;
    $_SESSION['email'] = $email;

    header('Location: ../profile.php?success=details');
    exit;
}

// ── Change Password ──────────────────────────────────────────────────────────
if ($action === 'password') {
    $current_pwd     = $_POST['current_pwd'] ?? '';
    $new_pwd         = $_POST['new_pwd'] ?? '';
    $new_pwd_confirm = $_POST['new_pwd_confirm'] ?? '';

    // Fetch current hash to verify identity
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!password_verify($current_pwd, $row['password'])) {
        $conn->close();
        header('Location: ../profile.php?error=wrongpwd'); exit;
    }

    // Server-side Password Complexity Enforcement
    if (strlen($new_pwd) < 8) {
        $conn->close();
        header('Location: ../profile.php?error=shortpwd'); exit;
    }
    if (!preg_match('/[A-Z]/', $new_pwd)) {
        $conn->close();
        header('Location: ../profile.php?error=pwdupper'); exit;
    }
    if (!preg_match('/[0-9]/', $new_pwd)) {
        $conn->close();
        header('Location: ../profile.php?error=pwdnumber'); exit;
    }
    if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};\':",.<>?\/\\\\|`~]/', $new_pwd)) {
        $conn->close();
        header('Location: ../profile.php?error=pwdspecial'); exit;
    }
    if ($new_pwd !== $new_pwd_confirm) {
        $conn->close();
        header('Location: ../profile.php?error=pwdmatch'); exit;
    }

    // Secure hashing using BCRYPT
    $new_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param('si', $new_hash, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: ../profile.php?success=password');
    exit;
}

// Fallback redirect
$conn->close();
header('Location: ../profile.php');
exit;
?>