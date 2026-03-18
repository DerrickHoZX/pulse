<!DOCTYPE html>
<html lang="en">
    <head>
        <title>All Events — PULSE Events Singapore</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>

        <?php include "inc/nav.inc.php" ?>

        <!-- Page Hero -->
        <div class="page-hero">
            <div class="container-fluid px-5">
                <span class="section-label">Browse</span>
                <h1 class="page-hero-title">All <em>Events</em></h1>
                <p class="page-hero-sub">Concerts, festivals, theatre, sports and more across Singapore.</p>
            </div>
        </div>

        <main>
            <!-- Filter Bar -->
            <div class="container-fluid px-5 pt-5 pb-2">
                <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                    <div class="hero-search" style="max-width:420px;flex:1;">
                        <svg width="16" height="16" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                            <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 12L15.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <input type="text" placeholder="Artist, event, or venue…" aria-label="Search events">
                        <button type="button">Search</button>
                    </div>
                </div>
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

            <!-- Events Grid -->
            <section class="container-fluid px-5 py-5 fade-up">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <span class="section-label">Showing All</span>
                        <h2 class="section-title">Upcoming <em>Events</em></h2>
                    </div>
                    <span style="font-size:0.78rem;color:var(--pulse-muted);">9 events found</span>
                </div>

                <div class="events-grid">
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

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600&q=80"
                            alt="BUS Fancon">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">K-Pop</span>
                            <div class="event-card-title">BUS: The 1st Asia Fancon Tour</div>
                            <div class="event-card-meta">
                                <span>14 Mar 2026</span>
                                <span class="meta-dot"></span>
                                <span>Singapore Indoor Stadium</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="hot-badge">🔥 Hot</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1598387993441-a364f854cfba?w=600&q=80"
                            alt="Anson Seabra">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Pop</span>
                            <div class="event-card-title">Anson Seabra: I Must Be Dreaming</div>
                            <div class="event-card-meta">
                                <span>2 Apr 2026</span>
                                <span class="meta-dot"></span>
                                <span>Esplanade Concert Hall</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$95</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1501612780327-45045538702b?w=600&q=80"
                            alt="Tame Impala">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Alternative</span>
                            <div class="event-card-title">Tame Impala: The Slow Rush Tour</div>
                            <div class="event-card-meta">
                                <span>14 Jun 2026</span>
                                <span class="meta-dot"></span>
                                <span>The Star Theatre</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$118</div>
                    </div>

                    <div class="event-card">
                        <img class="event-card-img"
                            src="https://images.unsplash.com/photo-1549451371-64aa98a6f660?w=600&q=80"
                            alt="Singapore Jazz Festival">
                        <div class="event-card-overlay">
                            <span class="event-genre-tag">Jazz &amp; Blues</span>
                            <div class="event-card-title">Singapore International Jazz Festival</div>
                            <div class="event-card-meta">
                                <span>20–22 Mar 2026</span>
                                <span class="meta-dot"></span>
                                <span>Marina Bay Sands</span>
                            </div>
                            <a href="booking.php" class="event-cta-btn">Get Tickets <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6H10M10 6L7 3M10 6L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></a>
                        </div>
                        <div class="price-badge">From S$58</div>
                    </div>
                </div>
            </section>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
