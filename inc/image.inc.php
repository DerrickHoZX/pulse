<?php
/**
 * PULSE Image Helper — compatible with PHP 7.4+
 */

function getEventImage($conn, $event_id, $type = 'banner') {
    $fallbacks = [
        'banner'  => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1400&q=80',
        'poster'  => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&q=80',
        'seatmap' => '',
    ];

    // Check table exists first
    $check = $conn->query("SHOW TABLES LIKE 'event_images'");
    if (!$check || $check->num_rows === 0) {
        return $fallbacks[$type] ?? '';
    }

    $stmt = $conn->prepare(
        "SELECT image_path FROM event_images
         WHERE event_id = ? AND image_type = ?
         ORDER BY sort_order ASC LIMIT 1"
    );
    if (!$stmt) return $fallbacks[$type] ?? '';
    $stmt->bind_param('is', $event_id, $type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? $row['image_path'] : ($fallbacks[$type] ?? '');
}

function getEventImages($conn, $event_id, $type = 'gallery') {
    $check = $conn->query("SHOW TABLES LIKE 'event_images'");
    if (!$check || $check->num_rows === 0) return [];

    $stmt = $conn->prepare(
        "SELECT image_id, image_path, alt_text, sort_order
         FROM event_images
         WHERE event_id = ? AND image_type = ?
         ORDER BY sort_order ASC"
    );
    if (!$stmt) return [];
    $stmt->bind_param('is', $event_id, $type);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllEventImages($conn, $event_id) {
    $result = ['banner' => '', 'poster' => '', 'seatmap' => ''];

    $stmt = $conn->prepare(
        "SELECT image_type, image_path
         FROM event_images
         WHERE event_id = ?
         ORDER BY image_type, sort_order ASC"
    );
    if (!$stmt) return $result;
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as $row) {
        if (isset($result[$row['image_type']]) && !$result[$row['image_type']]) {
            $result[$row['image_type']] = $row['image_path'];
        }
    }

    return $result;
}

function resolveImageSrc($path) {
    if (!$path) return '';
    // External URL — use as-is (PHP 7.4 compatible check)
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    // Local file
    return '/' . ltrim($path, '/');
}
?>