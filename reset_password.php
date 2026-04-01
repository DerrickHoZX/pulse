<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password &mdash; PULSE Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="auth-page-wrapper">
        <div class="auth-card">
            <?php
            $token = trim($_GET['token'] ?? '');

            if (!$token):
            ?>
                <h1>Invalid Link</h1>
                <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom:24px; font-weight:300;">
                    This reset link is invalid. Please request a new one.
                </p>
                <a href="forgot_password.php" class="btn btn-accent" style="display:inline-flex; padding:12px 28px;">
                    Request New Link
                </a>

            <?php else: ?>

            <?php
            require_once 'inc/db.inc.php';
            $conn = getDBConnection();
            $chk = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() AND is_deleted = 0");
            $chk->bind_param('s', $token);
            $chk->execute();
            $valid = $chk->get_result()->fetch_assoc();
            $chk->close();
            $conn->close();

            if (!$valid):
            ?>
                <h1>Link Expired</h1>
                <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom:24px; font-weight:300;">
                    This reset link has expired. Please request a new one.
                </p>
                <a href="forgot_password.php" class="btn btn-accent" style="display:inline-flex; padding:12px 28px;">
                    Request New Link
                </a>

            <?php else: ?>

                <h1>Reset Password</h1>
                <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom: 32px; font-weight:300;">
                    Enter your new password below.
                </p>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert-pulse mb-4">⚠ <?php
                    $errs = [
                        'invalid'   => 'This reset link is invalid or has expired. Please request a new one.',
                        'shortpwd'  => 'Password must be at least 8 characters.',
                        'pwdupper'  => 'Password must contain at least one uppercase letter.',
                        'pwdnumber' => 'Password must contain at least one number.',
                        'pwdspecial'=> 'Password must contain at least one special character.',
                        'pwdmatch'  => 'Passwords do not match.',
                    ];
                    echo $errs[$_GET['error']] ?? 'Something went wrong. Please try again.';
                ?></div>
                <?php endif; ?>

                <form action="actions/process_reset_password.php" method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="new_pwd" name="new_pwd"
                                class="form-control" placeholder="Enter new password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('new_pwd', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <div class="pwd-checklist mt-2">
                            <div class="pwd-rule" id="profile-rule-length"><span class="pwd-rule-icon">✗</span> Minimum 8 characters</div>
                            <div class="pwd-rule" id="profile-rule-upper"><span class="pwd-rule-icon">✗</span> At least one uppercase letter</div>
                            <div class="pwd-rule" id="profile-rule-number"><span class="pwd-rule-icon">✗</span> At least one number</div>
                            <div class="pwd-rule" id="profile-rule-special"><span class="pwd-rule-icon">✗</span> At least one special character (!@#$%^&*)</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="new_pwd_confirm" name="new_pwd_confirm"
                                class="form-control" placeholder="Confirm new password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('new_pwd_confirm', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <div id="profile-pwd-match-msg" class="pwd-match-msg mt-1"></div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                        Reset Password
                    </button>
                </form>

                <p style="text-align:center; margin-top:24px;">
                    <a href="forgot_password.php" style="color: var(--pulse-muted); font-size:0.78rem; text-decoration:none;">
                        Request a new link
                    </a>
                </p>

            <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>