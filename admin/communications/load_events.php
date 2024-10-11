<?php
include '../../config/config.php';

header('Content-Type: application/json');

$query = "SELECT * FROM events";
$result = $conn->query($query);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start']
    ];
}

echo json_encode($events);
$conn->close();
?>
