<?php
include_once __DIR__ . '/../../config/config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and ensure they're properly cast to numbers
    $categoryId = $_POST['category'] ?? '';
    $courseName = $_POST['course'] ?? '';
    $year = $_POST['year'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $unitCode = $_POST['unit'] ?? ''; // Ensure this is populated from the form
    $admissionNumber = $_POST['student_id'] ?? '';
    $studentName = $_POST['student_name'] ?? '';
    $catMarks = isset($_POST['cat_marks']) ? (float)$_POST['cat_marks'] : 0;
    $examMarks = isset($_POST['exam_marks']) ? (float)$_POST['exam_marks'] : 0;
    $intake = $_POST['intake'] ?? '';

    // Fetch category name based on ID
    $categorySql = "SELECT CategoryName FROM Categories WHERE CategoryID = ?";
    if ($categoryStmt = $conn->prepare($categorySql)) {
        $categoryStmt->bind_param("i", $categoryId);
        $categoryStmt->execute();
        $categoryStmt->bind_result($categoryName);
        $categoryStmt->fetch();
        $categoryStmt->close();
    }

    // Fetch unit name based on unit code
    $unitSql = "SELECT UnitName FROM Units WHERE UnitCode = ?";
    if ($unitStmt = $conn->prepare($unitSql)) {
        $unitStmt->bind_param("s", $unitCode);
        $unitStmt->execute();
        $unitStmt->bind_result($unitName);
        $unitStmt->fetch();
        $unitStmt->close();
    }

    // Debugging: Check if unit code and unit name are retrieved correctly
    if (empty($unitCode) || empty($unitName)) {
        echo "<p style='color: red;'>Error: Unit code or unit name not found.</p>";
        exit;
    }

    // Process each student
    foreach ($_POST['student_id'] as $index => $admissionNumber) {
        $studentName = $_POST['student_name'][$index];
        $catMarks = isset($_POST['cat_marks'][$index]) ? (float)$_POST['cat_marks'][$index] : 0;
        $examMarks = isset($_POST['exam_marks'][$index]) ? (float)$_POST['exam_marks'][$index] : 0;

        // Check for existing records
        $checkSql = "SELECT * FROM exam_marks WHERE admission_number = ? AND unit_code = ? AND semester = ? AND year = ? AND intake = ?";
        if ($checkStmt = $conn->prepare($checkSql)) {
            $checkStmt->bind_param("sssss", $admissionNumber, $unitCode, $semester, $year, $intake);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                echo "<p style='color: red;'>Duplicate entry detected for $studentName ($admissionNumber) in $unitCode for $semester semester, year $year, intake $intake.</p>";
            } else {
                // Calculate total marks and assign grade
                $totalMarks = $catMarks + $examMarks;
                // $totalMarks = round($totalMarks, 2);

                // Assign grade and class based on total marks
                $grade = '';
                $class = '';
                if ($totalMarks >= 70) {
                    $grade = 'A';
                    $class = 'Distinction';
                } elseif ($totalMarks >= 60) {
                    $grade = 'B';
                    $class = 'Credit';
                } elseif ($totalMarks >= 50) {
                    $grade = 'C';
                    $class = 'Credit';
                } elseif ($totalMarks >= 40) {
                    $grade = 'D';
                    $class = 'Pass';
                } else {
                    $grade = 'E';
                    $class = 'Fail';
                }

                // Insert the record into the database
                $sql = "INSERT INTO exam_marks (category_name, course_name, year, semester, unit_code, unit_name, admission_number, student_name, cat_marks, exam_marks, total_marks, grade, class, intake)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param(
                        'ssissssssddsss',
                        $categoryName,
                        $courseName,
                        $year,
                        $semester,
                        $unitCode,
                        $unitName,
                        $admissionNumber,
                        $studentName,
                        $catMarks,
                        $examMarks,
                        $totalMarks,
                        $grade,
                        $class,
                        $intake
                    );

                    // Execute the statement
                    if ($stmt->execute()) {
                        echo "<p style='color: green;'>Exam marks for $studentName have been submitted successfully!</p>";
                    } else {
                        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
                    }

                    $stmt->close();
                } else {
                    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
                }
            }

            $checkStmt->close();
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }

    $conn->close();
}
?>
