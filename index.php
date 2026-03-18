<!DOCTYPE html>
<html lang="en">
    <head>
        <title>PULSE — Live Events Singapore</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>

        <?php include "inc/nav.inc.php" ?>

        <?php include "inc/header.inc.php" ?>

        <main>

            <!-- Category Pills -->
            <div class="container-fluid px-5 pt-5 pb-2">
                <div class="category-pills-track">
                    <button class="cat-pill active">🔥 All Shows</button>
                    <button class="cat-pill">🎸 Rock &amp; Metal</button>
                    <button class="cat-pill">🎶 K-Pop &amp; J-Pop</button>
                    <button class="cat-pill">🎤 Hip-Hop &amp; R&amp;B</button>
                    <button class="cat-pill">🎹 Classical</button>
                    <button class="cat-pill">🎧 Electronic / EDM</button>
                    <button class="cat-pill">🎷 Jazz &amp; Blues</button>
                    <button class="cat-pill">🎭 Theatre &amp; Arts</button>
                    <button class="cat-pill">⚽ Sports</button>
                    <button class="cat-pill">🎪 Festivals</button>
                </div>
            </div>

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
                    <!-- Large card -->
                    <div class="event-card card-large">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=900&q=80"
                            alt="Guns N' Roses">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Rock</span>
                            <div class="event-card-title">Guns N' Roses<br>World Tour 2026</div>
                            <div class="event-card-meta">
                                <span>18 Apr 2026</span>
                                <span class="meta-dot"></span>
                                <span>Singapore Indoor Stadium</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">
                                Get Tickets
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </a>
                        </div>
                        <div class="price-badge">From S$148</div>
                    </div>

                    <!-- Small cards -->
                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=600&q=80"
                            alt="Laufey">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Pop / Jazz</span>
                            <div class="event-card-title">Laufey: A Matter of Time</div>
                            <div class="event-card-meta">
                                <span>3 May 2026</span>
                                <span class="meta-dot"></span>
                                <span>Sands Expo</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="hot-badge">🔥 Selling Fast</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=600&q=80"
                            alt="Kraftwerk">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Electronic</span>
                            <div class="event-card-title">Kraftwerk Multimedia Tour</div>
                            <div class="event-card-meta">
                                <span>8 May 2026</span>
                                <span class="meta-dot"></span>
                                <span>The Star Theatre</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$78</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&q=80"
                            alt="Avantgardey">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">K-Pop</span>
                            <div class="event-card-title">AVANTGARDEY 2026: Let's Groove!</div>
                            <div class="event-card-meta">
                                <span>11–12 Mar 2026</span>
                                <span class="meta-dot"></span>
                                <span>Star Performing Arts</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$128</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?w=600&q=80"
                            alt="Vir Das">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Comedy</span>
                            <div class="event-card-title">Vir Das: Hey Stranger</div>
                            <div class="event-card-meta">
                                <span>21 May 2026</span>
                                <span class="meta-dot"></span>
                                <span>Esplanade Theatre</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$68</div>
                    </div>
                </div>
            </section>

            <!-- Upcoming Shows — white band for contrast against warm body -->
            <div class="section-band">
            <section class="container-fluid px-5 py-5 fade-up">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <span class="section-label">What's Next</span>
                        <h2 class="section-title">Upcoming <em>Shows</em></h2>
                    </div>
                    <a href="events.php" class="view-all-link">Full Calendar</a>
                </div>

                <div class="upcoming-list">

                    <a href="#" class="upcoming-item">
                        <div class="upcoming-date-box">
                            <span class="date-month">Mar</span>
                            <span class="date-day">14</span>
                        </div>
                        <div>
                            <div class="upcoming-event-title">BUS: The 1st Asia Fancon Tour — The First Light</div>
                            <div class="upcoming-event-details">
                                <span>📍 Singapore Indoor Stadium</span>
                                <span class="meta-dot"></span>
                                <span>7:30 PM</span>
                                <span class="tag-chip tag-hot">🔥 Hot</span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price">S$188 <small>From</small></div>
                            <span class="tag-chip">K-Pop</span>
                        </div>
                    </a>

                    <a href="#" class="upcoming-item">
                        <div class="upcoming-date-box">
                            <span class="date-month">Apr</span>
                            <span class="date-day">02</span>
                        </div>
                        <div>
                            <div class="upcoming-event-title">Anson Seabra: The I Must Be Dreaming Tour</div>
                            <div class="upcoming-event-details">
                                <span>📍 Esplanade Concert Hall</span>
                                <span class="meta-dot"></span>
                                <span>8:00 PM</span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price">S$95 <small>From</small></div>
                            <span class="tag-chip">Pop</span>
                        </div>
                    </a>

                    <a href="#" class="upcoming-item">
                        <div class="upcoming-date-box">
                            <span class="date-month">Apr</span>
                            <span class="date-day">18</span>
                        </div>
                        <div>
                            <div class="upcoming-event-title">Guns N' Roses — World Tour 2026</div>
                            <div class="upcoming-event-details">
                                <span>📍 Singapore Indoor Stadium</span>
                                <span class="meta-dot"></span>
                                <span>8:00 PM</span>
                                <span class="tag-chip tag-hot">🔥 Hot</span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price">S$148 <small>From</small></div>
                            <span class="tag-chip">Rock</span>
                        </div>
                    </a>

                    <a href="#" class="upcoming-item">
                        <div class="upcoming-date-box">
                            <span class="date-month">May</span>
                            <span class="date-day">08</span>
                        </div>
                        <div>
                            <div class="upcoming-event-title">Kraftwerk: Multimedia Tour</div>
                            <div class="upcoming-event-details">
                                <span>📍 The Star Theatre</span>
                                <span class="meta-dot"></span>
                                <span>8:30 PM</span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price">S$78 <small>From</small></div>
                            <span class="tag-chip">Electronic</span>
                        </div>
                    </a>

                    <a href="#" class="upcoming-item">
                        <div class="upcoming-date-box">
                            <span class="date-month">Jun</span>
                            <span class="date-day">05</span>
                        </div>
                        <div>
                            <div class="upcoming-event-title">Laufey: A Matter of Time Tour</div>
                            <div class="upcoming-event-details">
                                <span>📍 Sands Expo &amp; Convention Centre</span>
                                <span class="meta-dot"></span>
                                <span>8:00 PM</span>
                                <span class="tag-chip tag-hot">🔥 Selling Fast</span>
                            </div>
                        </div>
                        <div class="upcoming-right">
                            <div class="upcoming-price">S$120 <small>From</small></div>
                            <span class="tag-chip">Pop / Jazz</span>
                        </div>
                    </a>

                </div>
            </section>
            </div><!-- /.section-band -->

            <!-- Promo Banner -->
            <div class="container-fluid px-5 pb-5 fade-up">
                <div class="promo-banner">
                    <div>
                        <span class="promo-label">Limited Time Offer</span>
                        <div class="promo-title">Early Bird<br><em>20% Off</em></div>
                        <p class="promo-body">Use code <strong>PULSE20</strong> at checkout for selected March &amp; April shows.</p>
                    </div>
                    <a href="#" class="btn-dark-solid">
                        Shop Now
                        <svg width="13" height="13" viewBox="0 0 14 14" fill="none">
                            <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Venues -->
            <section class="container-fluid px-5 pb-5 fade-up">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <span class="section-label">Where It Happens</span>
                        <h2 class="section-title">Iconic <em>Venues</em></h2>
                    </div>
                    <a href="#" class="view-all-link">All Venues</a>
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
                            <div class="venue-name">Esplanade — Theatres on the Bay</div>
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

            <!-- Newsletter -->
            <div class="container-fluid px-5 pb-5 fade-up">
                <div class="newsletter-box">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-5">
                            <span class="section-label">Stay in the Loop</span>
                            <h2 class="section-title">Never Miss<br><em>a Beat</em></h2>
                            <p class="mt-3" style="color: var(--pulse-muted); font-weight:300; font-size:0.9rem; line-height:1.7;">
                                Get first access to presales, exclusive offers, and event announcements — straight to your inbox.
                            </p>
                        </div>
                        <div class="col-lg-7">
                            <div class="newsletter-form-row">
                                <input type="email" placeholder="Your email address">
                                <button type="button">Subscribe</button>
                            </div>
                            <p class="newsletter-note">By subscribing you agree to our Privacy Policy. No spam — unsubscribe any time.</p>
                        </div>
                    </div>
                </div>
            </div>

        </main>

    </body>
    <?php include "inc/footer.inc.php" ?>
</html>
