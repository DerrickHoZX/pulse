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
            <section id="events" class="container-fluid px-5 py-5 fade-up">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <span class="section-label">Don't Miss</span>
                        <h2 class="section-title">Featured <em>Events</em></h2>
                    </div>
                    <a href="events.php" class="view-all-link">View All</a>
                </div>

                <div class="events-grid">
                    <div class="event-card card-large">
                        <img class="event-card-img" src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=900&q=80" alt="BLACKPINK">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">K-Pop</span>
                            <div class="event-card-title">BLACKPINK<br>BORN PINK World Tour</div>
                            <div class="event-card-meta">
                                <span>29&ndash;30 Nov 2025</span><span class="meta-dot"></span><span>National Stadium</span>
                            </div>
                            <a href="event-detail.php?event_id=1" class="event-cta-btn">Get Tickets <svg width="11" height="11" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="hot-badge">Selling Fast</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img" src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&q=80" alt="Lady Gaga">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Pop</span>
                            <div class="event-card-title">Lady Gaga: Chromatica Ball</div>
                            <div class="event-card-meta"><span>22 May 2025</span><span class="meta-dot"></span><span>National Stadium</span></div>
                            <a href="event-detail.php?event_id=3" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$188</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img" src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600&q=80" alt="TWICE">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">K-Pop</span>
                            <div class="event-card-title">TWICE: Ready To Be World Tour</div>
                            <div class="event-card-meta"><span>11&ndash;12 Oct 2025</span><span class="meta-dot"></span><span>Singapore Indoor Stadium</span></div>
                            <a href="event-detail.php?event_id=4" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="hot-badge">Hot</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img" src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&q=80" alt="My Chemical Romance">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Rock</span>
                            <div class="event-card-title">My Chemical Romance</div>
                            <div class="event-card-meta"><span>29 Apr 2026</span><span class="meta-dot"></span><span>Singapore Indoor Stadium</span></div>
                            <a href="event-detail.php?event_id=2" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$118</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img" src="https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?w=600&q=80" alt="Cirque du Soleil">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Theatre</span>
                            <div class="event-card-title">Cirque du Soleil: KOOZA</div>
                            <div class="event-card-meta"><span>Ongoing 2026</span><span class="meta-dot"></span><span>Bayfront Event Space</span></div>
                            <a href="event-detail.php?event_id=5" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$108</div>
                    </div>
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
                    <a href="event-detail.php?event_id=16" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month">Mar</span><span class="date-day">31</span></div>
                        <div>
                            <div class="upcoming-event-title">Laufey: A Matter of Time Tour</div>
                            <div class="upcoming-event-details"><span>Sands Expo</span><span class="meta-dot"></span><span>8:00 PM</span><span class="tag-chip tag-hot">Hot</span></div>
                        </div>
                        <div class="upcoming-right"><div class="upcoming-price">S$98 <small>From</small></div><span class="tag-chip">Pop / Jazz</span></div>
                    </a>
                    <a href="event-detail.php?event_id=3" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month">May</span><span class="date-day">22</span></div>
                        <div>
                            <div class="upcoming-event-title">Lady Gaga: Chromatica Ball Singapore</div>
                            <div class="upcoming-event-details"><span>National Stadium</span><span class="meta-dot"></span><span>8:00 PM</span><span class="tag-chip tag-hot">Hot</span></div>
                        </div>
                        <div class="upcoming-right"><div class="upcoming-price">S$188 <small>From</small></div><span class="tag-chip">Pop</span></div>
                    </a>
                    <a href="event-detail.php?event_id=13" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month">Sep</span><span class="date-day">23</span></div>
                        <div>
                            <div class="upcoming-event-title">Sting: My Songs Tour Singapore</div>
                            <div class="upcoming-event-details"><span>Singapore Indoor Stadium</span><span class="meta-dot"></span><span>8:00 PM</span></div>
                        </div>
                        <div class="upcoming-right"><div class="upcoming-price">S$128 <small>From</small></div><span class="tag-chip">Rock</span></div>
                    </a>
                    <a href="event-detail.php?event_id=4" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month">Oct</span><span class="date-day">11</span></div>
                        <div>
                            <div class="upcoming-event-title">TWICE 5TH WORLD TOUR: Ready To Be</div>
                            <div class="upcoming-event-details"><span>Singapore Indoor Stadium</span><span class="meta-dot"></span><span>7:00 PM</span><span class="tag-chip tag-hot">Selling Fast</span></div>
                        </div>
                        <div class="upcoming-right"><div class="upcoming-price">S$148 <small>From</small></div><span class="tag-chip">K-Pop</span></div>
                    </a>
                    <a href="event-detail.php?event_id=1" class="upcoming-item">
                        <div class="upcoming-date-box"><span class="date-month">Nov</span><span class="date-day">29</span></div>
                        <div>
                            <div class="upcoming-event-title">BLACKPINK: BORN PINK World Tour Singapore</div>
                            <div class="upcoming-event-details"><span>National Stadium</span><span class="meta-dot"></span><span>7:30 PM</span><span class="tag-chip tag-hot">Hot</span></div>
                        </div>
                        <div class="upcoming-right"><div class="upcoming-price">S$148 <small>From</small></div><span class="tag-chip">K-Pop</span></div>
                    </a>
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
                    <a href="index.php#venues" class="view-all-link">All Venues</a>
                </div>
                <div class="venues-grid">
                    <div class="venue-card">
                        <img class="venue-card-img"
                            src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800&q=80"
                            alt="Singapore Indoor Stadium">
                        <div class="venue-overlay">
                            <div class="venue-name">Singapore Indoor Stadium</div>
                            <div class="venue-count">12 upcoming shows</div>
                        </div>
                    </div>
                    <div class="venue-card">
                        <img class="venue-card-img"
                            src="https://images.unsplash.com/photo-1549451371-64aa98a6f660?w=800&q=80"
                            alt="Esplanade">
                        <div class="venue-overlay">
                            <div class="venue-name">Esplanade &mdash; Theatres on the Bay</div>
                            <div class="venue-count">8 upcoming shows</div>
                        </div>
                    </div>
                    <div class="venue-card">
                        <img class="venue-card-img"
                            src="https://images.unsplash.com/photo-1563841930606-67e2bce48b78?w=800&q=80"
                            alt="The Star Theatre">
                        <div class="venue-overlay">
                            <div class="venue-name">The Star Theatre</div>
                            <div class="venue-count">5 upcoming shows</div>
                        </div>
                    </div>
                </div>
            </section>

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
