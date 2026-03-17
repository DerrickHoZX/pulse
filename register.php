<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Register — PULSE Events</title>
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

                <form action="process_register.php" method="post">
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
                        <input required maxlength="45" type="email" id="email" name="email"
                            class="form-control" placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label">Password</label>
                        <input required type="password" id="pwd" name="pwd"
                            class="form-control" placeholder="Create a password">
                    </div>
                    <div class="mb-4">
                        <label for="pwd_confirm" class="form-label">Confirm Password</label>
                        <input required type="password" id="pwd_confirm" name="pwd_confirm"
                            class="form-control" placeholder="Confirm your password">
                    </div>
                    <div class="mb-4 form-check">
                        <input required type="checkbox" name="agree" id="agree" class="form-check-input">
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="#" style="color:var(--pulse-accent); text-decoration:none;">Terms &amp; Conditions</a>.
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
