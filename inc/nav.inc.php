<nav class="navbar navbar-expand-lg pulse-navbar" id="pulseNav">
    <div class="container-fluid px-4">
        <!-- Brand Logo -->
        <a class="navbar-brand pulse-brand" href="index.php">
            PUL<span class="brand-accent">S</span>E
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarMain" aria-controls="navbarMain"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="events.php">All Events</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
                    <ul class="dropdown-menu dropdown-menu-dark pulse-dropdown">
                        <li><a class="dropdown-item" href="#">🎸 Concerts</a></li>
                        <li><a class="dropdown-item" href="#">🎪 Festivals</a></li>
                        <li><a class="dropdown-item" href="#">🎭 Theatre & Arts</a></li>
                        <li><a class="dropdown-item" href="#">⚽ Sports</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">🔥 On Sale Now</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Venues</a>
                </li>
            </ul>

            <!-- Right-side icons -->
            <div class="d-flex align-items-center gap-3">
                <a href="login.php" class="btn btn-outline-accent btn-sm nav-auth-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    Sign In / Register
                </a>
            </div>
        </div>
    </div>
</nav>
