<?php
include_once __DIR__ . '/../../config/config.php';

$courseName = $_POST['courseName'] ?? '';
$semesterNumber = $_POST['semesterNumber'] ?? '';

$units = [];
if ($courseName && $semesterNumber) {
    $unitSql = "SELECT * FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?) AND SemesterNumber = ?";
    $unitStmt = $conn->prepare($unitSql);
    $unitStmt->bind_param("si", $courseName, $semesterNumber);
    $unitStmt->execute();
    $unitResult = $unitStmt->get_result();
    if ($unitResult->num_rows > 0) {
        while ($row = $unitResult->fetch_assoc()) {
            $units[] = ['value' => $row['UnitCode'], 'text' => $row['UnitName']];
        }
    }
}

echo json_encode($units);
?>


