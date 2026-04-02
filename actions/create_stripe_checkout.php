<?php
session_start();
header('Content-Type: application/json');
require_once '../inc/db.inc.php';
require_once '../inc/stripe_config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
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
        'card' => ['card', 'credit_card', 'credit card', 'debit_card', 'debit card', 'stripe'],
    ];

    foreach ($aliases[$requested] ?? [$requested] as $alias) {
        foreach ($allowed as $value) {
            if (strcasecmp($value, $alias) === 0) {
                return $value;
            }
        }
    }

    if ($requested === 'card') {
        foreach ($allowed as $value) {
            $normalized = strtolower(preg_replace('/[^a-z]/', '', $value));
            if (in_array($normalized, ['card', 'creditcard', 'debitcard', 'stripe'], true)) {
                return $value;
            }
        }
    }

    return $allowed[0];
}

$user_id        = intval($_SESSION['user_id']);
$event_id       = intval($_POST['event_id'] ?? 0);
$total          = floatval($_POST['total'] ?? 0);
$selections_raw = $_POST['selections'] ?? '';

if (!$event_id || !$selections_raw || $total <= 0) {
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

$eventCheck = $conn->prepare("SELECT event_date FROM events WHERE event_id = ? AND is_active = 1");
$eventCheck->bind_param('i', $event_id);
$eventCheck->execute();
$eventRow = $eventCheck->get_result()->fetch_assoc();
$eventCheck->close();
if (!$eventRow || strtotime($eventRow['event_date']) < strtotime('today')) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Tickets are no longer available for this event.']);
    exit;
}

$conn->begin_transaction();

try {
    // ── Fetch section labels & prices for Stripe line items ──────────────────
    $secIds       = array_values(array_unique(array_map(fn($s) => intval($s['section_id']), $selections)));
    $placeholders = implode(',', array_fill(0, count($secIds), '?'));
    $types        = str_repeat('i', count($secIds));

    $secStmt = $conn->prepare(
        "SELECT section_id, label, price FROM seat_sections WHERE section_id IN ($placeholders)"
    );
    $secStmt->bind_param($types, ...$secIds);
    $secStmt->execute();
    $secMap = [];
    foreach ($secStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $secMap[$row['section_id']] = $row;
    }
    $secStmt->close();

    // ── Reserve seats (identical lock logic to process_booking.php) ──────────
    $allAssigned = [];
    foreach ($selections as $sel) {
        $section_id = intval($sel['section_id']);
        $qty        = intval($sel['qty']);
        if ($qty < 1) continue;

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
            echo json_encode(['success' => false, 'message' => 'Not enough seats available. Please reduce your quantity.']);
            exit;
        }

        foreach ($picked as &$p) {
            $p['section_id'] = $section_id;
        }
        unset($p);
        $allAssigned = array_merge($allAssigned, $picked);
    }

    // ── Create pending booking ────────────────────────────────────────────────
    $dbPayment = resolvePaymentValue($conn, 'card');
    $ins = $conn->prepare(
        "INSERT INTO bookings (user_id, event_id, status, payment, total)
         VALUES (?, ?, 'pending', ?, ?)"
    );
    $ins->bind_param('iisd', $user_id, $event_id, $dbPayment, $total);
    $ins->execute();
    $booking_id = $conn->insert_id;
    $ins->close();

    // ── Insert booking_seats & mark seats booked ──────────────────────────────
    $seatIns = $conn->prepare(
        "INSERT INTO booking_seats (booking_id, seat_id, section_id, price) VALUES (?, ?, ?, ?)"
    );
    $updSeat = $conn->prepare("UPDATE seats SET status = 'booked' WHERE seat_id = ?");

    foreach ($allAssigned as $seat) {
        $sid   = $seat['seat_id'];
        $secid = $seat['section_id'];
        $price = floatval($secMap[$secid]['price'] ?? 0);

        $seatIns->bind_param('iiid', $booking_id, $sid, $secid, $price);
        $seatIns->execute();

        $updSeat->bind_param('i', $sid);
        $updSeat->execute();
    }
    $seatIns->close();
    $updSeat->close();

    // ── Build Stripe Checkout line items ──────────────────────────────────────
    $lineItems = [];
    $idx = 0;
    foreach ($selections as $sel) {
        $secId = intval($sel['section_id']);
        $qty   = intval($sel['qty']);
        if ($qty < 1 || !isset($secMap[$secId])) continue;

        $sec = $secMap[$secId];
        $lineItems[$idx++] = [
            'price_data' => [
                'currency'     => 'sgd',
                'product_data' => ['name' => 'Ticket — ' . $sec['label']],
                'unit_amount'  => intval(round(floatval($sec['price']) * 100)), // SGD cents
            ],
            'quantity' => $qty,
        ];
    }

    // ── Detect base URL ───────────────────────────────────────────────────────
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Walk up two levels from /actions/create_stripe_checkout.php → project root
    $rootPath = rtrim(dirname(dirname($_SERVER['PHP_SELF'] ?? '/actions/create_stripe_checkout.php')), '/\\');
    $baseUrl  = $scheme . '://' . $host . $rootPath;

    // ── Create Stripe Checkout Session ────────────────────────────────────────
    $stripeData = [
        'mode'        => 'payment',
        'line_items'  => $lineItems,
        'success_url' => $baseUrl . '/booking_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => $baseUrl . '/booking.php?event_id=' . $event_id . '&cancelled=1',
        'metadata'    => [
            'booking_id' => $booking_id,
            'user_id'    => $user_id,
        ],
        'payment_intent_data' => [
            'metadata' => ['booking_id' => $booking_id],
        ],
    ];

    $stripeResult = stripeRequest('POST', 'checkout/sessions', $stripeData);

    if ($stripeResult['code'] !== 200 || empty($stripeResult['body']['url'])) {
        $conn->rollback();
        $errMsg = $stripeResult['body']['error']['message'] ?? 'Stripe session creation failed. Check your API keys.';
        echo json_encode(['success' => false, 'message' => $errMsg]);
        exit;
    }

    $sessionId  = $stripeResult['body']['id'];
    $sessionUrl = $stripeResult['body']['url'];

    // ── Store session ID on booking ───────────────────────────────────────────
    $updSession = $conn->prepare("UPDATE bookings SET stripe_session_id = ? WHERE booking_id = ?");
    $updSession->bind_param('si', $sessionId, $booking_id);
    $updSession->execute();
    $updSession->close();

    $conn->commit();

    echo json_encode(['success' => true, 'redirect' => $sessionUrl]);

} catch (Throwable $e) {
    try { $conn->rollback(); } catch (Throwable $re) {}
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();


