<?php
session_start();
require_once 'inc/db.inc.php';
require_once 'inc/stripe_config.php';
require_once 'inc/mail.inc.php';
require_once 'inc/generate_ticket.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$session_id = $_GET['session_id'] ?? '';
// Stripe test sessions start with cs_test_, live with cs_live_
if (!$session_id || strpos($session_id, 'cs_') !== 0) {
    header('Location: events.php');
    exit;
}

// ── Verify session with Stripe ────────────────────────────────────────────────
$result = stripeRequest('GET', 'checkout/sessions/' . urlencode($session_id));

if ($result['code'] !== 200 || empty($result['body']['id'])) {
    header('Location: events.php');
    exit;
}

$stripeSession  = $result['body'];
$paymentStatus  = $stripeSession['payment_status'] ?? '';
$bookingId      = intval($stripeSession['metadata']['booking_id'] ?? 0);
$userId         = intval($_SESSION['user_id']);

if ($paymentStatus !== 'paid' || !$bookingId) {
    header('Location: events.php?error=payment_not_completed');
    exit;
}

// ── Confirm the booking (only if still pending & owned by this user) ─────────
$conn = getDBConnection();

$upd = $conn->prepare(
    "UPDATE bookings SET status = 'confirmed', stripe_session_id = ?
     WHERE booking_id = ? AND user_id = ? AND status = 'pending'"
);
$upd->bind_param('sii', $session_id, $bookingId, $userId);
$upd->execute();
$justConfirmed = $upd->affected_rows > 0;
$upd->close();

// ── Fetch booking + event details ─────────────────────────────────────────────
$bookingStmt = $conn->prepare(
    "SELECT b.total, e.title, e.event_date, e.event_time, v.name AS venue_name
     FROM bookings b
     JOIN events e ON b.event_id = e.event_id
     JOIN venues v ON e.venue_id = v.venue_id
     WHERE b.booking_id = ? AND b.user_id = ?"
);
$bookingStmt->bind_param('ii', $bookingId, $userId);
$bookingStmt->execute();
$booking = $bookingStmt->get_result()->fetch_assoc();
$bookingStmt->close();

// ── Fetch assigned seats ──────────────────────────────────────────────────────
$seatsStmt = $conn->prepare(
    "SELECT s.row_label, s.seat_num
     FROM booking_seats bs
     JOIN seats s ON bs.seat_id = s.seat_id
     WHERE bs.booking_id = ?"
);
$seatsStmt->bind_param('i', $bookingId);
$seatsStmt->execute();
$seats      = $seatsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$seatLabels = array_map(fn($s) => $s['row_label'] . $s['seat_num'], $seats);
$seatsStmt->close();

// ── Send confirmation email (only on first confirmation) ──────────────────────
if ($justConfirmed && $booking) {
    $userStmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?");
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    if ($user && !empty($user['email'])) {
        $bookingRef  = 'PULSE-' . date('Y') . '-' . str_pad($bookingId, 5, '0', STR_PAD_LEFT);
        $userName    = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
        $mailPayload = [
            'booking_ref'   => $bookingRef,
            'event_title'   => $booking['title'],
            'venue_name'    => $booking['venue_name'],
            'event_date'    => date('d M Y', strtotime($booking['event_date'])),
            'event_time'    => date('g:i A', strtotime($booking['event_time'])),
            'payment_label' => 'Credit / Debit Card (Stripe)',
            'total'         => floatval($booking['total']),
            'seats'         => $seatLabels,
        ];
        $mailContent = buildBookingConfirmationMail($mailPayload);

        $ticketPdf  = '';
        $pdfFilename = $bookingRef . '.pdf';
        try {
            $ticketPdf = generateTicketPDF($mailPayload, $userName);
        } catch (Throwable $e) {
            error_log('[PULSE] Ticket PDF generation failed: ' . $e->getMessage());
        }

        pulseSendMail(
            $user['email'],
            $userName,
            $mailContent['subject'],
            $mailContent['html'],
            $mailContent['text'],
            $ticketPdf,
            $pdfFilename
        );
    }
}

$conn->close();

$bookingRef = 'PULSE-' . date('Y') . '-' . str_pad($bookingId, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Confirmed &mdash; PULSE</title>
    <?php include 'inc/head.inc.php' ?>
</head>
<body>
    <?php include 'inc/nav.inc.php' ?>

    <main class="booking-page-wrapper">
        <div class="container" style="max-width:700px;padding-top:80px;padding-bottom:60px;">
            <div class="booking-success">
                <h1 class="success-title">Booking Confirmed</h1>
                <div class="success-ref"><?= htmlspecialchars($bookingRef) ?></div>

                <?php if ($seatLabels): ?>
                <div style="margin:12px 0 20px;font-size:0.82rem;color:var(--pulse-muted);line-height:1.8;">
                    Assigned seats: <?= htmlspecialchars(implode(', ', $seatLabels)) ?>
                </div>
                <?php endif; ?>

                <p class="success-body">
                    Payment received. Your booking has been confirmed.<br>
                    A confirmation email has been sent to your registered address.
                </p>

                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                    <a href="events.php" class="btn btn-accent" style="display:inline-flex;align-items:center;gap:8px;">Browse More Events</a>
                    <a href="my_bookings.php" class="btn btn-outline-accent" style="display:inline-flex;align-items:center;gap:8px;">My Bookings</a>
                    <a href="actions/download_ticket.php?booking_id=<?= $bookingId ?>" class="btn btn-outline-accent" style="display:inline-flex;align-items:center;gap:8px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Ticket
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'inc/footer.inc.php' ?>
</body>
</html>
