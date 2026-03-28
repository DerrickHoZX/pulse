<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=dashboard.php');
    exit;
}

require_once 'inc/db.inc.php';
$conn = getDBConnection();

$user_id = intval($_SESSION['user_id']);

$u_stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?");
$u_stmt->bind_param('i', $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();
$u_stmt->close();

$b_stmt = $conn->prepare(
    "SELECT b.booking_id, b.status, b.payment, b.total, b.created_at,
            e.title, e.event_date, e.event_time, e.img_url, e.category,
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
    .dash-header h1 { font-size: 1.8rem; margin-bottom: 4px; }
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
        font-size: 0.65rem; letter-spacing: 0.16em; text-transform: uppercase;
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
        font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
        padding: 4px 10px; border-radius: 2px; font-weight: 600;
    }
    .status-confirmed { background: rgba(15,110,86,0.15); color: #4cd964; border: 1px solid rgba(15,110,86,0.3); }
    .status-pending { background: rgba(217,119,6,0.15); color: #f5a623; border: 1px solid rgba(217,119,6,0.3); }
    .status-cancelled { background: rgba(226,75,74,0.1); color: #e24b4a; border: 1px solid rgba(226,75,74,0.2); }
    .booking-total { font-size: 1.05rem; font-weight: 700; color: var(--pulse-white); }
    .booking-total small { font-size: 0.68rem; color: var(--pulse-muted); font-weight: 400; display: block; }
    .booking-ref { font-size: 0.65rem; color: var(--pulse-muted); margin-top: 6px; }
    .empty-state { text-align: center; padding: 80px 20px; color: var(--pulse-muted); }
    .empty-state h3 { color: var(--pulse-white); margin-bottom: 10px; }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <div class="dash-wrap">
        <div class="dash-header">
            <div class="container-fluid px-5">
                <nav style="margin-bottom:10px;">
                    <ol class="breadcrumb mb-0" style="background:none;padding:0;font-size:0.78rem;">
                        <li class="breadcrumb-item"><a href="index.php" style="color:var(--pulse-muted);text-decoration:none;">Home</a></li>
                        <li class="breadcrumb-item active" style="color:var(--pulse-muted);">My Bookings</li>
                    </ol>
                </nav>
                <h1>My Bookings</h1>
                <p>Welcome back, <strong style="color:var(--pulse-white);"><?= htmlspecialchars(trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''))) ?></strong> &middot; <?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
        </div>

        <div class="container-fluid px-5">
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
                    <div class="booking-img"><img src="<?= htmlspecialchars($b['img_url']) ?>" alt=""></div>
                    <div class="booking-info">
                        <div class="booking-event-cat"><?= htmlspecialchars($b['category']) ?></div>
                        <div class="booking-event-title"><?= htmlspecialchars($b['title']) ?></div>
                        <div class="booking-meta">
                            <span><?= $dateStr ?> &middot; <?= $timeStr ?></span>
                            <span><?= htmlspecialchars($b['venue_name']) ?></span>
                            <span><?= intval($b['ticket_count']) ?> ticket<?= intval($b['ticket_count']) !== 1 ? 's' : '' ?></span>
                            <span><?= $b['payment'] === 'paynow' ? 'PayNow' : 'Pay in Person' ?></span>
                        </div>
                    </div>
                    <div class="booking-right">
                        <span class="booking-status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars($b['status']) ?></span>
                        <div>
                            <div class="booking-total">S$<?= number_format($b['total'], 2) ?><small>total paid</small></div>
                            <div class="booking-ref"><?= htmlspecialchars($ref) ?></div>
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
                <div class="booking-card" style="<?= $isPast ? 'opacity:0.65;' : '' ?>">
                    <div class="booking-img"><img src="<?= htmlspecialchars($b['img_url']) ?>" alt=""></div>
                    <div class="booking-info">
                        <div class="booking-event-cat"><?= htmlspecialchars($b['category']) ?></div>
                        <div class="booking-event-title"><?= htmlspecialchars($b['title']) ?></div>
                        <div class="booking-meta">
                            <span><?= $dateStr ?> &middot; <?= $timeStr ?></span>
                            <span><?= htmlspecialchars($b['venue_name']) ?></span>
                            <span><?= intval($b['ticket_count']) ?> ticket<?= intval($b['ticket_count']) !== 1 ? 's' : '' ?></span>
                            <span><?= $b['payment'] === 'paynow' ? 'PayNow' : 'Pay in Person' ?></span>
                            <?php if ($isPast): ?><span>Past event</span><?php endif; ?>
                        </div>
                    </div>
                    <div class="booking-right">
                        <span class="booking-status status-<?= htmlspecialchars($b['status']) ?>"><?= htmlspecialchars(ucfirst($b['status'])) ?></span>
                        <div>
                            <div class="booking-total">S$<?= number_format($b['total'], 2) ?><small>total paid</small></div>
                            <div class="booking-ref"><?= htmlspecialchars($ref) ?></div>
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
    </div>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>
