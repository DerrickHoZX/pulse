<header class="pulse-hero">
    <!-- Bootstrap Carousel -->
    <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="5500">
        <div class="carousel-inner h-100">
            <div class="carousel-item active h-100">
                <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80"
                    class="d-block w-100 hero-img" alt="Concert">
                <div class="hero-img-overlay"></div>
            </div>
            <div class="carousel-item h-100">
                <img src="https://images.unsplash.com/photo-1598387993441-a364f854cfba?w=1920&q=80"
                    class="d-block w-100 hero-img" alt="Festival">
                <div class="hero-img-overlay"></div>
            </div>
            <div class="carousel-item h-100">
                <img src="https://images.unsplash.com/photo-1501612780327-45045538702b?w=1920&q=80"
                    class="d-block w-100 hero-img" alt="Live Show">
                <div class="hero-img-overlay"></div>
            </div>
        </div>
    </div>

    <!-- Hero Content -->
    <div class="hero-content container-fluid px-5">
        <span class="hero-live-badge">
            <span class="live-dot"></span> Now On Sale — Singapore 2026
        </span>
        <h1 class="hero-title">Live Without<br><em>Limits</em></h1>
        <p class="hero-subtitle">
            Discover the most electrifying concerts, festivals, and live events happening in Singapore.
        </p>
        <div class="d-flex gap-3 flex-wrap">
            <a href="#events" class="btn btn-accent">
                Explore Events
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="ms-2">
                    <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </a>
            <a href="events.php" class="btn btn-outline-light">View Calendar</a>
        </div>
    </div>

    <!-- Carousel Indicators as vertical dots -->
    <div class="hero-dots">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="hero-dot active" aria-current="true"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" class="hero-dot"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" class="hero-dot"></button>
    </div>

    <!-- Scroll hint -->
    <div class="hero-scroll-hint">
        <div class="scroll-line"></div>
        <span>Scroll</span>
    </div>
</header>
