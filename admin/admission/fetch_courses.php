<?php
include_once '../../config/config.php'; // Include the database connection file

header('Content-Type: application/json');

// Check if the category parameter is set
if (!isset($_GET['category']) || empty($_GET['category'])) {
    echo json_encode(['error' => 'Category not provided']);
    exit();
}

// Sanitize category input
$category = $conn->real_escape_string($_GET['category']);

// Fetch courses based on selected category
$sql = "SELECT CourseName FROM courses WHERE CategoryID = (SELECT CategoryID FROM categories WHERE CategoryName = '$category')";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Database query failed', 'message' => $conn->error]);
    exit();
}

// Build courses array
$courses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Return the courses as JSON
echo json_encode($courses);

$conn->close();
?>
