<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}
require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';

$user_id = intval($_SESSION['user_id']);
$conn = getDBConnection();

$stmt = $conn->prepare(
    "SELECT e.event_id, e.title, e.event_date, e.event_time, e.category,
            v.name AS venue_name,
            (SELECT ei.image_path FROM event_images ei
             WHERE ei.event_id = e.event_id AND ei.image_type = 'poster'
             ORDER BY ei.sort_order ASC, ei.image_id ASC LIMIT 1) AS img_url,
            MIN(ss.price) AS min_price,
            MAX(w.created_at) AS saved_at
     FROM wishlist w
     JOIN events e ON w.event_id = e.event_id
     JOIN venues v ON e.venue_id = v.venue_id
     LEFT JOIN seat_sections ss ON ss.event_id = e.event_id
     WHERE w.user_id = ?
     GROUP BY e.event_id, e.title, e.event_date, e.event_time, e.category, v.name
     ORDER BY saved_at DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Wishlist &mdash; PULSE</title>
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
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
    }
    .wishlist-card {
        background: var(--pulse-surface);
        border: 1px solid var(--pulse-border);
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .wishlist-card:hover { border-color: var(--pulse-accent); }
    .wishlist-card-img {
        width: 100%; height: 180px;
        object-fit: cover; display: block;
    }
    .wishlist-card-body { padding: 16px; }
    .wishlist-card-cat { font-size: 0.6rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--pulse-accent); margin-bottom: 6px; }
    .wishlist-card-title { font-size: 0.95rem; font-weight: 600; color: var(--pulse-white); margin-bottom: 6px; }
    .wishlist-card-meta { font-size: 0.78rem; color: var(--pulse-muted); margin-bottom: 14px; }
    .wishlist-card-footer { display: flex; gap: 8px; }
    .empty-state { text-align: center; padding: 80px 20px; color: var(--pulse-muted); }
    .empty-state h2 { font-family: var(--font-display); font-size: 2rem; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 12px; color: var(--pulse-white); }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <div class="dash-wrap">
        <div class="dash-header">
            <div class="container">
                <h1>My Wishlist</h1>
                <p>Events you've saved — <?= count($events) ?> saved</p>
            </div>
        </div>

        <div class="container">
            <?php if (empty($events)): ?>
            <div class="empty-state">
                <h2>Nothing Saved Yet</h2>
                <p style="margin-bottom:24px;">Browse events and click Save to add them here.</p>
                <a href="events.php" class="btn btn-accent" style="display:inline-flex;padding:12px 28px;">Browse Events</a>
            </div>
            <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($events as $ev):
                    $imgSrc = !empty($ev['img_url']) ? resolveImageSrc($ev['img_url']) : '';
                    $dateStr = date('d M Y', strtotime($ev['event_date']));
                    $timeStr = date('g:i A', strtotime($ev['event_time']));
                ?>
                <div class="wishlist-card">
                    <?php if ($imgSrc): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($ev['title']) ?>" class="wishlist-card-img">
                    <?php else: ?>
                    <div style="width:100%;height:180px;background:var(--pulse-card);display:flex;align-items:center;justify-content:center;color:var(--pulse-muted);font-size:0.8rem;">No Image</div>
                    <?php endif; ?>
                    <div class="wishlist-card-body">
                        <div class="wishlist-card-cat"><?= htmlspecialchars($ev['category']) ?></div>
                        <div class="wishlist-card-title"><?= htmlspecialchars($ev['title']) ?></div>
                        <div class="wishlist-card-meta">
                            <?= $dateStr ?> · <?= $timeStr ?><br>
                            <?= htmlspecialchars($ev['venue_name']) ?>
                            <?php if ($ev['min_price']): ?>
                            · From S$<?= number_format($ev['min_price'], 0) ?>
                            <?php endif; ?>
                        </div>
                        <div class="wishlist-card-footer">
                            <a href="event-detail.php?event_id=<?= $ev['event_id'] ?>" class="btn btn-accent" style="flex:1;justify-content:center;padding:10px;">View Event</a>
                            <button onclick="removeFromWishlist(<?= $ev['event_id'] ?>, this)" class="btn btn-outline-accent" style="padding:10px 14px;">Remove</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "inc/footer.inc.php" ?>
    <script>
    function removeFromWishlist(eventId, btn) {
        fetch('actions/process_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'event_id=' + eventId + '&action=remove'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.closest('.wishlist-card').remove();
            }
        });
    }
    </script>
</body>
</html>