<?php

session_start();
$basePath = "../";


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Optional: admin name for greeting
$adminName = $_SESSION['fname'] ?? 'Admin';

// Temporary dashboard numbers
$totalEvents = 12;
$totalBookings = 245;
$totalUsers = 80;
$totalRevenue = "S$18,450";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>PULSE Admin Dashboard</title>
    <?php include "../inc/head.inc.php"; ?>
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
                    <p
                        style="color: var(--pulse-muted); max-width: 700px; font-weight:300; line-height:1.7; margin-top: 12px;">
                        Welcome back, <?php echo htmlspecialchars($adminName); ?>. Manage events, bookings, users, and
                        reports from one place.
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
                        <h2 class="mb-0"><?php echo $totalEvents; ?></h2>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Total Bookings</h5>
                        <h2 class="mb-0"><?php echo $totalBookings; ?></h2>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Registered Users</h5>
                        <h2 class="mb-0"><?php echo $totalUsers; ?></h2>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4">
                        <span class="section-label">Overview</span>
                        <h5 class="mb-2">Revenue</h5>
                        <h2 class="mb-0"><?php echo $totalRevenue; ?></h2>
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
            </div>
        </section>

        <!-- Recent Admin Activity -->
        <section class="container-fluid px-5 pb-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">Latest Updates</span>
                    <h2 class="section-title">Recent <em>Activity</em></h2>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BLACKPINK event updated with new ticket allocation</td>
                                <td>28 Mar 2026</td>
                                <td><span class="badge bg-success">Completed</span></td>
                            </tr>
                            <tr>
                                <td>Booking #BK1024 marked as paid</td>
                                <td>28 Mar 2026</td>
                                <td><span class="badge bg-primary">Processed</span></td>
                            </tr>
                            <tr>
                                <td>New admin user permission reviewed</td>
                                <td>27 Mar 2026</td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                            </tr>
                            <tr>
                                <td>Cancelled event moved to archive</td>
                                <td>26 Mar 2026</td>
                                <td><span class="badge bg-secondary">Archived</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Reports + Settings -->
        <section class="container-fluid px-5 pb-5 fade-up">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <span class="section-label">Reporting</span>
                        <h4 class="mb-3">Reports</h4>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            Access sales reports, booking summaries, event performance insights, and downloadable
                            records.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="reports.php" class="btn-dark-solid">View Reports</a>
                            <a href="export_report.php" class="btn-dark-solid">Export Data</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <span class="section-label">Administration</span>
                        <h4 class="mb-3">System Settings</h4>
                        <p style="color: var(--pulse-muted); font-weight:300;">
                            Configure admin access, update platform settings, and manage core system controls.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="settings.php" class="btn-dark-solid">Open Settings</a>
                            <a href="logout.php" class="btn-dark-solid">Log Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>

</html>