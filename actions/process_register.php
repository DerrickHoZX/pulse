<?php
session_start();
require_once '../inc/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$fname       = trim(htmlspecialchars($_POST['fname'] ?? ''));
$lname       = trim(htmlspecialchars($_POST['lname'] ?? ''));
$email       = trim(htmlspecialchars($_POST['email'] ?? ''));
$pwd         = $_POST['pwd'] ?? '';
$pwd_confirm = $_POST['pwd_confirm'] ?? '';
$agree       = $_POST['agree'] ?? '';

if (!$fname || !$lname || !$email || !$pwd || !$pwd_confirm) {
    header('Location: ../register.php?error=missing'); exit;
}
if (!preg_match("/^[a-zA-Z\s'-]{1,45}$/", $fname) || !preg_match("/^[a-zA-Z\s'-]{1,45}$/", $lname)) {
    header('Location: ../register.php?error=invalidname'); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    header('Location: ../register.php?error=invalidemail'); exit;
}
if (strlen($pwd) < 8) {
    header('Location: ../register.php?error=shortpwd'); exit;
}
if (!preg_match('/[A-Z]/', $pwd)) {
    header('Location: ../register.php?error=pwdupper'); exit;
}
if (!preg_match('/[0-9]/', $pwd)) {
    header('Location: ../register.php?error=pwdnumber'); exit;
}
if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};\':",.<>?\/\\\\|`~]/', $pwd)) {
    header('Location: ../register.php?error=pwdspecial'); exit;
}
if ($pwd !== $pwd_confirm) {
    header('Location: ../register.php?error=pwdmatch'); exit;
}
if (empty($agree)) {
    header('Location: ../register.php?error=agree'); exit;
}

// Verify reCAPTCHA
$captcha = $_POST['g-recaptcha-response'] ?? '';
if (!$captcha) {
    header('Location: ../register.php?error=captcha'); exit;
}
$verify = file_get_contents(
    'https://www.google.com/recaptcha/api/siteverify?secret=6Ldq4Z0sAAAAAMUE0Uc232vy1rkMbocapMB6vFZj&response=' . urlencode($captcha)
);
$result = json_decode($verify, true);
if (!$result['success']) {
    header('Location: ../register.php?error=captcha'); exit;
}

$conn = getDBConnection();
$chk  = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$chk->bind_param('s', $email);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    header('Location: ../register.php?error=exists'); exit;
}
$chk->close();

$pwd_hashed = password_hash($pwd, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, role) VALUES (?, ?, ?, ?, 'member')");
$stmt->bind_param('ssss', $fname, $lname, $email, $pwd_hashed);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['fname']   = $fname;
    $_SESSION['lname']   = $lname;
    $_SESSION['email']   = $email;
    $_SESSION['role']    = 'member';
    $redirect = $_GET['redirect'] ?? '../index.php';
    header('Location: ' . $redirect);
} else {
    header('Location: ../register.php?error=dbfail');
}

$stmt->close();
$conn->close();
?>