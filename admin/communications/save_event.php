<?php
include_once '../../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['title']) || !isset($data['start'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$title = $conn->real_escape_string($data['title']);
$start = $conn->real_escape_string($data['start']);

$sql = "INSERT INTO events (title, start) VALUES ('$title', '$start')";

if ($conn->query($sql) === TRUE) {
    $id = $conn->insert_id; // Get the ID of the newly inserted event
    echo json_encode(['success' => true, 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
