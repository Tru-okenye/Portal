<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include_once '../../config/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize the input category
    $category = $conn->real_escape_string($_POST['category']);

    // Prepare the SQL query to fetch courses based on the selected category
    $sql = "SELECT CourseName FROM courses WHERE CategoryID = (SELECT CategoryID FROM categories WHERE CategoryName = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare an array for the courses
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = ['CourseName' => htmlspecialchars($row['CourseName'])];
    }

    // Output the JSON encoded array
    echo json_encode($courses);

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    exit();
}
?>
