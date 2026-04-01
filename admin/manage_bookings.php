<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();
$message = '';
$error   = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    if ($booking_id && in_array($new_status, ['pending', 'confirmed', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param('si', $new_status, $booking_id);
        $stmt->execute();
        $stmt->close();
        $message = 'Booking #' . $booking_id . ' updated to ' . ucfirst($new_status) . '.';
    }
}

// Filters
$search     = trim($_GET['q'] ?? '');
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT b.booking_id, b.status, b.payment, b.total, b.created_at,
               u.fname, u.lname, u.email,
               e.title AS event_title,
               COUNT(bs.id) AS qty
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN events e ON b.event_id = e.event_id
        LEFT JOIN booking_seats bs ON bs.booking_id = b.booking_id
        WHERE 1=1";

$params = []; $types = '';

if ($search) {
    $like = "%$search%";
    $sql .= " AND (u.fname LIKE ? OR u.lname LIKE ? OR u.email LIKE ? OR e.title LIKE ?)";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}
if ($filter_status) {
    $sql .= " AND b.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$sql .= " GROUP BY b.booking_id ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Summary counts
$counts = $conn->query("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$summary = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
foreach ($counts as $c) $summary[$c['status']] = $c['cnt'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Manage Bookings</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main class="container-fluid px-5 py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-label">Administration</span>
                <h2 class="section-title">Manage <em>Bookings</em></h2>
            </div>
            <a href="admin.php" class="btn btn-outline-light">Back</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <span class="section-label">Overview</span>
                    <h5 class="mb-1">Pending</h5>
                    <h3 class="mb-0"><?= $summary['pending'] ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <span class="section-label">Overview</span>
                    <h5 class="mb-1">Confirmed</h5>
                    <h3 class="mb-0"><?= $summary['confirmed'] ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 rounded-4">
                    <span class="section-label">Overview</span>
                    <h5 class="mb-1">Cancelled</h5>
                    <h3 class="mb-0"><?= $summary['cancelled'] ?></h3>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="manage_bookings.php" class="d-flex gap-2 flex-wrap mb-4">
            <input type="text" name="q" class="form-control admin-form-control"
                   style="max-width:280px;" placeholder="Search customer, email, event..."
                   value="<?= htmlspecialchars($search) ?>">

            <select name="status" class="form-control admin-form-control" style="max-width:160px;"
                    onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending"   <?= $filter_status === 'pending'   ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>

            <button type="submit" class="btn-dark-solid">Search</button>
            <?php if ($search || $filter_status): ?>
                <a href="manage_bookings.php" class="btn btn-outline-light">Clear</a>
            <?php endif; ?>
        </form>

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
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr><td colspan="9" class="text-center">No bookings found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>#<?= $b['booking_id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($b['fname'] . ' ' . $b['lname']) ?>
                                    <div style="font-size:0.72rem;color:var(--pulse-muted);">
                                        <?= htmlspecialchars($b['email']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($b['event_title']) ?></td>
                                <td><?= $b['qty'] ?></td>
                                <td>S$<?= number_format($b['total'], 2) ?></td>
                                <td>
                                    <span style="font-size:0.78rem;color:var(--pulse-muted);">
                                        <?= $b['payment'] === 'paynow' ? 'PayNow' : 'In Person' ?>
                                    </span>
                                </td>
                                <td style="font-size:0.82rem;">
                                    <?= date('d M Y', strtotime($b['created_at'])) ?>
                                </td>
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
                                <td>
                                    <form method="POST" class="d-flex gap-1 flex-wrap">
                                        <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                        <?php if ($b['status'] !== 'confirmed'): ?>
                                            <button type="submit" name="new_status" value="confirmed"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Confirm this booking?')">Confirm</button>
                                        <?php endif; ?>
                                        <?php if ($b['status'] !== 'pending'): ?>
                                            <button type="submit" name="new_status" value="pending"
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Set this booking back to pending?')">Pending</button>
                                        <?php endif; ?>
                                        <?php if ($b['status'] !== 'cancelled'): ?>
                                            <button type="submit" name="new_status" value="cancelled"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Cancel this booking?')">Cancel</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>
</html>