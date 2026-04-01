<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    header("Location: manage_events.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $event_date  = $_POST['event_date'] ?? '';
        $event_time  = $_POST['event_time'] ?? '';
        $venue_id    = intval($_POST['venue_id'] ?? 0) ?: null;
        $description = trim($_POST['description'] ?? '');
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE events SET title=?, category=?, event_date=?, event_time=?, venue_id=?, description=?, is_active=? WHERE event_id=?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param('ssssisii', $title, $category, $event_date, $event_time, $venue_id, $description, $is_active, $event_id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();

        $img_banner  = trim($_POST['img_banner'] ?? '');
        $img_poster  = trim($_POST['img_poster'] ?? '');
        $img_seatmap = trim($_POST['img_seatmap'] ?? '');

        $del_img = $conn->prepare("DELETE FROM event_images WHERE event_id = ? AND image_type IN ('banner','poster','seatmap')");
        if (!$del_img) {
            throw new Exception($conn->error);
        }
        $del_img->bind_param('i', $event_id);
        if (!$del_img->execute()) {
            throw new Exception($del_img->error);
        }
        $del_img->close();

        $img_stmt = $conn->prepare("INSERT INTO event_images (event_id, image_type, image_path) VALUES (?, ?, ?)");
        if (!$img_stmt) {
            throw new Exception($conn->error);
        }
        foreach ([
            'banner'  => $img_banner,
            'poster'  => $img_poster,
            'seatmap' => $img_seatmap,
        ] as $type => $url) {
            if ($url) {
                $img_stmt->bind_param('iss', $event_id, $type, $url);
                if (!$img_stmt->execute()) {
                    throw new Exception($img_stmt->error);
                }
            }
        }
        $img_stmt->close();

        $sec_ids    = $_POST['section_id'] ?? [];
        $sec_labels = $_POST['section_label'] ?? [];
        $sec_prices = $_POST['section_price'] ?? [];
        $sec_seats  = $_POST['section_seats'] ?? [];

        $upd = $conn->prepare("UPDATE seat_sections SET label=?, price=?, total_seats=? WHERE section_id=? AND event_id=?");
        $del_bs = $conn->prepare("
            DELETE bs
            FROM booking_seats bs
            JOIN seats s ON bs.seat_id = s.seat_id
            WHERE s.section_id = ?
        ");
        $del = $conn->prepare("DELETE FROM seats WHERE section_id=?");
        $seat = $conn->prepare("INSERT INTO seats (section_id, row_label, seat_num, status) VALUES (?, ?, ?, 'available')");

        if (!$upd || !$del_bs || !$del || !$seat) {
            throw new Exception($conn->error);
        }

        foreach ($sec_ids as $i => $sec_id) {
            $sec_id     = intval($sec_id);
            $label      = trim($sec_labels[$i] ?? '');
            $price      = floatval($sec_prices[$i] ?? 0);
            $totalSeats = intval($sec_seats[$i] ?? 0);

            if (!$label) continue;

            $upd->bind_param('sdiii', $label, $price, $totalSeats, $sec_id, $event_id);
            if (!$upd->execute()) {
                throw new Exception($upd->error);
            }

            $del_bs->bind_param('i', $sec_id);
            if (!$del_bs->execute()) {
                throw new Exception($del_bs->error);
            }

            $del->bind_param('i', $sec_id);
            if (!$del->execute()) {
                throw new Exception($del->error);
            }

            $seatsPerRow = 10;
            $rows = ceil($totalSeats / $seatsPerRow);
            $seatCount = 0;

            for ($r = 0; $r < $rows; $r++) {
                $rowLabel = chr(65 + $r);
                for ($s = 1; $s <= $seatsPerRow; $s++) {
                    if ($seatCount >= $totalSeats) break;
                    $seat->bind_param('isi', $sec_id, $rowLabel, $s);
                    if (!$seat->execute()) {
                        throw new Exception($seat->error);
                    }
                    $seatCount++;
                }
            }
        }

        $upd->close();
        $del_bs->close();
        $del->close();

        $new_labels = $_POST['new_section_label'] ?? [];
        $new_prices = $_POST['new_section_price'] ?? [];
        $new_seats  = $_POST['new_section_seats'] ?? [];

        $ins = $conn->prepare("INSERT INTO seat_sections (event_id, label, price, total_seats) VALUES (?, ?, ?, ?)");
        if (!$ins) {
            throw new Exception($conn->error);
        }

        foreach ($new_labels as $i => $label) {
            $label      = trim($label);
            $price      = floatval($new_prices[$i] ?? 0);
            $totalSeats = intval($new_seats[$i] ?? 0);
            if (!$label) continue;

            $ins->bind_param('isdi', $event_id, $label, $price, $totalSeats);
            if (!$ins->execute()) {
                throw new Exception($ins->error);
            }
            $new_sec_id = $conn->insert_id;

            $seatsPerRow = 10;
            $rows = ceil($totalSeats / $seatsPerRow);
            $seatCount = 0;
            for ($r = 0; $r < $rows; $r++) {
                $rowLabel = chr(65 + $r);
                for ($s = 1; $s <= $seatsPerRow; $s++) {
                    if ($seatCount >= $totalSeats) break;
                    $seat->bind_param('isi', $new_sec_id, $rowLabel, $s);
                    if (!$seat->execute()) {
                        throw new Exception($seat->error);
                    }
                    $seatCount++;
                }
            }
        }

        $ins->close();
        $seat->close();

        $conn->commit();
        $conn->close();

        header("Location: manage_events.php?updated=1");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        die("Edit failed: " . $e->getMessage());
    }
}

// Fetch event
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: manage_events.php");
    exit;
}

// Fetch existing images
$existing_imgs = [];
$img_res = $conn->prepare("SELECT image_type, image_path FROM event_images WHERE event_id = ? ORDER BY sort_order ASC");
$img_res->bind_param('i', $event_id);
$img_res->execute();
foreach ($img_res->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    if (!isset($existing_imgs[$row['image_type']])) {
        $existing_imgs[$row['image_type']] = $row['image_path'];
    }
}
$img_res->close();

// Fetch existing sections
$sec_stmt = $conn->prepare("SELECT * FROM seat_sections WHERE event_id = ? ORDER BY section_id");
$sec_stmt->bind_param('i', $event_id);
$sec_stmt->execute();
$sections = $sec_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sec_stmt->close();

// Fetch venues
$venues = $conn->query("SELECT venue_id, name FROM venues ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Edit Event</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main class="container px-5 py-5">
        <div class="mb-4">
            <span class="section-label">Administration</span>
            <h2 class="section-title">Edit <em>Event</em></h2>
        </div>

        <div class="admin-form-card">
            <form method="POST" action="edit_event.php?event_id=<?= $event_id ?>">

                <div class="mb-3">
                    <label class="form-label admin-form-label">Event Name</label>
                    <input type="text" name="title" class="form-control admin-form-control"
                           value="<?= htmlspecialchars($event['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Category</label>
                    <input type="text" name="category" class="form-control admin-form-control"
                           value="<?= htmlspecialchars($event['category'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Date</label>
                    <input type="date" name="event_date" class="form-control admin-form-control"
                           value="<?= htmlspecialchars($event['event_date']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Time</label>
                    <input type="time" name="event_time" class="form-control admin-form-control"
                           value="<?= htmlspecialchars($event['event_time'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Venue</label>
                    <select name="venue_id" class="form-control admin-form-control">
                        <option value="">-- Select Venue --</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?= $venue['venue_id'] ?>"
                                <?= $event['venue_id'] == $venue['venue_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($venue['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label admin-form-label">Description</label>
                    <textarea name="description" rows="5"
                              class="form-control admin-form-control"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>

                <!-- Event Images -->
                <div class="mb-4">
                    <label class="form-label admin-form-label">Event Images</label>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Banner Image URL</label>
                        <input type="url" name="img_banner" class="form-control admin-form-control"
                               value="<?= htmlspecialchars($existing_imgs['banner'] ?? '') ?>"
                               placeholder="https://example.com/banner.jpg">
                        <small style="color:var(--pulse-muted);">Main blurred background on the event detail page.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Poster / Thumbnail URL</label>
                        <input type="url" name="img_poster" class="form-control admin-form-control"
                               value="<?= htmlspecialchars($existing_imgs['poster'] ?? '') ?>"
                               placeholder="https://example.com/poster.jpg">
                        <small style="color:var(--pulse-muted);">Portrait image shown on the event card and detail page.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label admin-form-label" style="font-size:0.78rem;">Seat Map URL</label>
                        <input type="url" name="img_seatmap" class="form-control admin-form-control"
                               value="<?= htmlspecialchars($existing_imgs['seatmap'] ?? '') ?>"
                               placeholder="https://example.com/seatmap.jpg">
                        <small style="color:var(--pulse-muted);">Optional. Enables "View Seat Map" button on event page.</small>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                           <?= $event['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label admin-form-label" for="is_active">Active (visible to public)</label>
                </div>

                <!-- Existing Sections -->
                <div class="mb-3">
                    <label class="form-label admin-form-label">Seat Sections & Pricing</label>
                    <?php if ($sections): ?>
                        <?php foreach ($sections as $sec): ?>
                            <div class="d-flex gap-2 mb-2 align-items-center">
                                <input type="hidden" name="section_id[]" value="<?= $sec['section_id'] ?>">
                                <input type="text" name="section_label[]" class="form-control admin-form-control"
                                       value="<?= htmlspecialchars($sec['label']) ?>" placeholder="Section" style="flex:2;">
                                <input type="number" step="0.01" min="0" name="section_price[]"
                                       class="form-control admin-form-control" value="<?= $sec['price'] ?>"
                                       placeholder="Price (S$)" style="flex:1;">
                                <input type="number" min="0" name="section_seats[]"
                                       class="form-control admin-form-control" value="<?= $sec['total_seats'] ?>"
                                       placeholder="Seats" style="flex:1;">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--pulse-muted); font-size: 0.85rem;">No sections yet. Add one below.</p>
                    <?php endif; ?>
                </div>

                <!-- New Sections -->
                <div class="mb-3">
                    <label class="form-label admin-form-label">Add New Sections</label>
                    <div id="new-sections-wrapper"></div>
                    <button type="button" id="add-section" class="btn btn-outline-light btn-sm mt-2">+ Add Section</button>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="manage_events.php" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn-dark-solid">Update Event</button>
                </div>

            </form>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>

    <script>
        const newWrapper = document.getElementById('new-sections-wrapper');

        document.getElementById('add-section').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'd-flex gap-2 mb-2 align-items-center';
            row.innerHTML = `
                <input type="text" name="new_section_label[]" class="form-control admin-form-control" placeholder="Section (e.g. CAT 3)" style="flex:2;">
                <input type="number" step="0.01" min="0" name="new_section_price[]" class="form-control admin-form-control" placeholder="Price (S$)" style="flex:1;">
                <input type="number" min="0" name="new_section_seats[]" class="form-control admin-form-control" placeholder="Seats" style="flex:1;">
                <button type="button" class="btn btn-outline-danger btn-sm remove-section">✕</button>
            `;
            newWrapper.appendChild(row);
        });

        newWrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-section')) {
                e.target.closest('.d-flex').remove();
            }
        });
    </script>
</body>
</html>