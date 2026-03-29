<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();

// Fetch all events joined with venue name
$result = $conn->query("
    SELECT e.event_id, e.title, e.event_date, e.category, e.is_active,
           v.name AS venue_name
    FROM events e
    LEFT JOIN venues v ON e.venue_id = v.venue_id
    ORDER BY e.event_date DESC
");
$events = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Manage Events</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

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

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success mb-4">Event added successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success mb-4">Event updated successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success mb-4">Event deleted successfully.</div>
        <?php endif; ?>

        <div class="admin-panel-card">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr><td colspan="7" class="text-center">No events found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= $event['event_id'] ?></td>
                                    <td><?= htmlspecialchars($event['title']) ?></td>
                                    <td><?= htmlspecialchars($event['event_date']) ?></td>
                                    <td><?= htmlspecialchars($event['venue_name'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($event['category'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($event['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="edit_event.php?event_id=<?= $event['event_id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                            <a href="delete_event.php?event_id=<?= $event['event_id'] ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to permanently delete this event?')">Delete</a>
                                        </div>
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