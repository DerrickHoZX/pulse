<?php
include "../inc/admin_check.inc.php";
$basePath = "../";

$bookings = [
    [
        'booking_id' => 1001,
        'customer_name' => 'Daven Tan',
        'event_name' => 'BLACKPINK World Tour',
        'quantity' => 2,
        'total_price' => 'S$296',
        'status' => 'Confirmed'
    ],
    [
        'booking_id' => 1002,
        'customer_name' => 'Sarah Lim',
        'event_name' => 'TWICE World Tour',
        'quantity' => 1,
        'total_price' => 'S$148',
        'status' => 'Pending'
    ],
    [
        'booking_id' => 1003,
        'customer_name' => 'John Lee',
        'event_name' => 'Lady Gaga',
        'quantity' => 3,
        'total_price' => 'S$564',
        'status' => 'Cancelled'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>PULSE Admin - Manage Bookings</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<div style="margin-top: 100px;"></div>
<body>
    <?php include "../inc/nav.inc.php"; ?>

    <div class="admin-page-offset"></div>

    <main class="container-fluid px-5 py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-label">Administration</span>
                <h2 class="section-title">Manage <em>Bookings</em></h2>
            </div>
            <a href="admin.php" class="btn-dark-solid">Back to Dashboard</a>
        </div>

        <div class="admin-panel-card">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Event</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                                <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                <td><?= htmlspecialchars($booking['event_name']) ?></td>
                                <td><?= htmlspecialchars($booking['quantity']) ?></td>
                                <td><?= htmlspecialchars($booking['total_price']) ?></td>
                                <td>
                                    <?php if ($booking['status'] === 'Confirmed'): ?>
                                        <span class="badge bg-success">Confirmed</span>
                                    <?php elseif ($booking['status'] === 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>
</html>