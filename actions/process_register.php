<?php
session_start();
require_once '../inc/db.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$fname       = trim($_POST['fname'] ?? '');
$lname       = trim($_POST['lname'] ?? '');
$email       = trim($_POST['email'] ?? '');
$pwd         = $_POST['pwd'] ?? '';
$pwd_confirm = $_POST['pwd_confirm'] ?? '';

if (!$fname || !$lname || !$email || !$pwd || !$pwd_confirm) {
    header('Location: ../register.php?error=missing');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.php?error=invalidemail');
    exit;
}

if (strlen($pwd) < 8) {
    header('Location: ../register.php?error=shortpwd');
    exit;
}

if ($pwd !== $pwd_confirm) {
    header('Location: ../register.php?error=pwdmatch');
    exit;
}

$conn = getDBConnection();
$chk  = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$chk->bind_param('s', $email);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) {
    header('Location: ../register.php?error=exists');
    exit;
}
$chk->close();

$pwd_hash = password_hash($pwd, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (fname, lname, email, pwd_hash) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('ssss', $fname, $lname, $email, $pwd_hash);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['fname']   = $fname;
    $_SESSION['email']   = $email;

    $redirect = $_GET['redirect'] ?? '../index.php';
    header('Location: ' . $redirect);
} else {
    header('Location: ../register.php?error=dbfail');
}

$stmt->close();
$conn->close();
?>
