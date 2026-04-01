<footer class="pulse-footer">
    <?php
    $currentDir = dirname($_SERVER['PHP_SELF']);
    $isAdminPage = (strpos($currentDir, '/admin') !== false);
    $base = $isAdminPage ? '../' : '';
    ?>
    <div class="container-fluid px-5">
        <div class="row gy-5 footer-main">
            <div class="col-lg-4 col-md-6">
                <a href="<?= $base ?>index.php" class="footer-brand">PUL<span class="brand-accent">S</span>E</a>
                <p class="footer-desc">
                    Singapore's premier destination for live music, sports, theatre, and unforgettable experiences.
                </p>
            </div>

            <div class="col-lg-2 col-md-3 col-6 ms-auto">
                <h2 class="footer-col-title">Explore</h2>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>events.php">Concerts</a></li>
                    <li><a href="<?= $base ?>events.php?cat=Festivals">Festivals</a></li>
                    <li><a href="<?= $base ?>events.php?cat=Theatre">Theatre &amp; Arts</a></li>
                    <li><a href="<?= $base ?>events.php?cat=Sports">Sports</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h2 class="footer-col-title">Support</h2>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>about_us.php">About Us</a></li>
                    <li><a href="<?= $base ?>faq.php">FAQ</a></li>
                    <li><a href="<?= $base ?>contact.php">Contact Us</a></li>
                    <li><a href="<?= $base ?>terms_of_service.php">Terms of Service</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h2 class="footer-col-title">Account</h2>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                            <li><a href="<?= $base ?>profile.php">My Profile</a></li>
                            <li><a href="<?= $base ?>dashboard.php">My Bookings</a></li>
                        <?php endif; ?>
                        <li><a href="<?= $base ?>actions/logout.php">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="<?= $base ?>login.php">Sign In</a></li>
                        <li><a href="<?= $base ?>register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p><em>Copyright &copy; 2026 PULSE Events Pte. Ltd. All rights reserved.</em></p>
        </div>
    </div>
</footer>