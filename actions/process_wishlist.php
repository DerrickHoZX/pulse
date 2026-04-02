<?php
session_start();
header('Content-Type: application/json');
require_once '../inc/db.inc.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$user_id  = intval($_SESSION['user_id']);
$event_id = intval($_POST['event_id'] ?? 0);
$action   = $_POST['action'] ?? '';

if (!$event_id || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$conn = getDBConnection();

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'saved' => true]);
} else {
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'saved' => false]);
}

$conn->close();
?>