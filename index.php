<?php
session_start();
require_once 'inc/db.inc.php';
require_once 'inc/image.inc.php';

$conn = getDBConnection();

// Featured events — next 5 upcoming active events
$featured = $conn->query("
    SELECT e.event_id, e.title, e.category, e.event_date, e.event_time,
           v.name AS venue_name,
           MIN(ss.price) AS min_price,
           (SELECT ei.image_path FROM event_images ei
            WHERE ei.event_id = e.event_id AND ei.image_type = 'poster'
            ORDER BY ei.sort_order ASC LIMIT 1) AS img_url
    FROM events e
    JOIN venues v ON e.venue_id = v.venue_id
    LEFT JOIN seat_sections ss ON ss.event_id = e.event_id
    WHERE e.is_active = 1 AND e.event_date >= CURDATE()
    GROUP BY e.event_id
    ORDER BY e.event_date ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Upcoming shows — next 5 after featured
$upcoming = $conn->query("
    SELECT e.event_id, e.title, e.category, e.event_date, e.event_time,
           v.name AS venue_name,
           MIN(ss.price) AS min_price
    FROM events e
    JOIN venues v ON e.venue_id = v.venue_id
    LEFT JOIN seat_sections ss ON ss.event_id = e.event_id
    WHERE e.is_active = 1 AND e.event_date >= CURDATE()
    GROUP BY e.event_id
    ORDER BY e.event_date ASC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Venue show counts
$venueCounts = [];
$vcResult = $conn->query("
    SELECT v.name, COUNT(e.event_id) AS show_count
    FROM venues v
    JOIN events e ON e.venue_id = v.venue_id
    WHERE e.is_active = 1 AND e.event_date >= CURDATE()
    GROUP BY v.venue_id
    ORDER BY show_count DESC
    LIMIT 3
");
while ($row = $vcResult->fetch_assoc()) {
    $venueCounts[$row['name']] = $row['show_count'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE &mdash; Live Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>
    <?php include "inc/header.inc.php" ?>

    <main>
        <!-- Featured Events -->
        <section id="events" class="container-fluid px-5 py-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">Don't Miss</span>
                    <h2 class="section-title">Featured <em>Events</em></h2>
                </div>
                <a href="events.php" class="view-all-link">View All</a>
            </div>

            <div class="events-grid">
                <?php foreach ($featured as $i => $e):
                    $imgUrl   = resolveImageSrc($e['img_url'] ?? '');
                    $fallback = 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=900&q=80';
                    $img      = $imgUrl ?: $fallback;
                    $dateStr  = date('d M Y', strtotime($e['event_date']));
                    $price    = $e['min_price'] ? 'From S$' . number_format($e['min_price'], 0) : 'Free';
                ?>
                <div class="event-card <?= $i === 0 ? 'card-large' : '' ?>">
                    <img class="event-card-img" src="<?= $img ?>" alt="<?= htmlspecialchars($e['title']) ?>">
                    <div class="event-card-overlay">
                        <span class="event-genre-tag"><?= htmlspecialchars($e['category'] ?? '') ?></span>
                        <div class="event-card-title"><?= htmlspecialchars($e['title']) ?></div>
                        <div class="event-card-meta">
                            <span><?= $dateStr ?></span>
                            <span class="meta-dot"></span>
                            <span><?= htmlspecialchars($e['venue_name']) ?></span>
                        </div>
                        <a href="event-detail.php?event_id=<?= $e['event_id'] ?>" class="event-cta-btn">
                            Get Tickets
                            <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                <path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </a>
                    </div>
                    <div class="price-badge"><?= $price ?></div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($featured)): ?>
                    <p style="color:var(--pulse-muted);">No upcoming events at the moment. Check back soon!</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Upcoming Shows -->
        <section class="container-fluid px-5 py-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">What's Next</span>
                    <h2 class="section-title">Upcoming <em>Shows</em></h2>
                </div>
                <a href="events.php" class="view-all-link">Full Calendar</a>
            </div>

            <div class="upcoming-list">
                <?php foreach ($upcoming as $e):
                    $month   = date('M', strtotime($e['event_date']));
                    $day     = date('j', strtotime($e['event_date']));
                    $timeStr = $e['event_time'] ? date('g:i A', strtotime($e['event_time'])) : 'TBA';
                    $price   = $e['min_price'] ? 'S$' . number_format($e['min_price'], 0) : 'Free';
                ?>
                <a href="event-detail.php?event_id=<?= $e['event_id'] ?>" class="upcoming-item">
                    <div class="upcoming-date-box">
                        <span class="date-month"><?= $month ?></span>
                        <span class="date-day"><?= $day ?></span>
                    </div>
                    <div>
                        <div class="upcoming-event-title"><?= htmlspecialchars($e['title']) ?></div>
                        <div class="upcoming-event-details">
                            <span><?= htmlspecialchars($e['venue_name']) ?></span>
                            <span class="meta-dot"></span>
                            <span><?= $timeStr ?></span>
                        </div>
                    </div>
                    <div class="upcoming-right">
                        <div class="upcoming-price"><?= $price ?> <small>From</small></div>
                        <span class="tag-chip"><?= htmlspecialchars($e['category'] ?? '') ?></span>
                    </div>
                </a>
                <?php endforeach; ?>

                <?php if (empty($upcoming)): ?>
                    <p style="color:var(--pulse-muted);padding:20px 0;">No upcoming shows at the moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Promo Banner -->
        <div class="container-fluid px-5 pb-5 fade-up">
            <div class="promo-banner">
                <div>
                    <span class="promo-label">Limited Time Offer</span>
                    <div class="promo-title">Early Bird<br><em>20% Off</em></div>
                    <p class="promo-body">Use code <strong>PULSE20</strong> at checkout for selected shows.</p>
                </div>
                <a href="events.php" class="btn-dark-solid">
                    Shop Now
                    <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Venues -->
        <section id="venues" class="container-fluid px-5 pb-5 fade-up">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-label">Where It Happens</span>
                    <h2 class="section-title">Iconic <em>Venues</em></h2>
                </div>
            </div>
            <div class="venues-grid">
                <div class="venue-card">
                    <img class="venue-card-img"
                        src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=80"
                        alt="Singapore Indoor Stadium">
                    <div class="venue-overlay">
                        <div class="venue-name">Singapore Indoor Stadium</div>
                        <div class="venue-count"><?= $venueCounts['Singapore Indoor Stadium'] ?? 0 ?> upcoming shows</div>
                    </div>
                </div>
                <div class="venue-card">
                    <img class="venue-card-img"
                        src="https://images.unsplash.com/photo-1549451371-64aa98a6f660?w=800&q=80"
                        alt="Esplanade">
                    <div class="venue-overlay">
                        <div class="venue-name">Esplanade &mdash; Theatres on the Bay</div>
                        <div class="venue-count"><?= $venueCounts['Esplanade - Theatres on the Bay'] ?? 0 ?> upcoming shows</div>
                    </div>
                </div>
                <div class="venue-card">
                    <img class="venue-card-img"
                        src="https://images.unsplash.com/photo-1563841930606-67e2bce48b78?w=800&q=80"
                        alt="The Star Theatre">
                    <div class="venue-overlay">
                        <div class="venue-name">The Star Theatre</div>
                        <div class="venue-count"><?= $venueCounts['The Star Theatre'] ?? 0 ?> upcoming shows</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter -->
        <div class="container-fluid px-5 pb-5 fade-up">
            <div class="newsletter-box">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5">
                        <span class="section-label">Stay in the Loop</span>
                        <h2 class="section-title">Never Miss<br><em>a Beat</em></h2>
                        <p class="mt-3" style="color: var(--pulse-muted); font-weight:300; font-size:0.9rem; line-height:1.7;">
                            Get first access to presales, exclusive offers, and event announcements &mdash; straight to your inbox.
                        </p>
                    </div>
                    <div class="col-lg-7">
                        <div class="newsletter-form-row">
                            <input type="email" placeholder="Your email address">
                            <button type="button">Subscribe</button>
                        </div>
                        <p class="newsletter-note">By subscribing you agree to our Privacy Policy. No spam &mdash; unsubscribe any time.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>