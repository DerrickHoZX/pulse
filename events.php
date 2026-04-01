<?php
session_start();
require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';
$conn = getDBConnection();

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$venue_id = intval($_GET['venue_id'] ?? 0);
$date = trim($_GET['date'] ?? '');

$sql = "SELECT e.event_id, e.title, e.category, e.event_date, e.event_time,
                  v.name AS venue_name, MIN(ss.price) AS min_price,
                  (SELECT ei.image_path
                   FROM event_images ei
                   WHERE ei.event_id = e.event_id AND ei.image_type = 'poster'
                   ORDER BY ei.sort_order ASC, ei.image_id ASC
                   LIMIT 1) AS poster_img,
                  (SELECT ei.image_path
                   FROM event_images ei
                   WHERE ei.event_id = e.event_id AND ei.image_type = 'banner'
                   ORDER BY ei.sort_order ASC, ei.image_id ASC
                   LIMIT 1) AS banner_img
           FROM events e
           JOIN venues v ON e.venue_id = v.venue_id
           LEFT JOIN seat_sections ss ON ss.event_id = e.event_id
           WHERE e.is_active = 1";

$params = [];
$types = '';
if ($q) {
    $like = "%$q%";
    $sql .= " AND (e.title LIKE ? OR v.name LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if ($cat) {
    $sql .= " AND e.category = ?";
    $params[] = $cat;
    $types .= 's';
}
if ($venue_id) {
    $sql .= " AND e.venue_id = ?";
    $params[] = $venue_id;
    $types .= 'i';
}
if ($date) {
    $sql .= " AND e.event_date = ?";
    $params[] = $date;
    $types .= 's';
}
$sql .= " GROUP BY e.event_id ORDER BY e.event_date ASC";

$stmt = $conn->prepare($sql);
if ($params)
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$count = count($events);

$venues = $conn->query("SELECT venue_id, name FROM venues ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$conn->close();

$categories = [
    '' => 'All Events',
    'Rock' => 'Rock & Metal',
    'K-Pop' => 'K-Pop & J-Pop',
    'Hip-Hop' => 'Hip-Hop & R&B',
    'Classical' => 'Classical',
    'Electronic' => 'Electronic / EDM',
    'Jazz & Blues' => 'Jazz & Blues',
    'Theatre' => 'Theatre & Arts',
    'Sports' => 'Sports',
    'Festivals' => 'Festivals',
    'Pop / Jazz' => 'Pop / Jazz',
    'Alternative' => 'Alternative',
    'Pop' => 'Pop',
    'Comedy' => 'Comedy',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>All Events — PULSE Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>

<body>
    <?php include "inc/nav.inc.php" ?>

    <!-- Page header -->
    <div class="events-page-wrap">
        <div class="container-fluid px-5">
            <nav aria-label="breadcrumb" style="margin-bottom:10px;">
                <ol class="breadcrumb mb-0" style="background:none;padding:0;font-size:0.78rem;">
                    <li class="breadcrumb-item"><a href="index.php"
                            style="color:var(--pulse-muted);text-decoration:none;">Home</a></li>
                    <li class="breadcrumb-item active" style="color:var(--pulse-muted);">Events</li>
                </ol>
            </nav>
            <h1 class="page-hero-title" style="margin-bottom:6px;">All <em>Events</em></h1>
            <p style="color:var(--pulse-muted);font-size:0.88rem;margin:0;">
                Concerts, festivals, theatre, sports and more across Singapore.
            </p>

            <!-- Category pills -->
            <form method="GET" action="events.php" id="filterForm">
                <input type="hidden" name="cat" id="catInput" value="<?= htmlspecialchars($cat) ?>">
                <input type="hidden" name="q" id="qInput" value="<?= htmlspecialchars($q) ?>">
                <input type="hidden" name="venue_id" id="venueInput" value="<?= $venue_id ?>">
                <input type="hidden" name="date" id="dateInput" value="<?= htmlspecialchars($date) ?>">

                <div class="eb-cat-pills">
                    <?php foreach ($categories as $val => $label): ?>
                        <button type="button" class="eb-cat-pill <?= $cat === $val ? 'active' : '' ?>"
                            onclick="setCat('<?= $val ?>')">
                            <?= htmlspecialchars($label) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>
    </div>

    <main class="container-fluid px-5 py-5">

        <!-- Filter bar -->
        <div class="d-flex align-items-center gap-3 flex-wrap mb-4 pb-4"
            style="border-bottom:1px solid var(--pulse-border);">

            <div class="hero-search" style="max-width:340px;flex:1;min-width:200px;">
                <svg width="16" height="16" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5" />
                    <path d="M12 12L15.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <input type="text" id="searchInput" value="<?= htmlspecialchars($q) ?>"
                    placeholder="Artist, event, or venue…"
                    onkeydown="if(event.key==='Enter'){document.getElementById('qInput').value=this.value;document.getElementById('filterForm').submit();}">
                <button type="button"
                    onclick="document.getElementById('qInput').value=document.getElementById('searchInput').value;document.getElementById('filterForm').submit();">Search</button>
            </div>

            <select class="filter-select"
                onchange="document.getElementById('venueInput').value=this.value;document.getElementById('filterForm').submit();">
                <option value="">All Venues</option>
                <?php foreach ($venues as $v): ?>
                    <option value="<?= $v['venue_id'] ?>" <?= $venue_id == $v['venue_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" class="filter-select" value="<?= htmlspecialchars($date) ?>" style="color-scheme:dark;"
                onchange="document.getElementById('dateInput').value=this.value;document.getElementById('filterForm').submit();">

            <?php if ($q || $cat || $venue_id || $date): ?>
                <a href="events.php" class="filter-clear-btn">&#10005; Clear filters</a>
            <?php endif; ?>

            <span style="margin-left:auto;font-size:0.8rem;color:var(--pulse-muted);">
                <strong style="color:var(--pulse-white);"><?= $count ?></strong>
                event<?= $count !== 1 ? 's' : '' ?> found
            </span>
        </div>

        <!-- Events grid -->
        <?php if ($count === 0): ?>
            <div style="text-align:center;padding:80px 0;color:var(--pulse-muted);">
                <div style="font-size:3rem;margin-bottom:16px;">🎵</div>
                <p style="margin-bottom:20px;">No events match your filters.</p>
                <a href="events.php" class="btn btn-accent" style="display:inline-flex;align-items:center;gap:8px;">Clear
                    Filters</a>
            </div>
        <?php else: ?>
            <div class="eb-events-grid">
                <?php foreach ($events as $e):
                    $dateStr = date('d M Y', strtotime($e['event_date']));
                    $timeStr = date('g:i A', strtotime($e['event_time'] ?? '00:00:00'));
                    $minPrice = $e['min_price'] ? 'S$' . number_format($e['min_price'], 0) : 'Free';
                    ?>
                    <div class="eb-card" onclick="window.location='event-detail.php?event_id=<?= $e['event_id'] ?>'">

                        <!-- Image -->
                        <div class="eb-card-img">
                            <?php $cardImg = resolveImageSrc($e['poster_img'] ?: $e['banner_img'] ?: ''); ?>
                            <img src="<?= htmlspecialchars($cardImg) ?>" alt="<?= htmlspecialchars($e['title']) ?>"
                                loading="lazy">

                            <!-- Category badge -->
                            <div class="eb-card-cat"><?= htmlspecialchars($e['category']) ?></div>

                            <!-- Heart button -->
                            <button class="eb-card-heart" data-event-id="<?= $e['event_id'] ?>"
                                onclick="toggleHeart(event, this, <?= $e['event_id'] ?>)" aria-label="Save to wishlist">
                                &#9825;
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="eb-card-body">
                            <div class="eb-card-date">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <?= $dateStr ?> · <?= $timeStr ?>
                            </div>
                            <div class="eb-card-title"><?= htmlspecialchars($e['title']) ?></div>
                            <div class="eb-card-venue">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <?= htmlspecialchars($e['venue_name']) ?>
                            </div>
                            <div class="eb-card-footer">
                                <div class="eb-card-price">
                                    <span class="from">From</span>
                                    <span class="amount"><?= $minPrice ?></span>
                                </div>
                                <a href="event-detail.php?event_id=<?= $e['event_id'] ?>" class="eb-card-btn"
                                    onclick="event.stopPropagation()">
                                    Find Tickets
                                    <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                        <path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include "inc/footer.inc.php" ?>

    <script>
        function setCat(val) {
            document.getElementById('catInput').value = val;
            document.getElementById('filterForm').submit();
        }

        function toggleHeart(e, btn, eventId) {
            e.stopPropagation();
            const key = 'pulse_fav_' + eventId;
            const liked = localStorage.getItem(key);
            if (liked) {
                localStorage.removeItem(key);
                btn.classList.remove('liked');
                btn.innerHTML = '&#9825;'; // hollow heart
            } else {
                localStorage.setItem(key, '1');
                btn.classList.add('liked');
                btn.innerHTML = '&#9829;'; // filled heart
            }
        }

        // Restore saved hearts on load
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.eb-card-heart').forEach(btn => {
                const id = btn.dataset.eventId;
                if (localStorage.getItem('pulse_fav_' + id)) {
                    btn.classList.add('liked');
                    btn.innerHTML = '&#9829;';
                }
            });
        });
    </script>
</body>

</html>