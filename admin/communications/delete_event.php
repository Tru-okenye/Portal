<?php
include_once '../../config/config.php';

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

// Validate the data
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Escape the input data
$id = $conn->real_escape_string($data['id']);

// Prepare the SQL statement
$sql = "DELETE FROM events WHERE id='$id'";

// Execute the query and check for errors
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

// Close the database connection
$conn->close();
?>
