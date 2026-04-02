<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my_bookings.php');
    exit;
}

require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';
$conn = getDBConnection();

function paymentLabel(string $payment): string {
    $normalized = strtolower(preg_replace('/[^a-z]/', '', $payment));

    return match (true) {
        $normalized === 'paynow' => 'PayNow',
        in_array($normalized, ['card', 'creditcard', 'debitcard', 'stripe'], true) => 'Card',
        default => 'Pay in Person',
    };
}

$user_id = intval($_SESSION['user_id']);

$u_stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?");
$u_stmt->bind_param('i', $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();
$u_stmt->close();

$b_stmt = $conn->prepare(
    "SELECT b.booking_id, b.status, b.payment, b.total, b.created_at,
            b.stripe_session_id,
            e.title, e.event_date, e.event_time, e.category,
            (SELECT ei.image_path FROM event_images ei
             WHERE ei.event_id = e.event_id AND ei.image_type = 'poster'
             ORDER BY ei.sort_order ASC, ei.image_id ASC LIMIT 1) AS img_url,
            v.name AS venue_name,
            COUNT(bs.id) AS ticket_count
     FROM bookings b
     JOIN events e ON b.event_id = e.event_id
     JOIN venues v ON e.venue_id = v.venue_id
     LEFT JOIN booking_seats bs ON bs.booking_id = b.booking_id
     WHERE b.user_id = ?
     GROUP BY b.booking_id
     ORDER BY b.created_at DESC"
);
$b_stmt->bind_param('i', $user_id);
$b_stmt->execute();
$bookings = $b_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$b_stmt->close();
$conn->close();

$totalSpent = array_sum(array_column($bookings, 'total'));
$totalTickets = array_sum(array_column($bookings, 'ticket_count'));
$upcoming = array_filter($bookings, function ($b) {
    return strtotime($b['event_date']) >= strtotime('today') && $b['status'] === 'confirmed';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Bookings &mdash; PULSE</title>
    <?php include "inc/head.inc.php" ?>
    <style>
    .dash-wrap { padding: 100px 0 80px; min-height: 80vh; }
    .dash-header {
        background: var(--pulse-surface);
        border-bottom: 1px solid var(--pulse-border);
        padding: 32px 0 28px;
        margin-bottom: 40px;
    }
    .dash-header h1 { font-family: var(--font-display); font-size: 2.5rem; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 4px; }
    .dash-header p { color: var(--pulse-muted); font-size: 0.85rem; margin: 0; }
    .dash-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 36px;
    }
    @media (max-width: 576px) { .dash-stats { grid-template-columns: 1fr; } }
    .stat-box {
        background: var(--pulse-surface);
        border: 1px solid var(--pulse-border);
        padding: 20px 22px;
    }
    .stat-label { font-size: 0.65rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--pulse-muted); margin-bottom: 8px; }
    .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--pulse-white); }
    .stat-sub { font-size: 0.72rem; color: var(--pulse-muted); margin-top: 4px; }
    .section-divider {
        font-size: 1rem; letter-spacing: 0.16em; text-transform: uppercase;
        color: var(--pulse-muted); margin-bottom: 16px; padding-bottom: 12px;
        border-bottom: 1px solid var(--pulse-border);
    }
    .booking-list { display: flex; flex-direction: column; gap: 16px; }
    .booking-card {
        background: var(--pulse-surface);
        border: 1px solid var(--pulse-border);
        display: grid;
        grid-template-columns: 100px 1fr auto;
        overflow: hidden;
    }
    .booking-img img { width: 100px; height: 100%; object-fit: cover; display: block; }
    .booking-info { padding: 16px 20px; }
    .booking-event-cat {
        font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--pulse-accent); margin-bottom: 5px;
    }
    .booking-event-title { font-size: 0.97rem; font-weight: 600; color: var(--pulse-white); margin-bottom: 7px; line-height: 1.3; }
    .booking-meta { font-size: 0.78rem; color: var(--pulse-muted); line-height: 1.9; }
    .booking-meta span { display: inline-block; margin-right: 14px; }
    .booking-right {
        padding: 16px 18px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        border-left: 1px solid var(--pulse-border);
        min-width: 140px;
    }
    @media (max-width: 576px) {
        .booking-card { grid-template-columns: 80px 1fr; }
        .booking-img img { width: 80px; }
        .booking-right { display: none; }
    }
    .booking-status {
        font-size: 0.55rem; letter-spacing: 0.08em; text-transform: uppercase;
        padding: 2px 7px; border-radius: 2px; font-weight: 600;
    }
    .status-confirmed { background: rgba(15,110,86,0.15); color: #4cd964; border: 1px solid rgba(15,110,86,0.3); }
    .status-pending { background: rgba(217,119,6,0.15); color: #f5a623; border: 1px solid rgba(217,119,6,0.3); }
    .status-cancelled { background: rgba(226,75,74,0.1); color: #e24b4a; border: 1px solid rgba(226,75,74,0.2); }
    .booking-total { font-size: 1.05rem; font-weight: 700; color: var(--pulse-white); }
    .booking-total small { font-size: 0.68rem; color: var(--pulse-muted); font-weight: 400; display: block; }
    .booking-ref { font-size: 0.65rem; color: var(--pulse-muted); margin-top: 6px; }
    .booking-card.past-card { border-color: rgba(42,42,42,0.5); }
    .booking-card.past-card .booking-img img { opacity: 0.45; }
    .empty-state { text-align: center; padding: 80px 20px; color: var(--pulse-muted); }
    .empty-state h3 { color: var(--pulse-white); margin-bottom: 10px; }
    .resume-btn {
        font-size: 0.68rem; letter-spacing: 0.08em; text-transform: uppercase;
        padding: 5px 12px; border: 1px solid #f5a623; color: #f5a623;
        background: rgba(217,119,6,0.08); text-decoration: none; display: inline-block;
        margin-top: 8px; transition: background 0.15s;
    }
    .resume-btn:hover { background: rgba(217,119,6,0.2); color: #f5a623; }
    .dash-alert {
        padding: 12px 18px; margin-bottom: 24px; font-size: 0.82rem;
        border: 1px solid; display: flex; align-items: center; gap: 10px;
    }
    .dash-alert-warning { background: rgba(217,119,6,0.08); border-color: rgba(217,119,6,0.3); color: #f5a623; }
    .dash-alert-error   { background: rgba(226,75,74,0.08);  border-color: rgba(226,75,74,0.25);  color: #e24b4a; }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="dash-wrap">
        <div class="dash-header">
            <div class="container-fluid px-5">
                <h1>My Bookings</h1>
                <p>Welcome back, <?= htmlspecialchars(trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''))) ?></p>
            </div>
        </div>

        <div class="container-fluid px-5">
            <?php if (isset($_GET['error'])): ?>
            <div class="dash-alert <?= $_GET['error'] === 'session_expired' ? 'dash-alert-error' : 'dash-alert-warning' ?>">
                <?php if ($_GET['error'] === 'session_expired'): ?>
                    Your Stripe checkout session has expired. Please book again.
                <?php elseif ($_GET['error'] === 'ticket_unavailable'): ?>
                    Ticket download is temporarily unavailable on the server. Please try again later or contact support.
                <?php else: ?>
                    Could not resume payment. Please try booking again or contact support.
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="dash-stats">
                <div class="stat-box">
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?= count($bookings) ?></div>
                    <div class="stat-sub">all time</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Tickets Purchased</div>
                    <div class="stat-value"><?= $totalTickets ?></div>
                    <div class="stat-sub">across all events</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Total Spent</div>
                    <div class="stat-value">S$<?= number_format($totalSpent, 0) ?></div>
                    <div class="stat-sub">booking fees included</div>
                </div>
            </div>

            <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <h3>No bookings yet</h3>
                <p style="margin-bottom:24px;">You haven't booked any events yet. Find something you love.</p>
                <a href="events.php" class="btn btn-accent" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;">Browse Events</a>
            </div>
            <?php else: ?>

            <?php if (!empty($upcoming)): ?>
            <div class="section-divider">Upcoming Events (<?= count($upcoming) ?>)</div>
            <div class="booking-list" style="margin-bottom:36px;">
                <?php foreach ($upcoming as $b):
                    $dateStr = date('d M Y', strtotime($b['event_date']));
                    $timeStr = date('g:i A', strtotime($b['event_time']));
                    $ref = 'PULSE-' . date('Y', strtotime($b['created_at'])) . '-' . str_pad($b['booking_id'], 5, '0', STR_PAD_LEFT);
                ?>
                <div class="booking-card">
                    <div class="booking-img"><img src="<?= htmlspecialchars(resolveImageSrc($b['img_url'] ?? '')) ?>" alt="<?= htmlspecialchars($b['title']) ?> event poster"></div>
                    <div class="booking-info">
                        <div class="booking-event-cat"><?= htmlspecialchars($b['category']) ?></div>
                        <div class="booking-event-title"><?= htmlspecialchars($b['title']) ?></div>
                        <div class="booking-meta">
                            <span><?= $dateStr ?> &middot; <?= $timeStr ?></span>
                            <span><?= htmlspecialchars($b['venue_name']) ?></span>
                            <span><?= intval($b['ticket_count']) ?> ticket<?= intval($b['ticket_count']) !== 1 ? 's' : '' ?></span>
                            <span><?= htmlspecialchars(paymentLabel($b['payment'])) ?></span>
                        </div>
                    </div>
                    <div class="booking-right">
                        <span class="booking-status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span>
                        <div>
                            <div class="booking-total">S$<?= number_format($b['total'], 2) ?><small>total paid</small></div>
                            <div class="booking-ref"><?= htmlspecialchars($ref) ?></div>
                            <?php if ($b['status'] === 'pending' && !empty($b['stripe_session_id'])): ?>
                            <a href="actions/resume_payment.php?booking_id=<?= intval($b['booking_id']) ?>" class="resume-btn">Complete Payment</a>
                            <?php endif; ?>
                            <?php if ($b['status'] === 'confirmed'): ?>
                            <a href="actions/download_ticket.php?booking_id=<?= intval($b['booking_id']) ?>" class="resume-btn" style="background:rgba(82,71,184,0.15);color:#9d96ff;border-color:rgba(82,71,184,0.4);">&#8595; Download Ticket</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="section-divider">All Bookings (<?= count($bookings) ?>)</div>
            <div class="booking-list">
                <?php foreach ($bookings as $b):
                    $dateStr = date('d M Y', strtotime($b['event_date']));
                    $timeStr = date('g:i A', strtotime($b['event_time']));
                    $ref = 'PULSE-' . date('Y', strtotime($b['created_at'])) . '-' . str_pad($b['booking_id'], 5, '0', STR_PAD_LEFT);
                    $isPast = strtotime($b['event_date']) < strtotime('today');
                ?>
                <div class="booking-card<?= $isPast ? ' past-card' : '' ?>">
                    <div class="booking-img"><img src="<?= htmlspecialchars(resolveImageSrc($b['img_url'] ?? '')) ?>" alt="<?= htmlspecialchars($b['title']) ?> event poster"></div>
                    <div class="booking-info">
                        <div class="booking-event-cat"><?= htmlspecialchars($b['category']) ?></div>
                        <div class="booking-event-title"><?= htmlspecialchars($b['title']) ?></div>
                        <div class="booking-meta">
                            <span><?= $dateStr ?> &middot; <?= $timeStr ?></span>
                            <span><?= htmlspecialchars($b['venue_name']) ?></span>
                            <span><?= intval($b['ticket_count']) ?> ticket<?= intval($b['ticket_count']) !== 1 ? 's' : '' ?></span>
                            <span><?= htmlspecialchars(paymentLabel($b['payment'])) ?></span>
                            <?php if ($isPast): ?><span>Past event</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="booking-right">
                        <span class="booking-status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars(ucfirst($b['status'])) ?></span>
                        <div>
                            <div class="booking-total">S$<?= number_format($b['total'], 2) ?><small>total paid</small></div>
                            <div class="booking-ref"><?= htmlspecialchars($ref) ?></div>
                            <?php if ($b['status'] === 'pending' && !empty($b['stripe_session_id'])): ?>
                            <a href="actions/resume_payment.php?booking_id=<?= intval($b['booking_id']) ?>" class="resume-btn">Complete Payment</a>
                            <?php endif; ?>
                            <?php if ($b['status'] === 'confirmed'): ?>
                            <a href="actions/download_ticket.php?booking_id=<?= intval($b['booking_id']) ?>" class="resume-btn" style="background:rgba(82,71,184,0.15);color:#9d96ff;border-color:rgba(82,71,184,0.4);">&#8595; Download Ticket</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align:center;margin-top:40px;">
                <a href="events.php" class="btn btn-outline-accent" style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;">Browse More Events</a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>
