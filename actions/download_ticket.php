<?php
session_start();
require_once '../inc/db.inc.php';
require_once '../inc/generate_ticket.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$bookingId = intval($_GET['booking_id'] ?? 0);
$userId    = intval($_SESSION['user_id']);

if (!$bookingId) {
    header('Location: ../my_bookings.php');
    exit;
}

$conn = getDBConnection();

// Fetch booking — must belong to this user and be confirmed
$stmt = $conn->prepare(
    "SELECT b.booking_id, b.total, e.title, e.event_date, e.event_time, v.name AS venue_name,
            u.fname, u.lname
     FROM bookings b
     JOIN events e  ON b.event_id  = e.event_id
     JOIN venues v  ON e.venue_id  = v.venue_id
     JOIN users  u  ON b.user_id   = u.user_id
     WHERE b.booking_id = ? AND b.user_id = ? AND b.status = 'confirmed'"
);
$stmt->bind_param('ii', $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header('Location: ../my_bookings.php');
    exit;
}

// Fetch seats
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
$conn->close();

$bookingRef = 'PULSE-' . date('Y', strtotime($booking['event_date'])) . '-' . str_pad($bookingId, 5, '0', STR_PAD_LEFT);
$userName   = trim($booking['fname'] . ' ' . $booking['lname']);

$payload = [
    'booking_ref'     => $bookingRef,
    'event_title'     => $booking['title'],
    'venue_name'      => $booking['venue_name'],
    'event_date'      => date('d M Y', strtotime($booking['event_date'])),
    'event_time'      => date('g:i A', strtotime($booking['event_time'])),
    'total'           => floatval($booking['total']),
    'seats'           => $seatLabels,
    'ticket_category' => 'General Admission',
];

try {
    $pdf = generateTicketPDF($payload, $userName);
} catch (Throwable $e) {
    error_log('[PULSE] Ticket download failed for booking ' . $bookingId . ': ' . $e->getMessage());
    header('Location: ../my_bookings.php?error=ticket_unavailable');
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $bookingRef . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;

