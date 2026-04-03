<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";
$error = '';

function uploadEventImage(array $file, string $type): string
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new Exception("Failed to upload {$type} image.");
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        throw new Exception("Invalid {$type} image format. Use JPG, JPEG, PNG or WEBP.");
    }

    $uploadDir = __DIR__ . '/../uploads/events/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = $type . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("Failed to save {$type} image.");
    }

    return 'uploads/events/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    $conn->begin_transaction();

    try {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $venue_id = intval($_POST['venue_id'] ?? 0) ?: null;
        $description = trim($_POST['description'] ?? '');
        $img_banner = uploadEventImage($_FILES['img_banner'] ?? [], 'banner');
        $img_poster = uploadEventImage($_FILES['img_poster'] ?? [], 'poster');
        $img_seatmap = uploadEventImage($_FILES['img_seatmap'] ?? [], 'seatmap');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!$title || !$event_date) {
            throw new Exception('Event name and date are required.');
        }

        $stmt = $conn->prepare("INSERT INTO events (title, category, event_date, event_time, venue_id, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception($conn->error);
        }

        $stmt->bind_param('ssssisi', $title, $category, $event_date, $event_time, $venue_id, $description, $is_active);
        $stmt->execute();
        $new_event_id = $conn->insert_id;
        $stmt->close();

        $img_stmt = $conn->prepare("INSERT INTO event_images (event_id, image_type, image_path) VALUES (?, ?, ?)");
        if (!$img_stmt) {
            throw new Exception($conn->error);
        }

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

        $labels = $_POST['section_label'] ?? [];
        $prices = $_POST['section_price'] ?? [];
        $seats = $_POST['section_seats'] ?? [];

        $sec = $conn->prepare("INSERT INTO seat_sections (event_id, label, price, total_seats) VALUES (?, ?, ?, ?)");
        $seat = $conn->prepare("INSERT INTO seats (section_id, row_label, seat_num, status) VALUES (?, ?, ?, 'available')");

        if (!$sec || !$seat) {
            throw new Exception($conn->error);
        }

        foreach ($labels as $i => $label) {
            $label = trim($label);
            $price = floatval($prices[$i] ?? 0);
            $totalSeats = intval($seats[$i] ?? 0);

            if (!$label) {
                continue;
            }

            $sec->bind_param('isdi', $new_event_id, $label, $price, $totalSeats);
            $sec->execute();
            $section_id = $conn->insert_id;

            $seatsPerRow = 10;
            $rows = ceil($totalSeats / $seatsPerRow);
            $seatCount = 0;

            for ($r = 0; $r < $rows; $r++) {
                if ($r < 26) {
                    $rowLabel = chr(65 + $r);
                } else {
                    $rowLabel = chr(65 + intval(($r - 26) / 26)) . chr(65 + (($r - 26) % 26));
                }
                for ($s = 1; $s <= $seatsPerRow; $s++) {
                    if ($seatCount >= $totalSeats) {
                        break;
                    }
                    $seat->bind_param('isi', $section_id, $rowLabel, $s);
                    $seat->execute();
                    $seatCount++;
                }
            }
        }

        $sec->close();
        $seat->close();

        $conn->commit();
        $conn->close();

        header("Location: manage_events.php?added=1");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        $error = $e->getMessage();
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

    <style>
        .btn-outline-danger.remove-section {
            color: #b02a37;
            border-color: #b02a37;
        }

        .btn-outline-danger.remove-section:hover,
        .btn-outline-danger.remove-section:focus {
            color: #fff;
            background-color: #b02a37;
            border-color: #b02a37;
        }
    </style>
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
            <form method="POST" enctype="multipart/form-data" action="add_event.php">

                <div class="mb-3">
                    <label for="title" class="form-label admin-form-label">Event Name</label>
                    <input type="text" id="title" name="title" class="form-control admin-form-control" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label admin-form-label">Category</label>
                    <input type="text" id="category" name="category" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label for="event_date" class="form-label admin-form-label">Date</label>
                    <input type="date" id="event_date" name="event_date" class="form-control admin-form-control"
                        required>
                </div>

                <div class="mb-3">
                    <label for="event_time" class="form-label admin-form-label">Time</label>
                    <input type="time" id="event_time" name="event_time" class="form-control admin-form-control">
                </div>

                <div class="mb-3">
                    <label for="venue_id" class="form-label admin-form-label">Venue</label>
                    <select id="venue_id" name="venue_id" class="form-control admin-form-control">
                        <option value="">-- Select Venue --</option>
                        <?php foreach ($venues as $venue): ?>
                            <option value="<?= $venue['venue_id'] ?>">
                                <?= htmlspecialchars($venue['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label admin-form-label">Description</label>
                    <textarea id="description" name="description" rows="5"
                        class="form-control admin-form-control"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label admin-form-label">Event Images</label>

                    <div class="mb-2">
                        <label for="img_banner" class="form-label admin-form-label" style="font-size:0.78rem;">Banner
                            Image</label>
                        <input type="file" id="img_banner" name="img_banner" accept="image/*"
                            class="form-control admin-form-control">
                        <small style="color:var(--pulse-muted);">Main blurred background on the event detail
                            page.</small>
                    </div>

                    <div class="mb-2">
                        <label for="img_poster" class="form-label admin-form-label" style="font-size:0.78rem;">Poster /
                            Thumbnail Image</label>
                        <input type="file" id="img_poster" name="img_poster" accept="image/*"
                            class="form-control admin-form-control">
                        <small style="color:var(--pulse-muted);">Portrait image shown on the event card and detail
                            page.</small>
                    </div>

                    <div class="mb-2">
                        <label for="img_seatmap" class="form-label admin-form-label" style="font-size:0.78rem;">Seat Map
                            Image</label>
                        <input type="file" id="img_seatmap" name="img_seatmap" accept="image/*"
                            class="form-control admin-form-control">
                        <small style="color:var(--pulse-muted);">Optional. Enables "View Seat Map" button on event
                            page.</small>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label admin-form-label" for="is_active">Active (visible to public)</label>
                </div>

                <div class="mb-4">
                    <label class="form-label admin-form-label">Seat Categories & Pricing</label>
                    <div id="sections-wrapper">
                        <div class="section-row d-flex gap-2 mb-2 align-items-center">
                            <div style="flex:2;">
                                <label class="visually-hidden" for="section_label_0">Seat category label</label>
                                <input type="text" id="section_label_0" name="section_label[]"
                                    class="form-control admin-form-control" placeholder="e.g. CAT 1 - Floor">
                            </div>

                            <div style="flex:1;">
                                <label class="visually-hidden" for="section_price_0">Seat category price</label>
                                <input type="number" id="section_price_0" step="0.01" min="0" name="section_price[]"
                                    class="form-control admin-form-control" placeholder="Price (S$)">
                            </div>

                            <div style="flex:1;">
                                <label class="visually-hidden" for="section_seats_0">Number of seats</label>
                                <input type="number" id="section_seats_0" min="0" name="section_seats[]"
                                    class="form-control admin-form-control" placeholder="No. of Seats">
                            </div>

                            <button type="button" class="btn btn-danger btn-sm remove-section">
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
        let sectionIndex = 1;

        document.getElementById('add-section').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'section-row d-flex gap-2 mb-2 align-items-center';
            row.innerHTML = `
                <div style="flex:2;">
                    <label class="visually-hidden" for="section_label_${sectionIndex}">Seat category label</label>
                    <input type="text" id="section_label_${sectionIndex}" name="section_label[]" class="form-control admin-form-control" placeholder="e.g. CAT 2 - Lower Bowl">
                </div>

                <div style="flex:1;">
                    <label class="visually-hidden" for="section_price_${sectionIndex}">Seat category price</label>
                    <input type="number" id="section_price_${sectionIndex}" step="0.01" min="0" name="section_price[]" class="form-control admin-form-control" placeholder="Price (S$)">
                </div>

                <div style="flex:1;">
                    <label class="visually-hidden" for="section_seats_${sectionIndex}">Number of seats</label>
                    <input type="number" id="section_seats_${sectionIndex}" min="0" name="section_seats[]" class="form-control admin-form-control" placeholder="No. of Seats">
                </div>

                <button type="button" class="btn btn-outline-danger btn-sm remove-section" style="white-space:nowrap;">✕ Remove</button>
            `;
            wrapper.appendChild(row);
            sectionIndex++;
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