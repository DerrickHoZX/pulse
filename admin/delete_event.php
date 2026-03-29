<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    header("Location: manage_events.php");
    exit;
}

$conn = getDBConnection();

// Delete related seat sections first (foreign key safety)
$stmt = $conn->prepare("DELETE FROM seat_sections WHERE event_id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$stmt->close();

// Delete related event images
$stmt = $conn->prepare("DELETE FROM event_images WHERE event_id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$stmt->close();

// Delete the event itself
$stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: manage_events.php?deleted=1");
exit;
?>