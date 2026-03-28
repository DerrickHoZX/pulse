<?php
session_start();
$success = $_SESSION['contact_success'] ?? '';
$errors  = $_SESSION['contact_errors'] ?? [];
$old     = $_SESSION['contact_old'] ?? [];
unset($_SESSION['contact_success'], $_SESSION['contact_errors'], $_SESSION['contact_old']);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Contact Us — PULSE Events</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>
        <?php include "inc/nav.inc.php" ?>

        <main>
            <!-- Page Hero -->
            <div class="page-hero">
                <div class="container-fluid px-5">
                    <span class="section-label">Get In Touch</span>
                    <h1 class="page-hero-title">Contact <em>Us</em></h1>
                    <p class="page-hero-sub">Have a question or need help? We'd love to hear from you.</p>
                </div>
            </div>

            <div class="container-fluid px-5 py-5">
                <div class="contact-wrapper">

                    <!-- Left: Form -->
                    <div class="contact-form-col">

                        <?php if (!empty($success)): ?>
                            <div class="alert-pulse-success mb-4">
                                ✓ <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert-pulse mb-4">
                                <?php foreach ($errors as $e): ?>
                                    <div>⚠ <?= htmlspecialchars($e) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="actions/process_contact.php" method="post">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name <span class="required-star">*</span></label>
                                    <input required maxlength="100" type="text" id="name" name="name"
                                        class="form-control" placeholder="Your full name"
                                        value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number <span style="color:var(--pulse-muted); font-size:0.75rem; letter-spacing:0.05em; text-transform:none;">(Optional)</span></label>
                                    <input maxlength="20" type="tel" id="phone" name="phone"
                                        class="form-control" placeholder="+65 1234 5678"
                                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="required-star">*</span></label>
                                <input required maxlength="100" type="email" id="email" name="email"
                                    class="form-control" placeholder="you@example.com"
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Contact <span class="required-star">*</span></label>
                                <div class="select-wrapper">
                                    <select required id="reason" name="reason" class="form-control">
                                        <option value="" disabled <?= empty($old['reason']) ? 'selected' : '' ?>>Select a reason...</option>
                                        <option value="Booking Issue" <?= ($old['reason'] ?? '') === 'Booking Issue' ? 'selected' : '' ?>>Booking Issue</option>
                                        <option value="Refund Request" <?= ($old['reason'] ?? '') === 'Refund Request' ? 'selected' : '' ?>>Refund Request</option>
                                        <option value="Event Enquiry" <?= ($old['reason'] ?? '') === 'Event Enquiry' ? 'selected' : '' ?>>Event Enquiry</option>
                                        <option value="Account Help" <?= ($old['reason'] ?? '') === 'Account Help' ? 'selected' : '' ?>>Account Help</option>
                                        <option value="Technical Issue" <?= ($old['reason'] ?? '') === 'Technical Issue' ? 'selected' : '' ?>>Technical Issue</option>
                                        <option value="General Enquiry" <?= ($old['reason'] ?? '') === 'General Enquiry' ? 'selected' : '' ?>>General Enquiry</option>
                                        <option value="Other" <?= ($old['reason'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="message" class="form-label">Message <span class="required-star">*</span></label>
                                <textarea required id="message" name="message" rows="6"
                                    class="form-control" placeholder="Tell us how we can help..."
                                    maxlength="2000"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                                <div class="contact-char-hint">Max 2000 characters</div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                                Send Message
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="ms-2">
                                    <path d="M2 7H12M12 7L8 3M12 7L8 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Right: Info -->
                    <div class="contact-info-col">
                        <div class="contact-info-card">
                            <h3 class="contact-info-title">Other Ways to Reach Us</h3>

                            <div class="contact-info-item">
                                <div class="contact-info-icon">✉</div>
                                <div>
                                    <div class="contact-info-label">Email</div>
                                    <a href="mailto:support@pulseevents.sg" class="contact-info-value">support@pulseevents.sg</a>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-info-icon">🕐</div>
                                <div>
                                    <div class="contact-info-label">Response Time</div>
                                    <div class="contact-info-value">Within 1–2 business days</div>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <div class="contact-info-icon">📍</div>
                                <div>
                                    <div class="contact-info-label">Based In</div>
                                    <div class="contact-info-value">Singapore</div>
                                </div>
                            </div>
                        </div>

                        <div class="contact-info-card mt-4">
                            <h3 class="contact-info-title">Quick Links</h3>
                            <ul class="contact-quick-links">
                                <li><a href="faq.php">→ Help Centre / FAQ</a></li>
                                <li><a href="terms_of_service.php">→ Terms of Service</a></li>
                                <li><a href="about_us.php">→ About Us</a></li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
