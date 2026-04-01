<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password &mdash; PULSE Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="auth-page-wrapper">
        <div class="auth-card">
            <h1>Forgot Password</h1>
            <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom: 32px; font-weight:300;">
                Enter your email address and we'll send you a link to reset your password.
            </p>

            <?php if (isset($_GET['status'])): ?>
            <div class="<?= $_GET['status'] === 'sent' ? 'alert-pulse-success' : 'alert-pulse' ?> mb-4">
                <?php
                $msgs = [
                    'sent'    => '✓ A reset link has been sent. Check your inbox.',
                    'notfound' => '⚠ No account found with that email address.',
                    'invalid' => '⚠ Please enter a valid email address.',
                ];
                echo $msgs[$_GET['status']] ?? '⚠ Something went wrong.';
                ?>
            </div>
            <?php endif; ?>

            <form action="actions/process_forgot_password.php" method="post">
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input required maxlength="100" type="email" id="email" name="email"
                        class="form-control" placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                    Send Reset Link
                </button>
            </form>

            <p style="text-align:center; margin-top:24px;">
                <a href="login.php" style="color: var(--pulse-muted); font-size:0.78rem; text-decoration:none;">
                    ← Back to Sign In
                </a>
            </p>
        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>