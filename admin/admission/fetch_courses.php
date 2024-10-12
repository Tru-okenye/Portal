<?php
// Include the database connection file
include_once '../../config/config.php'; 

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the content type header for JSON response
header('Content-Type: application/json');

// Sanitize the input category
$category = $conn->real_escape_string($_GET['category']);

// Fetch courses based on selected category
$result = $conn->query("SELECT CourseName FROM courses WHERE CategoryID = (SELECT CategoryID FROM categories WHERE CategoryName = '$category')");

if (!$result) {
    // Return error message as JSON
    echo json_encode(['error' => 'Query error: ' . $conn->error]);
    exit();
}

$courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Output the courses as JSON
echo json_encode($courses);

// Close the database connection
$conn->close();
?>
