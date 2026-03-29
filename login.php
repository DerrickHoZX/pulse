<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Sign In &mdash; PULSE Events Singapore</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>
        <?php include "inc/nav.inc.php" ?>

        <main class="auth-page-wrapper">
            <div class="auth-card">
                <h1>Sign In</h1>
                <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom: 32px; font-weight:300;">
                    New here? <a href="register.php" style="color: var(--pulse-accent); text-decoration:none;">Create an account</a>.
                </p>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert-pulse mb-4">
                    <?php
                    $remaining = intval($_GET['remaining'] ?? 0);
                    $minutes   = intval($_GET['minutes'] ?? 5);
                    $errors = [
                        'missing' => '⚠ Please enter your email and password.',
                        'invalid' => '⚠ Incorrect email or password.' . ($remaining > 0 ? " $remaining attempt(s) remaining." : ''),
                        'locked'  => "⚠ Too many failed attempts. Please try again in $minutes minute(s).",
                    ];
                    echo $errors[$_GET['error']] ?? '⚠ Something went wrong. Please try again.';
                    ?>
                </div>
                <?php endif; ?>

                <form action="actions/process_login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="post">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input required maxlength="100" type="email" id="email" name="email"
                            class="form-control" placeholder="you@example.com"
                            value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label for="pwd" class="form-label">Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="pwd" name="pwd"
                                class="form-control" placeholder="Enter your password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('pwd', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                            Sign In
                        </button>
                    </div>
                </form>

                <p style="text-align:center; margin-top:20px;">
                    <a href="forgot_password.php" style="color: var(--pulse-muted); font-size:0.78rem; text-decoration:none;">Forgot your password?</a>
                    &nbsp;·&nbsp;
                    <a href="contact.php" style="color: var(--pulse-muted); font-size:0.78rem; text-decoration:none;">Need help?</a>
                </p>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>