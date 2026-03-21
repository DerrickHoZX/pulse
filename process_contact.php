<?php
session_start();
require_once "inc/db.inc.php";

// Sanitize inputs
$name    = trim(htmlspecialchars($_POST['name'] ?? ''));
$email   = trim(htmlspecialchars($_POST['email'] ?? ''));
$phone   = trim(htmlspecialchars($_POST['phone'] ?? ''));
$reason  = trim(htmlspecialchars($_POST['reason'] ?? ''));
$message = trim(htmlspecialchars($_POST['message'] ?? ''));

$errors = [];

// --- Validation ---
if (empty($name) || strlen($name) > 100) {
    $errors[] = "Please enter a valid full name.";
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = "Please enter a valid email address.";
}
if (!empty($phone) && !preg_match('/^[0-9\s\+\-\(\)]{6,20}$/', $phone)) {
    $errors[] = "Please enter a valid phone number.";
}
$valid_reasons = ['Booking Issue', 'Refund Request', 'Event Enquiry', 'Account Help', 'Technical Issue', 'General Enquiry', 'Other'];
if (empty($reason) || !in_array($reason, $valid_reasons)) {
    $errors[] = "Please select a valid reason.";
}
if (empty($message) || strlen($message) > 2000) {
    $errors[] = "Please enter a message (max 2000 characters).";
}

if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_old']    = [
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'reason'  => $reason,
        'message' => $message
    ];
    header("Location: contact.php");
    exit;
}

// --- Save to database ---
$conn = getDBConnection();
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, reason, message) VALUES (?, ?, ?, ?, ?)");
$phone_val = !empty($phone) ? $phone : null;
$stmt->bind_param("sssss", $name, $email, $phone_val, $reason, $message);

if ($stmt->execute()) {
    $_SESSION['contact_success'] = "Thank you $name! Your message has been received. We'll get back to you within 1–2 business days.";
} else {
    $_SESSION['contact_errors'] = ["Something went wrong. Please try again."];
    $_SESSION['contact_old']    = [
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'reason'  => $reason,
        'message' => $message
    ];
}

$stmt->close();
$conn->close();

header("Location: contact.php");
exit;
?>