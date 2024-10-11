<?php
include_once '../../config/config.php'; // Include the database connection file

$category = $conn->real_escape_string($_GET['category']);

// Fetch courses based on selected category
$result = $conn->query("SELECT CourseName FROM Courses WHERE CategoryID = (SELECT CategoryID FROM Categories WHERE CategoryName = '$category')");

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
