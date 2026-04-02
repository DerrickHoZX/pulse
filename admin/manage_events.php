<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();

// Sorting
$allowed_sorts = ['event_id', 'title', 'event_date', 'category', 'is_active'];
$sort = in_array($_GET['sort'] ?? '', $allowed_sorts) ? $_GET['sort'] : 'event_date';
$dir  = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$next_dir = $dir === 'asc' ? 'desc' : 'asc';

$result = $conn->query("
    SELECT e.event_id, e.title, e.event_date, e.category, e.is_active,
           v.name AS venue_name
    FROM events e
    LEFT JOIN venues v ON e.venue_id = v.venue_id
    ORDER BY e.$sort $dir
");
$events = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Manage Events</title>
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
                            <th>
                                <a href="manage_events.php?sort=event_id&dir=<?= $sort === 'event_id' ? $next_dir : 'asc' ?>"
                                style="color:inherit;text-decoration:none;">
                                    ID <?= $sort === 'event_id' ? ($dir === 'asc' ? '↑' : '↓') : '↕' ?>
                                </a>
                            </th>
                            <th>
                                <a href="manage_events.php?sort=title&dir=<?= $sort === 'title' ? $next_dir : 'asc' ?>"
                                style="color:inherit;text-decoration:none;">
                                    Event Name <?= $sort === 'title' ? ($dir === 'asc' ? '↑' : '↓') : '↕' ?>
                                </a>
                            </th>
                            <th>
                                <a href="manage_events.php?sort=event_date&dir=<?= $sort === 'event_date' ? $next_dir : 'asc' ?>"
                                style="color:inherit;text-decoration:none;">
                                    Date <?= $sort === 'event_date' ? ($dir === 'asc' ? '↑' : '↓') : '↕' ?>
                                </a>
                            </th>
                            <th>Venue</th>
                            <th>
                                <a href="manage_events.php?sort=category&dir=<?= $sort === 'category' ? $next_dir : 'asc' ?>"
                                style="color:inherit;text-decoration:none;">
                                    Category <?= $sort === 'category' ? ($dir === 'asc' ? '↑' : '↓') : '↕' ?>
                                </a>
                            </th>
                            <th>
                                <a href="manage_events.php?sort=is_active&dir=<?= $sort === 'is_active' ? $next_dir : 'asc' ?>"
                                style="color:inherit;text-decoration:none;">
                                    Status <?= $sort === 'is_active' ? ($dir === 'asc' ? '↑' : '↓') : '↕' ?>
                                </a>
                            </th>
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
                                            
                                            <form method="POST" action="delete_event.php" style="display:inline;">
                                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to permanently delete this event?')">
                                                    Delete
                                                </button>
                                            </form>

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