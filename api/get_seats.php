<?php
header('Content-Type: application/json');
require_once '../inc/db.inc.php';

$section_id = intval($_GET['section_id'] ?? 0);
if (!$section_id) { echo json_encode([]); exit; }

$conn = getDBConnection();
$stmt = $conn->prepare(
    "SELECT seat_id, row_label, seat_num, status
     FROM seats
     WHERE section_id = ?
     ORDER BY row_label, seat_num"
);
$stmt->bind_param('i', $section_id);
$stmt->execute();
$seats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();

echo json_encode($seats);
?>
