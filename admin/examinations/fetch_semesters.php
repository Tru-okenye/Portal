<?php
include_once __DIR__ . '/../../config/config.php';

$courseName = $_POST['courseName'] ?? '';

$semesters = [];
if ($courseName) {
    $semesterSql = "SELECT DISTINCT SemesterNumber FROM Units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?)";
    $semesterStmt = $conn->prepare($semesterSql);
    $semesterStmt->bind_param("s", $courseName);
    $semesterStmt->execute();
    $semesterResult = $semesterStmt->get_result();
    if ($semesterResult->num_rows > 0) {
        while ($row = $semesterResult->fetch_assoc()) {
            $semesters[] = ['value' => $row['SemesterNumber'], 'text' => 'Semester ' . $row['SemesterNumber']];
        }
    }
}

echo json_encode($semesters);
?>
