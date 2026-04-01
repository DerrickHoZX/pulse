<?php
session_start();
header('Content-Type: application/json');
require_once '../inc/db.inc.php';
require_once '../inc/mail.inc.php';
require_once '../inc/generate_ticket.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

function bookingErrorResponse(mysqli $conn, string $message, Throwable $e): void {
    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
    }

    $logDir = __DIR__ . '/../uploads';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }

    $detail = get_class($e) . ': ' . $e->getMessage();
    $logLine = sprintf("[%s] %s | %s%s", date('Y-m-d H:i:s'), $message, $detail, PHP_EOL);
    @file_put_contents($logDir . '/booking-error.log', $logLine, FILE_APPEND);
    @error_log($logLine);

    echo json_encode([
        'success' => false,
        'message' => $message . ' ' . $detail,
    ]);
    exit;
}

function getEnumValues(mysqli $conn, string $table, string $column): array {
    $table = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $column = preg_replace('/[^A-Za-z0-9_]/', '', $column);
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    $row = $result ? $result->fetch_assoc() : null;

    if (!$row || empty($row['Type'])) {
        return [];
    }

    if (!preg_match("/^enum\((.*)\)$/i", $row['Type'], $matches)) {
        return [];
    }

    $raw = str_getcsv($matches[1], ',', "'", '\\');
    return array_values(array_filter(array_map('trim', $raw), static fn($v) => $v !== ''));
}

function resolvePaymentValue(mysqli $conn, string $requested): string {
    $allowed = getEnumValues($conn, 'bookings', 'payment');
    if (empty($allowed)) {
        return $requested;
    }

    foreach ($allowed as $value) {
        if (strcasecmp($value, $requested) === 0) {
            return $value;
        }
    }

    $aliases = [
        'paynow' => ['paynow', 'pay_now', 'pay now'],
        'inperson' => ['inperson', 'in_person', 'pay in person', 'cash', 'onsite', 'on site', 'boxoffice', 'box office'],
        'card' => ['card', 'credit card', 'debit card'],
    ];

    foreach ($aliases[$requested] ?? [$requested] as $alias) {
        foreach ($allowed as $value) {
            if (strcasecmp($value, $alias) === 0) {
                return $value;
            }
        }
    }

    if ($requested === 'inperson') {
        foreach ($allowed as $value) {
            $normalized = strtolower(preg_replace('/[^a-z]/', '', $value));
            if (in_array($normalized, ['cash', 'onsite', 'boxoffice', 'payinperson', 'inperson'], true)) {
                return $value;
            }
        }
    }

    if ($requested === 'paynow') {
        foreach ($allowed as $value) {
            $normalized = strtolower(preg_replace('/[^a-z]/', '', $value));
            if (in_array($normalized, ['paynow', 'paynowqr'], true)) {
                return $value;
            }
        }
    }

    return $allowed[0];
}

$user_id = intval($_SESSION['user_id']);
$event_id = intval($_POST['event_id'] ?? 0);
$payment = $_POST['payment'] ?? '';
$total = floatval($_POST['total'] ?? 0);
$selections_raw = $_POST['selections'] ?? '';

if (!$event_id || !in_array($payment, ['paynow', 'inperson'], true) || !$selections_raw || $total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing booking data.']);
    exit;
}

$selections = json_decode($selections_raw, true);
if (!$selections || !is_array($selections)) {
    echo json_encode(['success' => false, 'message' => 'Invalid selections.']);
    exit;
}

$totalQty = array_sum(array_column($selections, 'qty'));
if ($totalQty < 1 || $totalQty > 8) {
    echo json_encode(['success' => false, 'message' => 'Please select between 1 and 8 tickets.']);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = getDBConnection();
$conn->begin_transaction();

try {
    $dbPayment = resolvePaymentValue($conn, $payment);
    $allAssigned = [];

    foreach ($selections as $sel) {
        $section_id = intval($sel['section_id']);
        $qty = intval($sel['qty']);
        if ($qty < 1) {
            continue;
        }

        $pick = $conn->prepare(
            "SELECT seat_id, row_label, seat_num
             FROM seats
             WHERE section_id = ? AND status = 'available'
             ORDER BY row_label ASC, seat_num ASC
             LIMIT ?
             FOR UPDATE"
        );
        $pick->bind_param('ii', $section_id, $qty);
        $pick->execute();
        $picked = $pick->get_result()->fetch_all(MYSQLI_ASSOC);
        $pick->close();

        if (count($picked) < $qty) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Not enough seats available. Please reduce your quantity and try again.'
            ]);
            exit;
        }

        $allAssigned = array_merge($allAssigned, $picked);
    }

    $qr_token = bin2hex(random_bytes(16));
    $ins = $conn->prepare(
        "INSERT INTO bookings (user_id, event_id, status, payment, total, qr_token)
         VALUES (?, ?, 'confirmed', ?, ?, ?)"
    );
    $ins->bind_param('iisds', $user_id, $event_id, $dbPayment, $total, $qr_token);
    $ins->execute();
    $booking_id = $conn->insert_id;
    $ins->close();

    $seatIds = array_column($allAssigned, 'seat_id');
    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $types = str_repeat('i', count($seatIds));

    $info = $conn->prepare(
        "SELECT s.seat_id, s.section_id, ss.price
         FROM seats s
         JOIN seat_sections ss ON s.section_id = ss.section_id
         WHERE s.seat_id IN ($placeholders)"
    );
    $info->bind_param($types, ...$seatIds);
    $info->execute();
    $seatInfo = [];
    foreach ($info->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $seatInfo[$row['seat_id']] = $row;
    }
    $info->close();

    $seatIns = $conn->prepare(
        "INSERT INTO booking_seats (booking_id, seat_id, section_id, price) VALUES (?, ?, ?, ?)"
    );
    $upd = $conn->prepare("UPDATE seats SET status = 'booked' WHERE seat_id = ?");

    foreach ($allAssigned as $seat) {
        $sid = $seat['seat_id'];
        $secid = $seatInfo[$sid]['section_id'];
        $price = $seatInfo[$sid]['price'];

        $seatIns->bind_param('iiid', $booking_id, $sid, $secid, $price);
        $seatIns->execute();

        $upd->bind_param('i', $sid);
        $upd->execute();
    }
    $seatIns->close();
    $upd->close();

    $conn->commit();

    $booking_ref = 'PULSE-' . date('Y') . '-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);

    $eventStmt = $conn->prepare(
        "SELECT e.title, e.event_date, e.event_time, v.name AS venue_name
         FROM events e
         JOIN venues v ON e.venue_id = v.venue_id
         WHERE e.event_id = ?"
    );
    $eventStmt->bind_param('i', $event_id);
    $eventStmt->execute();
    $mailEvent = $eventStmt->get_result()->fetch_assoc();
    $eventStmt->close();

    $userStmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?");
    $userStmt->bind_param('i', $user_id);
    $userStmt->execute();
    $mailUser = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    $seatLabels = array_map(function ($seat) {
        return $seat['row_label'] . $seat['seat_num'];
    }, $allAssigned);

    $mailResult = ['success' => false, 'message' => 'Mail skipped.'];
    if ($mailEvent && $mailUser && !empty($mailUser['email'])) {
        $mailUserName = trim(($mailUser['fname'] ?? '') . ' ' . ($mailUser['lname'] ?? ''));
        $mailPayload  = [
            'booking_ref'   => $booking_ref,
            'event_title'   => $mailEvent['title'],
            'venue_name'    => $mailEvent['venue_name'],
            'event_date'    => date('d M Y', strtotime($mailEvent['event_date'])),
            'event_time'    => date('g:i A', strtotime($mailEvent['event_time'])),
            'payment_label' => $payment === 'paynow' ? 'PayNow' : 'Pay in Person',
            'total'         => $total,
            'seats'         => $seatLabels,
            'qr_token'      => $qr_token,
        ];
        $mailContent = buildBookingConfirmationMail($mailPayload);

        $ticketPdf   = '';
        $pdfFilename = $booking_ref . '.pdf';
        try {
            $ticketPdf = generateTicketPDF($mailPayload, $mailUserName);
        } catch (Throwable $e) {
            error_log('[PULSE] Ticket PDF generation failed: ' . $e->getMessage());
        }

        $mailResult = pulseSendMail(
            $mailUser['email'],
            $mailUserName,
            $mailContent['subject'],
            $mailContent['html'],
            $mailContent['text'],
            $ticketPdf,
            $pdfFilename
        );
    }

    echo json_encode([
        'success' => true,
        'booking_ref' => $booking_ref,
        'booking_id' => $booking_id,
        'assigned_seats' => $allAssigned,
        'mail_sent' => $mailResult['success'],
        'mail_message' => $mailResult['message'],
        'stored_payment' => $dbPayment,
    ]);
} catch (Throwable $e) {
    bookingErrorResponse($conn, 'Booking failed on the server.', $e);
}

$conn->close();
?>
