<?php
session_start();
require_once '../inc/db.inc.php';
require_once '../inc/stripe_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$bookingId = intval($_GET['booking_id'] ?? 0);
$userId    = intval($_SESSION['user_id']);

if (!$bookingId) {
    header('Location: ../dashboard.php');
    exit;
}

// Fetch the booking — must belong to this user and be pending
$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT booking_id, stripe_session_id, status
     FROM bookings
     WHERE booking_id = ? AND user_id = ? AND status = 'pending'"
);
$stmt->bind_param('ii', $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$booking || empty($booking['stripe_session_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$sessionId = $booking['stripe_session_id'];

// Ask Stripe for the current session state
$result = stripeRequest('GET', 'checkout/sessions/' . urlencode($sessionId));

if ($result['code'] !== 200 || empty($result['body']['id'])) {
    // Stripe couldn't find the session — redirect back with error
    header('Location: ../dashboard.php?error=session_not_found');
    exit;
}

$stripeSession  = $result['body'];
$paymentStatus  = $stripeSession['payment_status'] ?? '';
$sessionStatus  = $stripeSession['status'] ?? '';
$checkoutUrl    = $stripeSession['url'] ?? '';

if ($paymentStatus === 'paid') {
    // Payment was completed but redirect never happened — confirm it now
    header('Location: ../booking_success.php?session_id=' . urlencode($sessionId));
    exit;
}

if ($sessionStatus === 'open' && $checkoutUrl) {
    // Session still active — send user back to Stripe checkout
    header('Location: ' . $checkoutUrl);
    exit;
}

// Session expired or cancelled — nothing to resume
header('Location: ../dashboard.php?error=session_expired');
exit;
