<?php
include_once '../../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['title'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$id = $conn->real_escape_string($data['id']);
$title = $conn->real_escape_string($data['title']);

$sql = "UPDATE events SET title='$title' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
