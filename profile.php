<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit;
}
require_once 'inc/db.inc.php';

// Fetch latest user data from DB
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile &mdash; PULSE Events Singapore</title>
    <?php include "inc/head.inc.php" ?>
</head>
<body>
    <?php include "inc/nav.inc.php" ?>

    <main class="auth-page-wrapper" style="align-items: flex-start; padding-top: 120px;">
        <div style="width: 100%; max-width: 560px; margin: 0 auto;">

            <h1 style="font-family: var(--font-display); font-size: 2.5rem; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 6px;">My Profile</h1>
            <p style="color: var(--pulse-muted); font-size: 0.85rem; font-weight: 300; margin-bottom: 40px;">
                Manage your account details and password.
            </p>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert-pulse-success mb-4">✓ <?php
                $msgs = [
                    'details'  => 'Your details have been updated.',
                    'password' => 'Your password has been changed.',
                ];
                echo $msgs[$_GET['success']] ?? 'Changes saved.';
            ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert-pulse mb-4">⚠ <?php
                $errs = [
                    'invalidname'  => 'Name can only contain letters.',
                    'invalidemail' => 'Please enter a valid email address.',
                    'emailtaken'   => 'That email is already in use by another account.',
                    'wrongpwd'     => 'Your current password is incorrect.',
                    'shortpwd'     => 'New password must be at least 8 characters.',
                    'pwdupper'     => 'New password must contain at least one uppercase letter.',
                    'pwdnumber'    => 'New password must contain at least one number.',
                    'pwdspecial'   => 'New password must contain at least one special character.',
                    'pwdmatch'     => 'New passwords do not match.',
                    'deletewrong'  => 'Incorrect password. Account not deleted.',
                ];
                echo $errs[$_GET['error']] ?? 'Something went wrong. Please try again.';
            ?></div>
            <?php endif; ?>

            <!-- ── Personal Details ── -->
            <div class="auth-card mb-4" style="max-width:100%;">
                <h2 style="font-family: var(--font-display); font-size: 1.3rem; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 24px;">Personal Details</h2>

                <form action="actions/process_profile.php" method="post">
                    <input type="hidden" name="action" value="details">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">First Name</label>
                            <input required maxlength="45" type="text" name="fname" class="form-control"
                                value="<?= htmlspecialchars($user['fname']) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name</label>
                            <input required maxlength="45" type="text" name="lname" class="form-control"
                                value="<?= htmlspecialchars($user['lname']) ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input required maxlength="100" type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <button type="submit" class="btn btn-accent" style="padding: 12px 28px;">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- ── Change Password ── -->
            <div class="auth-card mb-4" style="max-width:100%;">
                <h2 style="font-family: var(--font-display); font-size: 1.3rem; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 24px;">Change Password</h2>

                <form action="actions/process_profile.php" method="post">
                    <input type="hidden" name="action" value="password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="current_pwd" name="current_pwd" class="form-control" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('current_pwd', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="new_pwd" name="new_pwd" class="form-control" placeholder="Enter new password">
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
                            <input required type="password" id="new_pwd_confirm" name="new_pwd_confirm" class="form-control" placeholder="Confirm new password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('new_pwd_confirm', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                        <div id="profile-pwd-match-msg" class="pwd-match-msg mt-1"></div>
                    </div>
                    <button type="submit" class="btn btn-accent" style="padding: 12px 28px;">
                        Update Password
                    </button>
                </form>
            </div>

            <!-- ── Delete Account ── -->
            <div class="auth-card" style="max-width:100%; border-color: rgba(226,75,74,0.3);">
                <h2 style="font-family: var(--font-display); font-size: 1.3rem; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 8px; color: #e24b4a;">Delete Account</h2>
                <p style="color: var(--pulse-muted); font-size: 0.84rem; font-weight: 300; margin-bottom: 24px;">
                    Permanently delete account and all data. This cannot be undone.
                </p>

                <button type="button" class="btn" onclick="document.getElementById('delete-form').style.display='block'; this.style.display='none';"
                    style="border: 1px solid rgba(226,75,74,0.5); color: #e24b4a; background: transparent; padding: 10px 24px; font-size: 0.75rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer;">
                    Delete My Account
                </button>

                <form id="delete-form" action="actions/process_delete_account.php" method="post" style="display:none; margin-top: 20px;">
                    <p style="color: #e24b4a; font-size: 0.82rem; margin-bottom: 16px;">
                        Enter your password to confirm deletion:
                    </p>
                    <div class="mb-3">
                        <div class="pwd-input-wrap">
                            <input required type="password" id="delete_pwd" name="delete_pwd" class="form-control" placeholder="Enter your password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('delete_pwd', this)" aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn"
                            style="background: #e24b4a; color: #fff; border: none; padding: 10px 24px; font-size: 0.75rem; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer;">
                            Confirm Delete
                        </button>
                        <button type="button" onclick="document.getElementById('delete-form').style.display='none'; document.querySelector('[onclick*=delete-form]').style.display='inline-block';"
                            style="background: none; border: none; color: var(--pulse-muted); font-size: 0.8rem; cursor: pointer; text-decoration: underline;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <?php include "inc/footer.inc.php" ?>
</body>
</html>