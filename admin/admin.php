<?php
session_start();
$basePath = "../";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once "../inc/db.inc.php";

$adminName = $_SESSION['fname'] ?? 'Admin';
$conn = getDBConnection();

// Real dashboard stats
$totalEvents   = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0];
$totalBookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetch_row()[0];
$totalRevenue  = $conn->query("SELECT SUM(total) FROM bookings WHERE status = 'confirmed'")->fetch_row()[0] ?? 0;

// Recent bookings activity
$recentBookings = $conn->query("
    SELECT b.booking_id, b.status, b.total, b.created_at,
           u.fname, u.lname,
           e.title AS event_title
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN events e ON b.event_id = e.event_id
    ORDER BY b.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin Dashboard</title>
    <?php include "../inc/head.inc.php"; ?>
    <style>
    .admin-table > :not(caption) > * > * {
        background-color: #141414 !important;
        color: #f5f5f0 !important;
        border-color: #2a2a2a !important;
    }
    .admin-table thead > tr > th {
        background-color: #1a1a1a !important;
        color: #888 !important;
        font-size: 0.65rem !important;
        letter-spacing: 0.18em !important;
        text-transform: uppercase !important;
        font-weight: 500 !important;
        padding: 14px 16px !important;
    }
    .admin-table tbody > tr:hover > td {
        background-color: #1a1a1a !important;
    }
    .admin-panel-card {
        background: #141414 !important;
        border: 1px solid #2a2a2a !important;
        overflow: hidden;
    }
    .card {
        background: #141414 !important;
        border: 1px solid #2a2a2a !important;
        color: #f5f5f0 !important;
    }
    .card h3, .card h5, .card span {
        color: #f5f5f0 !important;
    }
</style>
</head>
<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main>
        <!-- Admin Hero -->
        <section class="container-fluid px-5 py-5 fade-up">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                    <span class="section-label">Control Centre</span>
                    <h2 class="section-title">Admin <em>Dashboard</em></h2>
                    <p style="color: var(--pulse-muted); max-width: 700px; font-weight:300; line-height:1.7; margin-top: 12px;">
                        Welcome back, <?= htmlspecialchars($adminName) ?>. Manage events, bookings, users, and reports from one place.
                    </p>
                </div>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="manage_events.php" class="btn-dark-solid">Manage Events</a>
                    <a href="manage_bookings.php" class="btn-dark-solid">View Bookings</a>
                </div>
            </div>
        </section>

        <!-- Dashboard Summary Cards -->
        <section class="container-fluid px-5 pb-5 fade-up">
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Total Events</h5>
                        <h2 class="mb-0"><?= $totalEvents ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Total Bookings</h5>
                        <h2 class="mb-0"><?= $totalBookings ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Registered Users</h5>
                        <h2 class="mb-0"><?= $totalUsers ?></h2>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Revenue</h5>
                        <h2 class="mb-0">S$<?= number_format($totalRevenue, 2) ?></h2>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="container-fluid px-5 pb-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">Management</span>
                    <h2 class="section-title">Quick <em>Actions</em></h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <h5>Add New Event</h5>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            Create and publish a new event listing with date, venue, pricing, and ticket limits.
                        </p>
                        <a href="add_event.php" class="btn-dark-solid mt-2">Add Event</a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <h5>Manage Events</h5>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            Edit event details, update seat availability, or remove outdated listings.
                        </p>
                        <a href="manage_events.php" class="btn-dark-solid mt-2">Open</a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <h5>Manage Bookings</h5>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            View customer bookings, verify payment status, and handle cancellations.
                        </p>
                        <a href="manage_bookings.php" class="btn-dark-solid mt-2">Open</a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <h5>Manage Users</h5>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            Review registered user accounts and monitor account activity.
                        </p>
                        <a href="manage_users.php" class="btn-dark-solid mt-2">Open</a>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <h5>User Messages</h5>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            View and respond to enquiries submitted through the contact form.
                        </p>
                        <a href="manage_messages.php" class="btn-dark-solid mt-2">View Messages</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Bookings Activity -->
        <section class="container-fluid px-5 pb-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">Latest Updates</span>
                    <h2 class="section-title">Recent <em>Bookings</em></h2>
                </div>
                <a href="manage_bookings.php" class="btn btn-outline-light btn-sm">View All</a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentBookings)): ?>
                                <tr><td colspan="6" class="text-center">No bookings yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentBookings as $b): ?>
                                    <tr>
                                        <td>#<?= $b['booking_id'] ?></td>
                                        <td><?= htmlspecialchars($b['fname'] . ' ' . $b['lname']) ?></td>
                                        <td><?= htmlspecialchars($b['event_title']) ?></td>
                                        <td>S$<?= number_format($b['total'], 2) ?></td>
                                        <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                                        <td>
                                            <?php
                                            $badge = match($b['status']) {
                                                'confirmed' => 'bg-success',
                                                'pending'   => 'bg-warning text-dark',
                                                'cancelled' => 'bg-danger',
                                                default     => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= ucfirst($b['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>
</html>