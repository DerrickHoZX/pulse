<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $venue_id = intval($_POST['venue_id'] ?? 0) ?: null;
    $description = trim($_POST['description'] ?? '');
    $img_banner = trim($_POST['img_banner'] ?? '');
    $img_poster = trim($_POST['img_poster'] ?? '');
    $img_seatmap = trim($_POST['img_seatmap'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$title || !$event_date) {
        $error = 'Event name and date are required.';
    } else {
        // Insert event
        $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, event_time, venue_id, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssisi', $title, $category, $event_date, $event_time, $venue_id, $description, $is_active);
        $stmt->execute();
        $new_event_id = $conn->insert_id;
        $stmt->close();

        // Insert images into event_images
        $img_stmt = $conn->prepare("INSERT INTO event_images (event_id, image_type, image_path) VALUES (?, ?, ?)");
        foreach ([
            'banner' => $img_banner,
            'poster' => $img_poster,
            'seatmap' => $img_seatmap,
        ] as $type => $url) {
            if ($url) {
                $img_stmt->bind_param('iss', $new_event_id, $type, $url);
                $img_stmt->execute();
            }
        }
        $img_stmt->close();

        // Insert seat sections + generate individual seats
        $labels = $_POST['section_label'] ?? [];
        $prices = $_POST['section_price'] ?? [];
        $seats = $_POST['section_seats'] ?? [];

        $sec = $conn->prepare("INSERT INTO seat_sections (event_id, label, price, total_seats) VALUES (?, ?, ?, ?)");
        $seat = $conn->prepare("INSERT INTO seats (section_id, row_label, seat_num, status) VALUES (?, ?, ?, 'available')");

        foreach ($labels as $i => $label) {
            $label = trim($label);
            $price = floatval($prices[$i] ?? 0);
            $totalSeats = intval($seats[$i] ?? 0);

            if (!$label)
                continue;

            // Insert section
            $sec->bind_param('isdi', $new_event_id, $label, $price, $totalSeats);
            $sec->execute();
            $section_id = $conn->insert_id;

            // Generate seats: rows of 10
            $seatsPerRow = 10;
            $rows = ceil($totalSeats / $seatsPerRow);
            $seatCount = 0;

            for ($r = 0; $r < $rows; $r++) {
                $rowLabel = chr(65 + $r); // A, B, C...
                for ($s = 1; $s <= $seatsPerRow; $s++) {
                    if ($seatCount >= $totalSeats)
                        break;
                    $seat->bind_param('isi', $section_id, $rowLabel, $s);
                    $seat->execute();
                    $seatCount++;
                }
            }
        }

        $sec->close();
        $seat->close();
        $conn->close();

        header("Location: manage_events.php?added=1");
        exit;
    }
}

$conn = getDBConnection();
$venues = $conn->query("SELECT venue_id, name FROM venues ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>PULSE Admin - Add Event</title>
    <?php include "../inc/head.inc.php"; ?>
</head>

<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main class="container px-5 py-5">
        <div class="mb-4">
            <span class="section-label">Administration</span>
            <h2 class="section-title">Add <em>Event</em></h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-form-card">
            <form method="POST" action="add_event.php">

                <div class="mb-3">
                    <label class="form-label admin-form-label">Event Name</label>
                    <input type="text" name="title" class="form-control admin-form-control" required>
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
                    <label class="form-label admin-form-label">Time</label>
                    <input type="time" name="event_time" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Venue</label>
                    <select name="venue_id" class="form-control admin-form-control">
                        <option value="">-- Select Venue --</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?= $venue['venue_id'] ?>">
                                <?= htmlspecialchars($venue['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Description</label>
                    <textarea name="description" rows="5" class="form-control admin-form-control"></textarea>
                </div>
                <!-- Event Images -->
                <div class="mb-4">
                    <label class="form-label admin-form-label">Event Images</label>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Banner Image URL</label>
                        <input type="url" name="img_banner" class="form-control admin-form-control"
                            placeholder="https://example.com/banner.jpg">
                        <small style="color:var(--pulse-muted);">Main blurred background on the event detail
                            page.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Poster / Thumbnail
                            URL</label>
                        <input type="url" name="img_poster" class="form-control admin-form-control"
                            placeholder="https://example.com/poster.jpg">
                        <small style="color:var(--pulse-muted);">Portrait image shown on the event card and detail
                            page.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Seat Map URL</label>
                        <input type="url" name="img_seatmap" class="form-control admin-form-control"
                            placeholder="https://example.com/seatmap.jpg">
                        <small style="color:var(--pulse-muted);">Optional. Enables "View Seat Map" button on event
                            page.</small>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label admin-form-label" for="is_active">Active (visible to public)</label>
                </div>

                <!-- Seat Sections -->
                <div class="mb-4">
                    <label class="form-label admin-form-label">Seat Categories & Pricing</label>
                    <div id="sections-wrapper">
                        <div class="section-row d-flex gap-2 mb-2 align-items-center">
                            <input type="text" name="section_label[]" class="form-control admin-form-control"
                                placeholder="e.g. CAT 1 - Floor" style="flex:2;">
                            <input type="number" step="0.01" min="0" name="section_price[]"
                                class="form-control admin-form-control" placeholder="Price (S$)" style="flex:1;">
                            <input type="number" min="0" name="section_seats[]" class="form-control admin-form-control"
                                placeholder="No. of Seats" style="flex:1;">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-section"
                                style="white-space:nowrap;">✕ Remove</button>
                        </div>
                    </div>
                    <button type="button" id="add-section" class="btn btn-outline-light btn-sm mt-2">+ Add
                        Category</button>
                </div>

                <div class="d-flex gap-2 mt-2">
                    <a href="manage_events.php" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn-dark-solid">Save Event</button>
                </div>

            </form>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>

    <script>
        const wrapper = document.getElementById('sections-wrapper');

        document.getElementById('add-section').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'section-row d-flex gap-2 mb-2 align-items-center';
            row.innerHTML = `
                <input type="text" name="section_label[]" class="form-control admin-form-control" placeholder="e.g. CAT 2 - Lower Bowl" style="flex:2;">
                <input type="number" step="0.01" min="0" name="section_price[]" class="form-control admin-form-control" placeholder="Price (S$)" style="flex:1;">
                <input type="number" min="0" name="section_seats[]" class="form-control admin-form-control" placeholder="No. of Seats" style="flex:1;">
                <button type="button" class="btn btn-outline-danger btn-sm remove-section" style="white-space:nowrap;">✕ Remove</button>
            `;
            wrapper.appendChild(row);
        });

        wrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-section')) {
                const rows = wrapper.querySelectorAll('.section-row');
                if (rows.length > 1) {
                    e.target.closest('.section-row').remove();
                } else {
                    alert('You need at least one seat category.');
                }
            }
        });
    </script>
</body>

</html>