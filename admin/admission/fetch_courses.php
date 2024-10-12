<?php
include_once '../../config/config.php'; // Include the database connection file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$category = $conn->real_escape_string($_GET['category']);

// Fetch courses based on selected category
$result = $conn->query("SELECT CourseName FROM courses WHERE CategoryID = (SELECT CategoryID FROM categories WHERE CategoryName = '$category')");

if (!$result) {
    echo json_encode(['error' => 'Query error: ' . $conn->error]); // Return error message as JSON
    exit();
}

$courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($courses);

$conn->close();
?>
