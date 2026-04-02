<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Register &mdash; PULSE Events Singapore</title>
        <?php include "inc/head.inc.php" ?>
    </head>
    <body>
        <?php include "inc/nav.inc.php" ?>

        <main class="auth-page-wrapper">
            <div class="auth-card">
                <h1>Create Account</h1>
                <p style="color: var(--pulse-muted); font-size:0.85rem; margin-bottom: 32px; font-weight:300;">
                    Already a member? <a href="login.php" style="color: var(--pulse-accent); text-decoration:none;">Sign in here</a>.
                </p>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert-pulse mb-4">
                    <?php
                    $errors = [
                        'missing'      => '⚠ Please fill in all fields.',
                        'invalidname'  => '⚠ Name can only contain letters.',
                        'invalidemail' => '⚠ Please enter a valid email address.',
                        'shortpwd'     => '⚠ Password must be at least 8 characters.',
                        'pwdupper'     => '⚠ Password must contain at least one uppercase letter.',
                        'pwdnumber'    => '⚠ Password must contain at least one number.',
                        'pwdspecial'   => '⚠ Password must contain at least one special character.',
                        'pwdmatch'     => '⚠ Passwords do not match.',
                        'agree'        => '⚠ You must agree to the Terms & Conditions.',
                        'captcha'      => '⚠ Please complete the CAPTCHA verification.',
                        'exists'       => '⚠ An account with that email already exists. <a href="login.php" style="color:inherit;font-weight:500;">Sign in instead?</a>',
                        'dbfail'       => '⚠ Registration failed. Please try again.',
                    ];
                    echo $errors[$_GET['error']] ?? '⚠ Something went wrong. Please try again.';
                    ?>
                </div>
                <?php endif; ?>

                <form action="actions/process_register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="post">

                    <div class="row g-3 mb-1">
                        <div class="col-6">
                            <label for="fname" class="form-label">First Name</label>
                            <input required maxlength="45" type="text" id="fname" name="fname"
                                autocomplete="given-name" class="form-control" placeholder="First name">
                        </div>
                        <div class="col-6">
                            <label for="lname" class="form-label">Last Name</label>
                            <input required maxlength="45" type="text" id="lname" name="lname"
                                autocomplete="family-name" class="form-control" placeholder="Last name">
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Email</label>
                        <input required maxlength="100" type="email" id="email" name="email"
                            autocomplete="email" class="form-control" placeholder="you@example.com">
                    </div>

                    <div class="mb-2">
                        <label for="pwd" class="form-label">Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="pwd" name="pwd"
                                autocomplete="new-password" class="form-control" placeholder="Create a password">
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
                        <div class="pwd-checklist mt-2">
                            <div class="pwd-rule" id="rule-length"><span class="pwd-rule-icon">✗</span> Minimum 8 characters</div>
                            <div class="pwd-rule" id="rule-upper"><span class="pwd-rule-icon">✗</span> At least one uppercase letter</div>
                            <div class="pwd-rule" id="rule-number"><span class="pwd-rule-icon">✗</span> At least one number</div>
                            <div class="pwd-rule" id="rule-special"><span class="pwd-rule-icon">✗</span> At least one special character (!@#$%^&*)</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="pwd_confirm" class="form-label">Confirm Password</label>
                        <div class="pwd-input-wrap">
                            <input required type="password" id="pwd_confirm" name="pwd_confirm"
                                autocomplete="new-password" class="form-control" placeholder="Confirm your password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('pwd_confirm', this)" aria-label="Show password">
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
                        <div id="pwd-match-msg" class="pwd-match-msg mt-1"></div>
                    </div>

                    <div class="mb-4 form-check">
                        <input required type="checkbox" name="agree" id="agree" class="form-check-input">
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="terms_of_service.php" style="color:var(--pulse-accent); text-decoration:none;">Terms of Service</a>.
                        </label>
                    </div>

                    <div id="captcha-section" style="display:none; margin-bottom: 16px;">
                        <div class="g-recaptcha" data-sitekey="6Ldq4Z0sAAAAAHeMUWUcZVNMRmvelY0eqey3tP_I" data-theme="dark"></div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" id="submit-btn" class="btn btn-accent w-100" style="justify-content:center; padding:14px; display:none;">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
        <script>
        document.getElementById('agree').addEventListener('change', function() {
            const captcha = document.getElementById('captcha-section');
            const btn = document.getElementById('submit-btn');
            if (this.checked) {
                captcha.style.display = 'block';
                btn.style.display = 'flex';
            } else {
                captcha.style.display = 'none';
                btn.style.display = 'none';
            }
        });
        </script>
    </body>
</html>