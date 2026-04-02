<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>FAQ — PULSE Events</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>
        <?php include "inc/nav.inc.php" ?>

        <main>
            <!-- Page Header -->
            <div class="page-hero">
                <div class="container-fluid px-5">
                    <h1 class="page-hero-title">Frequently Asked<br><em>Questions</em></h1>
                    <p class="page-hero-sub">Everything you need to know about booking, tickets and your account.</p>
                </div>
            </div>

            <div class="container-fluid px-5 py-5">
                <div class="faq-wrapper">

                    <!-- Category: Tickets & Booking -->
                    <div class="faq-section fade-up">
                        <h2 class="faq-category-title">
                            <span>🎟</span> Tickets & Booking
                        </h2>
                        <div class="accordion" id="faqTickets">

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-t1" aria-expanded="false">
                                    How do I purchase tickets?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-t1" class="collapse faq-answer" data-bs-parent="#faqTickets">
                                    Browse events on our homepage or All Events page, click <strong>Get Tickets</strong> on your chosen event, select your preferred date, section, and quantity, then proceed to checkout. You'll need a PULSE account to complete your booking.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-t2" aria-expanded="false">
                                    What payment methods are accepted?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-t2" class="collapse faq-answer" data-bs-parent="#faqTickets">
                                    We currently accept <strong>PayNow (QR)</strong> and <strong>credit/debit card payment</strong>. Additional payment methods may be added in the future.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-t3" aria-expanded="false">
                                    How will I receive my tickets?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-t3" class="collapse faq-answer" data-bs-parent="#faqTickets">
                                    Once your booking is confirmed, you will receive an email with your event details and a <strong>QR code / PDF ticket</strong>. Your tickets are also accessible anytime under <strong>My Bookings</strong> in your account dashboard.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-t4" aria-expanded="false">
                                    Can I choose my seat?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-t4" class="collapse faq-answer" data-bs-parent="#faqTickets">
                                    You can select your preferred <strong>seating section</strong> during checkout. Seats within a section are allocated on a <strong>first come, first served</strong> basis.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-t5" aria-expanded="false">
                                    Is there a limit on how many tickets I can buy?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-t5" class="collapse faq-answer" data-bs-parent="#faqTickets">
                                    Ticket limits vary by event and are set by the organiser. The maximum quantity allowed per booking will be displayed during the ticket selection step.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Category: Cancellations & Refunds -->
                    <div class="faq-section fade-up">
                        <h2 class="faq-category-title">
                            <span>💸</span> Cancellations & Refunds
                        </h2>
                        <div class="accordion" id="faqRefunds">

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-r1" aria-expanded="false">
                                    Can I cancel my booking?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-r1" class="collapse faq-answer" data-bs-parent="#faqRefunds">
                                    You may cancel your booking through <strong>My Bookings</strong> in your account dashboard. Cancellations are subject to the event's refund policy. Please check the event details page for specific terms.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-r2" aria-expanded="false">
                                    How long does a refund take?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-r2" class="collapse faq-answer" data-bs-parent="#faqRefunds">
                                    Approved refunds are typically processed within <strong>5–10 business days</strong>. You will be notified via email once your refund has been issued.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-r3" aria-expanded="false">
                                    What happens if an event is cancelled?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-r3" class="collapse faq-answer" data-bs-parent="#faqRefunds">
                                    If an event is cancelled by the organiser, all ticket holders will be notified via email and a <strong>full refund</strong> will be automatically issued. No action is required on your part.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Category: Account -->
                    <div class="faq-section fade-up">
                        <h2 class="faq-category-title">
                            <span>👤</span> Account
                        </h2>
                        <div class="accordion" id="faqAccount">

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-a1" aria-expanded="false">
                                    Do I need an account to buy tickets?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-a1" class="collapse faq-answer" data-bs-parent="#faqAccount">
                                    Yes, a PULSE account is required to complete a booking. Registration is free and only takes a minute. You can <a href="register.php" style="color: var(--pulse-accent); text-decoration:underline;">create an account here</a>.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-a2" aria-expanded="false">
                                    How do I update my profile details?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-a2" class="collapse faq-answer" data-bs-parent="#faqAccount">
                                    Once logged in, go to your <strong>Profile</strong> page from the navigation menu. From there you can update your name, email address, and password.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-a3" aria-expanded="false">
                                    I forgot my password. What do I do?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-a3" class="collapse faq-answer" data-bs-parent="#faqAccount">
                                    If you cannot sign in, use the help link on the Sign In page or <a href="contact.php" style="color: var(--pulse-accent); text-decoration:underline;">contact support</a> and we will help you recover access.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-a4" aria-expanded="false">
                                    How do I delete my account?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-a4" class="collapse faq-answer" data-bs-parent="#faqAccount">
                                    You can delete your account from your <strong>Profile</strong> page. Please note that deleting your account is permanent and will remove all your booking history. Any upcoming bookings should be cancelled first.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Category: Events -->
                    <div class="faq-section fade-up">
                        <h2 class="faq-category-title">
                            <span>🎤</span> Events
                        </h2>
                        <div class="accordion" id="faqEvents">

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-e1" aria-expanded="false">
                                    How do I find events by category or venue?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-e1" class="collapse faq-answer" data-bs-parent="#faqEvents">
                                    Use the <strong>search bar</strong> on the homepage to filter events by category, date, or keyword. You can also browse by venue or use the category pills to quickly find shows by genre.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-e2" aria-expanded="false">
                                    Are all events in Singapore?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-e2" class="collapse faq-answer" data-bs-parent="#faqEvents">
                                    Yes — PULSE is currently focused exclusively on live events happening across <strong>Singapore</strong>, covering venues like the Singapore Indoor Stadium, Esplanade, The Star Theatre, and more.
                                </div>
                            </div>

                            <div class="faq-item">
                                <button class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq-e3" aria-expanded="false">
                                    How do I know if an event is selling fast?
                                    <svg class="faq-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </button>
                                <div id="faq-e3" class="collapse faq-answer" data-bs-parent="#faqEvents">
                                    Events with high demand are tagged with a <strong>🔥 Selling Fast</strong> badge on the event card. We recommend booking early to avoid missing out.
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Still need help -->
                    <div class="faq-contact-box fade-up">
                        <div>
                            <h3 class="faq-contact-title">Still need help?</h3>
                            <p class="faq-contact-sub">Can't find what you're looking for? Our support team is here for you.</p>
                        </div>
                        <a href="contact.php" class="btn btn-accent">
                            Contact Us
                            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" class="ms-2">
                                <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
