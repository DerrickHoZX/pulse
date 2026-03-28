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
                <div class="alert-pulse">
                    <?php
                    $errors = [
                        'missing'      => 'Please fill in all fields.',
                        'invalidemail' => 'Please enter a valid email address.',
                        'shortpwd'     => 'Password must be at least 8 characters.',
                        'pwdmatch'     => 'Passwords do not match.',
                        'exists'       => 'An account with that email already exists. <a href="login.php" style="color:inherit;font-weight:500;">Sign in instead?</a>',
                        'dbfail'       => 'Registration failed. Please try again.',
                    ];
                    echo $errors[$_GET['error']] ?? 'Something went wrong. Please try again.';
                    ?>
                </div>
                <?php endif; ?>

                <form action="actions/process_register.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="post">
                    <div class="row g-3 mb-1">
                        <div class="col-6">
                            <label for="fname" class="form-label">First Name</label>
                            <input required maxlength="45" type="text" id="fname" name="fname"
                                class="form-control" placeholder="First name">
                        </div>
                        <div class="col-6">
                            <label for="lname" class="form-label">Last Name</label>
                            <input required maxlength="45" type="text" id="lname" name="lname"
                                class="form-control" placeholder="Last name">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Email</label>
                        <input required maxlength="100" type="email" id="email" name="email"
                            class="form-control" placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label">Password</label>
                        <input required type="password" id="pwd" name="pwd"
                            class="form-control" placeholder="Minimum 8 characters">
                    </div>
                    <div class="mb-4">
                        <label for="pwd_confirm" class="form-label">Confirm Password</label>
                        <input required type="password" id="pwd_confirm" name="pwd_confirm"
                            class="form-control" placeholder="Confirm your password">
                    </div>
                    <div class="mb-4 form-check">
                        <input required type="checkbox" name="agree" id="agree" class="form-check-input">
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="terms_of_service.php" style="color:var(--pulse-accent); text-decoration:none;">Terms &amp; Conditions</a>.
                        </label>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
