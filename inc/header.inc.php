<header class="pulse-hero">

    <!-- Left: Image / Carousel Panel -->
    <div class="hero-image-panel">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5500">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80"
                        class="hero-img" alt="Concert">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1598387993441-a364f854cfba?w=1920&q=80"
                        class="hero-img" alt="Festival">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1501612780327-45045538702b?w=1920&q=80"
                        class="hero-img" alt="Live Show">
                </div>
            </div>
        </div>

        <!-- Very light overlay â€” lets the image breathe -->
        <div class="hero-img-overlay"></div>

        <!-- Slide indicators â€” horizontal at bottom of image -->
        <div class="hero-dots">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="hero-dot active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" class="hero-dot" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" class="hero-dot" aria-label="Slide 3"></button>
        </div>
    </div>

    <!-- Right: Content Panel (light background, dark text) -->
    <div class="hero-content-panel">
        <div class="hero-content-inner">

            <!-- Live badge -->
            <span class="hero-live-badge">
                <span class="live-dot"></span>
                Now On Sale â€” Singapore 2026
            </span>

            <!-- Main heading -->
            <h1 class="hero-title">Live Without<br><em>Limits</em></h1>

            <!-- Subtitle -->
            <p class="hero-subtitle">
                Discover concerts, festivals, theatre, and live events in Singapore. Easy booking, instant confirmation.
            </p>

            <!-- Inline search bar -->
            <div class="hero-search" role="search">
                <svg width="16" height="16" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                    <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 12L15.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input type="text" placeholder="Artist, event, or venueâ€¦" aria-label="Search events">
                <button type="button">Search</button>
            </div>

            <!-- CTA buttons -->
            <div class="hero-cta-group">
                <a href="#events" class="btn btn-accent">
                    Explore Events
                    <svg width="13" height="13" viewBox="0 0 14 14" fill="none" class="ms-1" aria-hidden="true">
                        <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </a>
                <a href="events.php" class="hero-btn-secondary">View Calendar</a>
            </div>

            <!-- Stats strip â€” grounds the panel -->
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <span class="hero-stat-number">500+</span>
                    <span class="hero-stat-label">Events Listed</span>
                </div>
                <div class="hero-stat-item">
                    <span class="hero-stat-number">30+</span>
                    <span class="hero-stat-label">Venues</span>
                </div>
                <div class="hero-stat-item">
                    <span class="hero-stat-number">50K+</span>
                    <span class="hero-stat-label">Tickets Sold</span>
                </div>
            </div>

        </div>
    </div>

</header>
