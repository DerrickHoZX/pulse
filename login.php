<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Sign In — PULSE Events</title>
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

                <form action="process_login.php" method="post">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input required maxlength="45" type="email" id="email" name="email"
                            class="form-control" placeholder="you@example.com">
                    </div>
                    <div class="mb-4">
                        <label for="pwd" class="form-label">Password</label>
                        <input required type="password" id="pwd" name="pwd"
                            class="form-control" placeholder="Enter your password">
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-accent w-100" style="justify-content:center; padding:14px;">
                            Sign In
                        </button>
                    </div>
                </form>

                <p style="text-align:center; margin-top:20px;">
                    <a href="#" style="color: var(--pulse-muted); font-size:0.78rem; text-decoration:none;">Forgot your password?</a>
                </p>
            </div>
        </main>

        <?php include "inc/footer.inc.php" ?>
    </body>
</html>
