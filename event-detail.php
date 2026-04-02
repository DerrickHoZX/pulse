<?php
session_start();
require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    header('Location: events.php');
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare(
    "SELECT e.*, v.name AS venue_name, v.address AS venue_address
     FROM events e JOIN venues v ON e.venue_id = v.venue_id
     WHERE e.event_id = ? AND e.is_active = 1"
);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) {
    header('Location: events.php');
    exit;
}

$sec_stmt = $conn->prepare(
    "SELECT ss.section_id, ss.label, ss.price, ss.total_seats,
            COUNT(CASE WHEN s.status='available' THEN 1 END) AS avail_count
     FROM seat_sections ss
     LEFT JOIN seats s ON s.section_id = ss.section_id
     WHERE ss.event_id = ?
     GROUP BY ss.section_id ORDER BY ss.price DESC"
);
$sec_stmt->bind_param('i', $event_id);
$sec_stmt->execute();
$sections = $sec_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$rel_stmt = $conn->prepare(
    "SELECT e.event_id, e.title, e.event_date, e.category,
            v.name AS venue_name, MIN(ss.price) AS min_price,
            (SELECT ei.image_path FROM event_images ei
             WHERE ei.event_id = e.event_id AND ei.image_type = 'banner'
             ORDER BY ei.sort_order LIMIT 1) AS banner_img
     FROM events e
     JOIN venues v ON e.venue_id = v.venue_id
     LEFT JOIN seat_sections ss ON ss.event_id = e.event_id
     WHERE e.category = ? AND e.event_id != ? AND e.is_active = 1
     GROUP BY e.event_id ORDER BY e.event_date ASC LIMIT 3"
);
$rel_stmt->bind_param('si', $event['category'], $event_id);
$rel_stmt->execute();
$related = $rel_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$images = getAllEventImages($conn, $event_id);
$conn->close();

// Check wishlist status
$isSaved = false;
if (isset($_SESSION['user_id'])) {
    $conn2 = getDBConnection();
    $savedStmt = $conn2->prepare("SELECT id FROM wishlist WHERE user_id = ? AND event_id = ?");
    $savedStmt->bind_param('ii', $_SESSION['user_id'], $event_id);
    $savedStmt->execute();
    $isSaved = $savedStmt->get_result()->num_rows > 0;
    $savedStmt->close();
    $conn2->close();
}

$dateStr = date('d M Y', strtotime($event['event_date']));
$timeStr = date('g:i A', strtotime($event['event_time']));
$minPrice = $sections ? min(array_column($sections, 'price')) : null;
$totalAvail = array_sum(array_column($sections, 'avail_count'));
$isPast = strtotime($event['event_date']) < strtotime('today');

function cleanLabel(string $label): string
{
    $clean = preg_replace('/^Cat\s*\d+\s*[^A-Za-z0-9]*\s*/i', '', trim($label));
    return preg_replace('/^[^A-Za-z0-9]+/', '', $clean ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?= htmlspecialchars($event['title']) ?> &mdash; PULSE</title>
    <?php include "inc/head.inc.php" ?>
    <style>
        .detail-banner {
            height: 420px;
            position: relative;
            overflow: hidden;
        }

        .detail-banner-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            filter: none;
            transform: none;
        }

        .detail-banner-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.25);
        }

        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 40px;
            align-items: start;
            padding: 0 0 80px;
        }

        @media (max-width: 992px) {
            .detail-layout {
                grid-template-columns: 1fr;
            }

            .ticket-sticky {
                position: static !important;
            }
        }

        .poster-wrap {
            margin-top: -180px;
            position: relative;
            z-index: 2;
            max-width: 200px;
        }

        .poster-inner {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
            aspect-ratio: 2/3;
        }

        .poster-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .detail-section {
            background: var(--pulse-surface);
            border: 1px solid var(--pulse-border);
            padding: 22px 26px;
            margin-bottom: 16px;
        }

        .detail-section-title {
            font-size: 0.62rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--pulse-muted);
            margin-bottom: 16px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 576px) {
            .meta-grid {
                grid-template-columns: 1fr;
            }
        }

        .meta-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .meta-icon {
            width: 34px;
            height: 34px;
            background: rgba(82, 71, 184, 0.12);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--pulse-accent);
            flex-shrink: 0;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .meta-label {
            font-size: 0.68rem;
            color: var(--pulse-muted);
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 0.86rem;
            font-weight: 500;
            color: var(--pulse-white);
        }

        .ticket-sticky {
            position: sticky;
            top: 90px;
        }

        .ticket-card {
            background: var(--pulse-surface);
            border: 1px solid var(--pulse-border);
            padding: 22px;
        }

        .ticket-card-title {
            font-size: 0.62rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--pulse-muted);
            margin-bottom: 18px;
        }

        .tier-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 0;
            border-bottom: 1px solid var(--pulse-border);
        }

        .tier-row:last-of-type {
            border-bottom: none;
        }

        .tier-cat {
            font-size: 0.58rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--pulse-accent);
            margin-bottom: 2px;
        }

        .tier-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--pulse-white);
        }

        .tier-avail {
            font-size: 0.7rem;
            color: var(--pulse-muted);
            margin-top: 2px;
        }

        .tier-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--pulse-white);
            text-align: right;
        }

        .tier-price small {
            font-size: 0.65rem;
            color: var(--pulse-muted);
            font-weight: 400;
            display: block;
        }

        .btn-get-tickets {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: var(--pulse-accent);
            color: #fff;
            border: none;
            padding: 15px;
            font-family: var(--font-body);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: none;
            margin-top: 18px;
            transition: background 0.2s;
            cursor: pointer;
        }

        .btn-get-tickets:hover {
            background: #4239a6;
            color: #fff;
            text-decoration: none;
        }

        .btn-get-tickets.disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include "inc/nav.inc.php" ?>
    <?php
    $bannerSrc = resolveImageSrc($images['banner'] ?: $images['poster'] ?: '');
    ?>
    <div style="margin-top: 100px;"></div>

    <div class="detail-banner">
        <?php if (!empty($bannerSrc)): ?>
            <div class="detail-banner-bg" style="background-image: url('<?= htmlspecialchars($bannerSrc) ?>');"></div>
        <?php endif; ?>
        <div class="detail-banner-overlay"></div>
    </div>

    <div class="container-fluid px-5">
        <nav aria-label="breadcrumb" style="padding:14px 0 0;">
            <ol class="breadcrumb mb-0" style="background:none;padding:0;font-size:0.78rem;">
                <li class="breadcrumb-item"><a href="index.php"
                        style="color:var(--pulse-muted);text-decoration:none;">Home</a></li>
                <li class="breadcrumb-item"><a href="events.php"
                        style="color:var(--pulse-muted);text-decoration:none;">Events</a></li>
            </ol>
        </nav>

        <div class="detail-layout">
            <div>
                <div class="d-flex gap-4 align-items-start mb-5" style="flex-wrap:wrap;">
                    <div class="poster-wrap d-none d-md-block">
                        <div class="poster-inner">
                            <img src="<?= resolveImageSrc($images['poster'] ?: $images['banner']) ?>"
                                alt="<?= htmlspecialchars($event['title']) ?>">
                        </div>
                    </div>
                    <div style="flex:1;min-width:220px;padding-top:8px;">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span
                                style="background:rgba(82,71,184,0.18);color:var(--pulse-accent);border:1px solid rgba(82,71,184,0.3);font-size:0.65rem;letter-spacing:0.1em;text-transform:uppercase;padding:4px 12px;">
                                <?= htmlspecialchars($event['category']) ?>
                            </span>
                            <?php if ($totalAvail > 0 && $totalAvail < 50): ?>
                                <span
                                    style="background:rgba(217,82,122,0.12);color:#D9527A;border:1px solid rgba(217,82,122,0.25);font-size:0.65rem;letter-spacing:0.1em;text-transform:uppercase;padding:4px 12px;">Selling Fast</span>
                            <?php elseif ($totalAvail === 0): ?>
                                <span
                                    style="background:rgba(226,75,74,0.1);color:#e24b4a;border:1px solid rgba(226,75,74,0.25);font-size:0.65rem;letter-spacing:0.1em;text-transform:uppercase;padding:4px 12px;">Sold Out</span>
                            <?php endif; ?>
                        </div>

                        <h1
                            style="font-size:clamp(1.4rem,3vw,2.4rem);line-height:1.15;margin-bottom:14px;color:var(--pulse-white);">
                            <?= htmlspecialchars($event['title']) ?>
                        </h1>

                        <div
                            style="display:flex;flex-wrap:wrap;gap:18px;color:var(--pulse-muted);font-size:0.83rem;margin-bottom:20px;">
                            <span>Date: <?= $dateStr ?></span>
                            <span>Time: <?= $timeStr ?></span>
                            <span>Venue: <?= htmlspecialchars($event['venue_name']) ?></span>
                        </div>

                        <div class="d-flex gap-3 flex-wrap">
                            <?php if ($isPast): ?>
                                <button class="btn btn-accent" disabled
                                    style="opacity:0.4;display:inline-flex;align-items:center;gap:8px;padding:12px 26px;">Past Event</button>
                            <?php elseif ($totalAvail > 0): ?>
                                <a href="booking.php?event_id=<?= $event_id ?>" class="btn btn-accent"
                                    style="display:inline-flex;align-items:center;gap:8px;padding:12px 26px;">
                                    Get Tickets
                                </a>
                            <?php else: ?>
                                <button class="btn btn-accent" disabled
                                    style="opacity:0.4;display:inline-flex;align-items:center;gap:8px;padding:12px 26px;">Sold Out</button>
                            <?php endif; ?>

                            <button onclick="toggleHeart(<?= $event_id ?>)" id="heartBtn" class="btn btn-outline-accent"
                                data-saved="<?= $isSaved ? '1' : '0' ?>"
                                style="display:inline-flex;align-items:center;gap:8px;padding:12px 18px;">
                                <span id="heartText"><?= $isSaved ? 'Saved' : 'Save' ?></span>
                            </button>

                            <button onclick="openSeatMap()" class="btn btn-outline-accent"
                                style="display:inline-flex;align-items:center;gap:8px;padding:12px 18px;">
                                View Seat Map
                            </button>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">About This Event</div>
                    <p style="color:var(--pulse-muted);font-size:0.88rem;line-height:1.85;margin:0;">
                        <?= nl2br(htmlspecialchars($event['description'] ?? 'More details coming soon.')) ?>
                    </p>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">Event Details</div>
                    <div class="meta-grid">
                        <div class="meta-item">
                            <div class="meta-icon">DT</div>
                            <div>
                                <div class="meta-label">Date &amp; Time</div>
                                <div class="meta-value"><?= $dateStr ?> &middot; <?= $timeStr ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">VN</div>
                            <div>
                                <div class="meta-label">Venue</div>
                                <div class="meta-value"><?= htmlspecialchars($event['venue_name']) ?></div>
                                <div style="font-size:0.7rem;color:var(--pulse-muted);margin-top:2px;">
                                    <?= htmlspecialchars($event['venue_address'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">CT</div>
                            <div>
                                <div class="meta-label">Category</div>
                                <div class="meta-value"><?= htmlspecialchars($event['category']) ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">PR</div>
                            <div>
                                <div class="meta-label">From</div>
                                <div class="meta-value"><?= $minPrice ? 'S$' . number_format($minPrice, 0) : 'Free' ?></div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">AG</div>
                            <div>
                                <div class="meta-label">Age Restriction</div>
                                <div class="meta-value">All Ages Welcome</div>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">DR</div>
                            <div>
                                <div class="meta-label">Doors Open</div>
                                <div class="meta-value">1 hour before show</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">Know Before You Go</div>
                    <ul style="color:var(--pulse-muted);font-size:0.84rem;line-height:2.1;padding-left:18px;margin:0;">
                        <li>Valid ID required for entry</li>
                        <li>No professional cameras or recording equipment</li>
                        <li>Tickets are non-refundable unless event is cancelled</li>
                        <li>Gates open 1 hour before show time</li>
                        <li>No large bags permitted inside the venue</li>
                        <li>Seats are best-available, auto-assigned on booking</li>
                    </ul>
                </div>

                <?php if (!empty($related)): ?>
                    <div style="margin-top:36px;">
                        <span class="section-label">More Like This</span>
                        <h2 class="section-title" style="margin-bottom:20px;">More
                            <em><?= htmlspecialchars($event['category']) ?></em> Events
                        </h2>
                        <div class="related-grid">
                            <?php foreach ($related as $r): ?>
                                <a href="event-detail.php?event_id=<?= $r['event_id'] ?>" class="eb-card"
                                    style="text-decoration:none;display:flex;flex-direction:column;">
                                    <div class="eb-card-img" style="height:150px;">
                                        <img src="<?= htmlspecialchars(resolveImageSrc($r['banner_img'] ?? '')) ?>"
                                            alt="<?= htmlspecialchars($r['title']) ?>" loading="lazy">
                                        <div class="eb-card-cat"><?= htmlspecialchars($r['category'] ?? '') ?></div>
                                    </div>
                                    <div class="eb-card-body">
                                        <div class="eb-card-date"><?= date('d M Y', strtotime($r['event_date'])) ?></div>
                                        <div class="eb-card-title"><?= htmlspecialchars($r['title']) ?></div>
                                        <div class="eb-card-venue"><?= htmlspecialchars($r['venue_name']) ?></div>
                                        <div class="eb-card-footer">
                                            <div class="eb-card-price">
                                                <span class="from">From</span>
                                                <span class="amount"><?= $r['min_price'] ? 'S$' . number_format($r['min_price'], 0) : 'Free' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ticket-sticky">
                <div class="ticket-card">
                    <div class="ticket-card-title">Ticket Prices</div>

                    <?php if ($totalAvail === 0): ?>
                        <div
                            style="background:rgba(226,75,74,0.1);border:1px solid rgba(226,75,74,0.3);color:#e24b4a;text-align:center;padding:10px;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:14px;">
                            This event is sold out
                        </div>
                    <?php endif; ?>

                    <?php foreach ($sections as $i => $sec):
                        $soldOut = $sec['avail_count'] < 1; ?>
                        <div class="tier-row">
                            <div>
                                <div class="tier-cat">Cat <?= $i + 1 ?></div>
                                <div class="tier-name"><?= htmlspecialchars(cleanLabel($sec['label'])) ?></div>
                                <div class="tier-avail">
                                    <?php if ($soldOut): ?>
                                        <span style="color:#e24b4a;">Sold out</span>
                                    <?php elseif ($sec['avail_count'] < 20): ?>
                                        <svg width="8" height="8" viewBox="0 0 8 8"
                                            style="margin-right:5px;vertical-align:middle;flex-shrink:0;">
                                            <circle cx="4" cy="4" r="4" fill="#D9527A" />
                                        </svg><span style="color:#D9527A;">Only <?= $sec['avail_count'] ?> left</span>
                                    <?php else: ?>
                                        <svg width="8" height="8" viewBox="0 0 8 8"
                                            style="margin-right:5px;vertical-align:middle;flex-shrink:0;">
                                            <circle cx="4" cy="4" r="4" fill="#0F6E56" />
                                        </svg><?= number_format($sec['avail_count']) ?> available
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tier-price">S$<?= number_format($sec['price'], 0) ?><small>per ticket</small></div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($isPast): ?>
                        <div class="btn-get-tickets disabled">Past Event</div>
                    <?php elseif ($totalAvail > 0): ?>
                        <a href="booking.php?event_id=<?= $event_id ?>" class="btn-get-tickets">Get Tickets Now</a>
                    <?php else: ?>
                        <div class="btn-get-tickets disabled">Sold Out</div>
                    <?php endif; ?>

                    <div style="font-size:0.7rem;color:var(--pulse-muted);text-align:center;margin-top:12px;line-height:1.6;">
                        Secure booking &middot; Instant confirmation<br>Seats auto-assigned at best available
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="seatmap-modal-overlay" id="seatMapModal" onclick="if(event.target===this)closeSeatMap();">
        <div class="seatmap-modal">
            <div class="seatmap-modal-header">
                <div>
                    <div class="seatmap-modal-title"><?= htmlspecialchars($event['venue_name']) ?> &mdash; Seating Plan</div>
                    <div class="seatmap-modal-sub">For reference only. Layout may vary by event.</div>
                </div>
                <button class="seatmap-modal-close" onclick="closeSeatMap()" aria-label="Close seating plan">X</button>
            </div>
            <div class="seatmap-modal-body">
                <?php if ($images['seatmap']): ?>
                    <img src="<?= htmlspecialchars(resolveImageSrc($images['seatmap'])) ?>"
                        alt="<?= htmlspecialchars($event['venue_name']) ?> seating plan"
                        style="width:100%;height:auto;display:block;"
                        onerror="this.style.display='none';document.getElementById('seatmapUnavailable').style.display='flex';">
                    <div id="seatmapUnavailable" style="display:none;align-items:center;justify-content:center;padding:48px 24px;color:var(--pulse-muted);font-size:0.85rem;">Seating map not available.</div>
                <?php else: ?>
                    <div style="display:flex;align-items:center;justify-content:center;padding:48px 24px;color:var(--pulse-muted);font-size:0.85rem;">Seating map not available.</div>
                <?php endif; ?>
            </div>
            <?php if ($images['seatmap']): ?>
            <div style="padding:10px 20px;font-size:0.7rem;color:var(--pulse-muted);border-top:1px solid var(--pulse-border);">
                Colour indicates price category &middot; Seat plan is not drawn to scale &middot; Layout subject to change
            </div>
            <?php endif; ?>
        </div>
    </div>
    <style>
        .seatmap-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.88); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; pointer-events: none; transition: opacity 0.25s; }
        .seatmap-modal-overlay.open { opacity: 1; pointer-events: all; }
        .seatmap-modal { background: var(--pulse-surface); border: 1px solid var(--pulse-border); width: 100%; max-width: 820px; max-height: 90vh; display: flex; flex-direction: column; transform: translateY(16px); transition: transform 0.25s; }
        .seatmap-modal-overlay.open .seatmap-modal { transform: translateY(0); }
        .seatmap-modal-header { display: flex; align-items: flex-start; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--pulse-border); flex-shrink: 0; }
        .seatmap-modal-title { font-size: 0.92rem; font-weight: 600; color: var(--pulse-white); margin-bottom: 3px; }
        .seatmap-modal-sub { font-size: 0.7rem; color: var(--pulse-muted); }
        .seatmap-modal-close { background: none; border: none; color: var(--pulse-muted); cursor: pointer; font-size: 1.1rem; padding: 2px; }
        .seatmap-modal-body { overflow-y: auto; flex: 1; background: #111; }
    </style>
    <script>
        function openSeatMap() { document.getElementById('seatMapModal').classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeSeatMap() { document.getElementById('seatMapModal').classList.remove('open'); document.body.style.overflow = ''; }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSeatMap(); });
    </script>

    <div id="toast"
        style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(20px);background:var(--pulse-surface);border:1px solid var(--pulse-border);padding:10px 22px;font-size:0.8rem;color:var(--pulse-white);opacity:0;transition:all 0.3s;z-index:9999;pointer-events:none;">
        Copied!
    </div>

    <?php include "inc/footer.inc.php" ?>
    <script>
        const EID = <?= $event_id ?>;

        function syncHeartLabels(saved) {
            ['heartText', 'heartText2'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = saved ? 'Saved' : 'Save';
            });
        }

        function toggleHeart(id) {
            const btn = document.getElementById('heartBtn');
            const currentlySaved = btn.dataset.saved === '1';
            const action = currentlySaved ? 'remove' : 'add';

            fetch('actions/process_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'event_id=' + id + '&action=' + action
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.dataset.saved = data.saved ? '1' : '0';
                    syncHeartLabels(data.saved);
                    showToast(data.saved ? 'Saved to wishlist' : 'Removed from saved');
                } else if (data.message === 'Not logged in.') {
                    window.location.href = 'login.php?redirect=event-detail.php?event_id=' + id;
                }
            });
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).catch(() => { });
            showToast('Link copied!');
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.opacity = '1';
            t.style.transform = 'translateX(-50%) translateY(0)';
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transform = 'translateX(-50%) translateY(20px)';
            }, 2500);
        }
    </script>
</body>

</html>