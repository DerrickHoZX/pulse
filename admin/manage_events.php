<?php
include "../inc/admin_check.inc.php";
$basePath = "../";

$events = [
    [
        'event_id' => 1,
        'event_name' => 'BLACKPINK World Tour',
        'event_date' => '2026-11-29',
        'venue' => 'National Stadium',
        'price' => 'S$148',
        'status' => 'Active'
    ],
    [
        'event_id' => 2,
        'event_name' => 'TWICE World Tour',
        'event_date' => '2026-10-11',
        'venue' => 'Singapore Indoor Stadium',
        'price' => 'S$148',
        'status' => 'Active'
    ],
    [
        'event_id' => 3,
        'event_name' => 'Lady Gaga',
        'event_date' => '2026-05-22',
        'venue' => 'National Stadium',
        'price' => 'S$188',
        'status' => 'Draft'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Manage Events</title>
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
                <h2 class="section-title">Manage <em>Events</em></h2>
            </div>
            <div class="d-flex gap-2">
                <a href="admin.php" class="btn btn-outline-light">Back</a>
                <a href="add_event.php" class="btn-dark-solid">Add Event</a>
            </div>
        </div>

        <div class="admin-panel-card">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['event_id']) ?></td>
                                <td><?= htmlspecialchars($event['event_name']) ?></td>
                                <td><?= htmlspecialchars($event['event_date']) ?></td>
                                <td><?= htmlspecialchars($event['venue']) ?></td>
                                <td><?= htmlspecialchars($event['price']) ?></td>
                                <td>
                                    <?php if ($event['status'] === 'Active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit_event.php?id=<?= urlencode($event['event_id']) ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                                    </div>
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