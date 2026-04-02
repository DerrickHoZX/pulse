<?php
session_start();

// Must have a pending OTP session — otherwise kick back to login
if (empty($_SESSION['otp_pending_id'])) {
    header('Location: login.php');
    exit;
}

// Mask email for display  e.g.  ad***@gmail.com
$rawEmail = $_SESSION['otp_pending_email'] ?? '';
$atPos    = strpos($rawEmail, '@');
$masked   = $atPos !== false
    ? substr($rawEmail, 0, 2) . str_repeat('*', max(1, $atPos - 2)) . substr($rawEmail, $atPos)
    : '***@***.***';

$fname = htmlspecialchars($_SESSION['otp_pending_fname'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Verification &mdash; PULSE Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="auth-page-wrapper">
        <div class="auth-card">

            <h1>Admin Verification</h1>
            <p style="color:var(--pulse-muted); font-size:0.85rem; margin-bottom:6px; font-weight:300;">
                Hi <?= $fname ?>, a 6-digit code was sent to
            </p>
            <p style="color:var(--pulse-accent); font-size:0.9rem; font-weight:500; margin-bottom:28px; letter-spacing:0.04em;">
                <?= htmlspecialchars($masked) ?>
            </p>

            <?php if (isset($_GET['resent'])): ?>
            <div class="alert-pulse-success mb-4">✓ A new code has been sent to your email.</div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert-pulse mb-4">
                <?php
                $errors = [
                    'invalid' => '⚠ Incorrect code. Please try again.',
                    'expired' => '⚠ That code has expired. Request a new one below.',
                    'missing' => '⚠ Please enter the 6-digit code.',
                ];
                echo $errors[$_GET['error']] ?? '⚠ Something went wrong. Please try again.';
                ?>
            </div>
            <?php endif; ?>

            <!-- OTP verify form -->
            <form action="actions/process_otp.php" method="post">
                <input type="hidden" name="action" value="verify">
                <div class="mb-4">
                    <label for="otp" class="form-label">Verification Code</label>
                    <input
                        required
                        type="text"
                        id="otp"
                        name="otp"
                        inputmode="numeric"
                        pattern="\d{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        class="form-control"
                        placeholder="000000"
                        style="letter-spacing:0.4em; font-size:1.5rem; text-align:center; font-weight:600;"
                        autofocus>
                    <div style="font-size:0.75rem; color:var(--pulse-muted); margin-top:6px;">
                        Enter the 6-digit code. Expires in <strong style="color:var(--pulse-fg);">5 minutes</strong>.
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                        Verify &amp; Enter Admin Panel
                    </button>
                </div>
            </form>

            <!-- Resend form -->
            <form action="actions/process_otp.php" method="post" style="text-align:center; margin-top:20px;">
                <input type="hidden" name="action" value="resend">
                <p style="font-size:0.8rem; color:var(--pulse-muted); margin-bottom:8px;">
                    Didn't receive a code or it expired?
                </p>
                <button type="submit" class="btn" style="background:transparent; border:1px solid var(--pulse-muted);
                    color:var(--pulse-muted); font-size:0.8rem; padding:8px 20px;
                    letter-spacing:0.06em; text-transform:uppercase;">
                    Resend Code
                </button>
            </form>

            <p style="text-align:center; margin-top:28px;">
                <a href="login.php" onclick="return confirm('Going back will cancel this sign-in. Continue?')"
                   style="color:var(--pulse-muted); font-size:0.78rem; text-decoration:none;">
                    ← Back to Sign In
                </a>
            </p>

        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>

    <script>
    // Auto-submit once 6 digits are entered
    document.getElementById('otp').addEventListener('input', function () {
        if (/^\d{6}$/.test(this.value)) {
            this.closest('form').submit();
        }
    });
    </script>
</body>
</html>