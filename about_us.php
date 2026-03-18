<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>About Us — PULSE Events</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>
        <?php include "inc/nav.inc.php" ?>

        <main>
            <!-- Page Hero -->
            <div class="page-hero">
                <div class="container-fluid px-5">
                    <span class="section-label">Our Story</span>
                    <h1 class="page-hero-title">We Are<br><em>PULSE</em></h1>
                    <p class="page-hero-sub">Singapore's premier destination for live music, sports, theatre, and unforgettable experiences.</p>
                </div>
            </div>

            <!-- Mission Section -->
            <section class="container-fluid px-5 py-6 fade-up">
                <div class="about-mission-grid">
                    <div>
                        <span class="section-label">Why We Exist</span>
                        <h2 class="section-title">Live Without<br><em>Limits</em></h2>
                        <p class="about-body">
                            PULSE was born from a simple belief — that live experiences have the power to move people, 
                            connect communities, and create memories that last a lifetime. We built this platform so that 
                            no one in Singapore ever has to miss out on the moments that matter most.
                        </p>
                        <p class="about-body">
                            From sold-out concerts at the Singapore Indoor Stadium to intimate theatre performances at 
                            the Esplanade, PULSE brings every live event in Singapore to one place — making discovery, 
                            booking, and attendance effortless.
                        </p>
                    </div>
                    <div class="about-mission-img-wrap">
                        <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=900&q=80"
                            alt="Live concert crowd" class="about-mission-img">
                    </div>
                </div>
            </section>

            <!-- Stats Bar -->
            <div class="about-stats-bar fade-up">
                <div class="container-fluid px-5">
                    <div class="about-stats-grid">
                        <div class="about-stat">
                            <div class="about-stat-number">500<span>+</span></div>
                            <div class="about-stat-label">Events Listed</div>
                        </div>
                        <div class="about-stat">
                            <div class="about-stat-number">50<span>K+</span></div>
                            <div class="about-stat-label">Tickets Sold</div>
                        </div>
                        <div class="about-stat">
                            <div class="about-stat-number">30<span>+</span></div>
                            <div class="about-stat-label">Venues Covered</div>
                        </div>
                        <div class="about-stat">
                            <div class="about-stat-number">20<span>K+</span></div>
                            <div class="about-stat-label">Happy Members</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Values Section -->
            <section class="container-fluid px-5 py-6 fade-up">
                <div class="text-center mb-5">
                    <span class="section-label">What Drives Us</span>
                    <h2 class="section-title">Our <em>Values</em></h2>
                </div>
                <div class="about-values-grid">
                    <div class="about-value-card">
                        <div class="about-value-icon">🎯</div>
                        <h3 class="about-value-title">Simplicity</h3>
                        <p class="about-value-body">Finding and booking tickets should be effortless. We obsess over making every step of the experience as smooth as possible.</p>
                    </div>
                    <div class="about-value-card">
                        <div class="about-value-icon">🔒</div>
                        <h3 class="about-value-title">Trust</h3>
                        <p class="about-value-body">Every booking on PULSE is secure. We protect your data, your payments, and your peace of mind — always.</p>
                    </div>
                    <div class="about-value-card">
                        <div class="about-value-icon">🎶</div>
                        <h3 class="about-value-title">Passion</h3>
                        <p class="about-value-body">We're fans first. Our team lives and breathes live events, which is why we build PULSE the way we do — for people who truly love the experience.</p>
                    </div>
                    <div class="about-value-card">
                        <div class="about-value-icon">🌏</div>
                        <h3 class="about-value-title">Community</h3>
                        <p class="about-value-body">Live events bring people together. PULSE is proud to be part of Singapore's vibrant arts, music, and entertainment community.</p>
                    </div>
                </div>
            </section>

            <!-- CTA -->
             <div class="container-fluid px-5 pb-6 fade-up">
                <div class="faq-contact-box">
                    <div>
                        <h3 class="faq-contact-title">Ready to Experience More?</h3>
                        <p class="faq-contact-sub">Create a free account and start booking your next live event today.</p>
                    </div>
                    <a href="register.php" class="btn btn-accent">
                        Get Started
                        <svg width="13" height="13" viewBox="0 0 14 14" fill="none" class="ms-2">
                            <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </main>
        <?php include "inc/footer.inc.php" ?>
    </body>
</html>