<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php
$currentDir = dirname($_SERVER['PHP_SELF']);
$isAdminPage = (strpos($currentDir, '/admin') !== false);

$homeLink = $isAdminPage ? '../index.php' : 'index.php';
$eventsLink = $isAdminPage ? '../events.php' : 'events.php';
$dashboardLink = $isAdminPage ? '../dashboard.php' : 'dashboard.php';
$logoutLink = $isAdminPage ? '../actions/logout.php' : 'actions/logout.php';
$loginLink = $isAdminPage ? '../login.php' : 'login.php';
$adminLink = $isAdminPage ? 'admin.php' : 'admin/admin.php';
$venuesLink = $homeLink . '#venues';
?>

<nav class="navbar navbar-expand-lg pulse-navbar" id="pulseNav">
    <div class="container-fluid px-4">
        <a class="navbar-brand pulse-brand" href="<?= $homeLink ?>">
            PUL<span class="brand-accent">S</span>E
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarMain" aria-controls="navbarMain"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?= $homeLink ?>">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $eventsLink ?>">All Events</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">Categories</a>
                    <ul class="dropdown-menu dropdown-menu-dark pulse-dropdown">
                        <li><a class="dropdown-item" href="<?= $eventsLink ?>">Concerts</a></li>
                        <li><a class="dropdown-item" href="<?= $eventsLink ?>?cat=Festivals">Festivals</a></li>
                        <li><a class="dropdown-item" href="<?= $eventsLink ?>?cat=Theatre">Theatre &amp; Arts</a></li>
                        <li><a class="dropdown-item" href="<?= $eventsLink ?>?cat=Sports">Sports</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $eventsLink ?>">On Sale Now</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= $venuesLink ?>">Venues</a>
                </li>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $adminLink ?>">Admin Panel</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-accent btn-sm nav-auth-btn dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            <?= htmlspecialchars($_SESSION['fname']) ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end pulse-dropdown">
                            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= $isAdminPage ? '../profile.php' : 'profile.php' ?>">My Profile</a></li>
                                <li><a class="dropdown-item" href="<?= $dashboardLink ?>">My Bookings</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>

                            <li><a class="dropdown-item text-danger" href="<?= $logoutLink ?>">Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $loginLink ?>" class="btn btn-outline-accent btn-sm nav-auth-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                        </svg>
                        Sign In / Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>