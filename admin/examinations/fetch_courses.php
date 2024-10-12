<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = $_POST['categoryId'];

    $sql = "SELECT CourseID, CourseName FROM courses WHERE CategoryID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            'value' => $row['CourseName'],
            'text' => $row['CourseName']
        ];
    }

    echo json_encode($courses);
}
?>
