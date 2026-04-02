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

// Venues for carousel
$venues_sql = "SELECT venue_id, name, google_maps_link FROM venues WHERE google_maps_link IS NOT NULL AND google_maps_link != '' ORDER BY venue_id";
$venues_result = $conn->query($venues_sql);
$venues = $venues_result->fetch_all(MYSQLI_ASSOC);

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
                        $cardClass = $i === 0 ? 'event-card card-large' : 'event-card';
                        $imgSrc = !empty($e['img_url']) ? resolveImageSrc($e['img_url']) : '';
                        $dateStr = date('d M Y', strtotime($e['event_date']));
                        $arrowSvg = '<svg width="' . ($i === 0 ? '11' : '10') . '" height="' . ($i === 0 ? '11' : '10') . '" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>';
                    ?>
                    <div class="<?= $cardClass ?>">
                        <?php if ($imgSrc): ?>
                        <img class="event-card-img" src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($e['title']) ?>">
                        <?php endif; ?>
                        <div class="event-card-overlay">
                            <span class="event-genre-tag"><?= htmlspecialchars($e['category']) ?></span>
                            <div class="event-card-title"><?= htmlspecialchars($e['title']) ?></div>
                            <div class="event-card-meta">
                                <span><?= $dateStr ?></span><span class="meta-dot"></span><span><?= htmlspecialchars($e['venue_name']) ?></span>
                            </div>
                            <a href="event-detail.php?event_id=<?= $e['event_id'] ?>" class="event-cta-btn">Get Tickets <?= $arrowSvg ?></a>
                        </div>
                        <?php if ($e['min_price']): ?>
                        <div class="price-badge">From S$<?= number_format($e['min_price'], 0) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($featured)): ?>
                    <div style="color:var(--pulse-muted);padding:40px 0;">No upcoming events at this time.</div>
                    <?php endif; ?>
                </div>
            </section>

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
                        $month = date('M', strtotime($e['event_date']));
                        $day   = date('j', strtotime($e['event_date']));
                        $time  = date('g:i A', strtotime($e['event_time']));
                    ?>
                    <a href="event-detail.php?event_id=<?= $e['event_id'] ?>" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month"><?= $month ?></span><span class="date-day"><?= $day ?></span></div>
                        <div>
                            <div class="upcoming-event-title"><?= htmlspecialchars($e['title']) ?></div>
                            <div class="upcoming-event-details">
                                <span><?= htmlspecialchars($e['venue_name']) ?></span>
                                <span class="meta-dot"></span>
                                <span><?= $time ?></span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price"><?= $e['min_price'] ? 'S$' . number_format($e['min_price'], 0) . ' <small>From</small>' : '' ?></div>
                            <span class="tag-chip"><?= htmlspecialchars($e['category']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($upcoming)): ?>
                    <div style="color:var(--pulse-muted);padding:20px 0;">No upcoming shows at this time.</div>
                    <?php endif; ?>
                </div>
            </section>

            <div class="container-fluid px-5 pb-5 fade-up">
                <div class="promo-banner">
                    <div>
                        <span class="promo-label">Limited Time Offer</span>
                        <div class="promo-title">Early Bird<br><em>20% Off</em></div>
                        <p class="promo-body">Use code <strong>PULSE20</strong> at checkout for selected March &amp; April shows.</p>
                    </div>
                    <a href="events.php" class="btn-dark-solid">
                        Shop Now
                        <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                            <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <section id="venues" class="container-fluid px-5 pb-5 fade-up">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <span class="section-label">Where It Happens</span>
                        <h2 class="section-title">Iconic <em>Venues</em></h2>
                    </div>
                    <div class="venues-carousel-controls">
                        <button class="venues-carousel-btn" id="venuesPrev" aria-label="Previous">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8L10 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button class="venues-carousel-btn" id="venuesNext" aria-label="Next">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3L11 8L6 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </div>

                <?php
                    // fallback images per venue — keyed by venue_id, then a generic default
                    $venue_images = [
                        1 => "https://images.unsplash.com/photo-1564585222527-c2777a5bc6cb?w=800&q=80",
                        2 => "https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=80",
                        3 => "https://images.unsplash.com/photo-1563841930606-67e2bce48b78?w=800&q=80",
                        4 => "https://images.unsplash.com/photo-1549451371-64aa98a6f660?w=800&q=80",
                        5 => "https://images.unsplash.com/photo-1534430480872-3498386e7856?w=800&q=80",
                        6 => "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=800&q=80",
                        7 => "https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=800&q=80",
                        8 => "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80",
                        9 => "https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&q=80",
                        10 => "https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&q=80",
                    ];
                    $default_img = "https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800&q=80";
                ?>

                <div class="venues-carousel-wrapper">
                    <div class="venues-carousel-track" id="venuesTrack">
                        <?php foreach ($venues as $v): ?>
                            <?php
                                $img = $venue_images[$v['venue_id']] ?? $default_img;
                                $maps_link = htmlspecialchars($v['google_maps_link']);
                                $name = htmlspecialchars($v['name']);
                            ?>
                            <a class="venue-card" href="<?= $maps_link ?>" target="_blank" rel="noopener noreferrer">
                                <img class="venue-card-img"
                                    src="<?= $img ?>"
                                    alt="<?= $name ?>">
                                <div class="venue-overlay">
                                    <div class="venue-name"><?= $name ?></div>
                                    <div class="venue-count">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-right:4px;opacity:0.6;"><path d="M6 1C4.067 1 2.5 2.567 2.5 4.5C2.5 7.25 6 11 6 11C6 11 9.5 7.25 9.5 4.5C9.5 2.567 7.933 1 6 1Z" stroke="currentColor" stroke-width="1.2"/></svg>
                                        View on Maps
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="venues-carousel-dots" id="venuesDots"></div>
            </section>
        </main>

        <!-- Venue carousel fix -->
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const track   = document.getElementById("venuesTrack");
            const dotsBox = document.getElementById("venuesDots");
            const btnPrev = document.getElementById("venuesPrev");
            const btnNext = document.getElementById("venuesNext");

            if (!track || !btnPrev || !btnNext) return;

            const cards = Array.from(track.children);
            let current = 0;

            function cardsPerView() {
                if (window.innerWidth < 576) return 1;
                if (window.innerWidth < 992) return 2;
                return 3;
            }

            function totalSlides() {
                return Math.ceil(cards.length / cardsPerView());
            }

            function goTo(index) {
                current = Math.max(0, Math.min(index, totalSlides() - 1));
                track.style.transform = `translateX(-${current * track.parentElement.offsetWidth}px)`;
                dotsBox.querySelectorAll(".venues-carousel-dot").forEach((d, i) => {
                    d.classList.toggle("active", i === current);
                });
            }

            function buildDots() {
                dotsBox.innerHTML = "";
                for (let i = 0; i < totalSlides(); i++) {
                    const dot = document.createElement("button");
                    dot.className = "venues-carousel-dot" + (i === current ? " active" : "");
                    dot.setAttribute("aria-label", "Go to slide " + (i + 1));
                    dot.addEventListener("click", () => goTo(i));
                    dotsBox.appendChild(dot);
                }
            }

            btnPrev.addEventListener("click", () => goTo(current - 1));
            btnNext.addEventListener("click", () => goTo(current + 1));

            window.addEventListener("resize", () => {
                current = 0;
                track.style.transform = "translateX(0)";
                buildDots();
            });

            buildDots();
        });
        </script>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
