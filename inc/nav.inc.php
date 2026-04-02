<?php 
// 1. Session Cookie Lockdown (Security Patch)
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1); // Uncomment this line AFTER you install your SSL/HTTPS certificate!

if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

// 2. Generate Global CSRF Token (Security Patch)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = dirname($_SERVER['PHP_SELF']);
$isAdminPage = (strpos($currentDir, '/admin') !== false);

$homeLink      = $isAdminPage ? '../index.php' : 'index.php';
$eventsLink    = $isAdminPage ? '../events.php' : 'events.php';
$dashboardLink = $isAdminPage ? '../dashboard.php' : 'dashboard.php';
$logoutLink    = $isAdminPage ? '../actions/logout.php' : 'actions/logout.php';
$loginLink     = $isAdminPage ? '../login.php' : 'login.php';
$adminLink     = $isAdminPage ? 'admin.php' : 'admin/admin.php';
$venuesLink    = $homeLink . '#venues';

// Determine active link
$isHome   = in_array($currentPage, ['index.php']) && !$isAdminPage;
$isEvents = $currentPage === 'events.php';
$isVenues = false; // anchor link, never truly "active"
$isAdmin  = $isAdminPage;
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
                    <a class="nav-link <?= $isHome ? 'active' : '' ?>" href="<?= $homeLink ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isEvents ? 'active' : '' ?>" href="<?= $eventsLink ?>">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $venuesLink ?>">Venues</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $isAdmin ? 'active' : '' ?>" href="<?= $adminLink ?>">Admin Panel</a>
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