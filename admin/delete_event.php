<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    header("Location: manage_events.php");
    exit;
}

$conn = getDBConnection();
$conn->begin_transaction();

try {
    // 1. Delete seats (child of seat_sections)
    $stmt = $conn->prepare("
        DELETE s FROM seats s
        JOIN seat_sections ss ON s.section_id = ss.section_id
        WHERE ss.event_id = ?
    ");
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute())
        throw new Exception($stmt->error);
    $stmt->close();

    // 2. Delete seat_sections
    $stmt = $conn->prepare("DELETE FROM seat_sections WHERE event_id = ?");
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute())
        throw new Exception($stmt->error);
    $stmt->close();

    // 3. Delete event_images
    $stmt = $conn->prepare("DELETE FROM event_images WHERE event_id = ?");
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute())
        throw new Exception($stmt->error);
    $stmt->close();

    // 4. Delete bookings
    $stmt = $conn->prepare("DELETE FROM bookings WHERE event_id = ?");
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute())
        throw new Exception($stmt->error);
    $stmt->close();

    // 5. Delete event
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    if (!$stmt)
        throw new Exception($conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute())
        throw new Exception($stmt->error);
    $stmt->close();

    $conn->commit();
    $conn->close();

    header("Location: manage_events.php?deleted=1");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("Delete failed: " . $e->getMessage());
}
?>