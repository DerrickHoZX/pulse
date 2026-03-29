<?php
include "../inc/admin_check.inc.php";
$basePath = "../";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Add Event</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<div style="margin-top: 100px;"></div>
<body>
    <?php include "../inc/nav.inc.php"; ?>

    <div class="admin-page-offset"></div>

    <main class="container px-5 py-5">
        <div class="mb-4">
            <span class="section-label">Administration</span>
            <h2 class="section-title">Add <em>Event</em></h2>
        </div>

        <div class="admin-form-card">
            <form method="POST" action="#">
                <div class="mb-3">
                    <label class="form-label admin-form-label">Event Name</label>
                    <input type="text" name="event_name" class="form-control admin-form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Category</label>
                    <input type="text" name="category" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Date</label>
                    <input type="date" name="event_date" class="form-control admin-form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Venue</label>
                    <input type="text" name="venue" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Price</label>
                    <input type="text" name="price" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Description</label>
                    <textarea name="description" rows="5" class="form-control admin-form-control"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <a href="manage_events.php" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn-dark-solid">Save Event</button>
                </div>
            </form>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>
</html>