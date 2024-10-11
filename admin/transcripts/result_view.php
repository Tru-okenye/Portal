<?php
require_once __DIR__ . '/../../config/config.php';

// Retrieve form data
$categoryID = $_POST['category'] ?? '';
$course = $_POST['course'] ?? '';
$semester = $_POST['semester'] ?? '';
$year = $_POST['year'] ?? '';
$intake = $_POST['intake'] ?? '';

// Fetch course ID
$courseID = '';
$courseSql = "SELECT CourseID FROM courses WHERE CourseName = ?";
if ($stmt = $conn->prepare($courseSql)) {
    $stmt->bind_param("s", $course);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $courseID = $result->fetch_assoc()['CourseID'];
    }
    $stmt->close();
}

// Fetch all units for the course and semester
$unitsSql = "SELECT UnitCode, UnitName FROM units WHERE CourseID = ? AND SemesterNumber = ?";
$units = [];
if ($stmt = $conn->prepare($unitsSql)) {
    $stmt->bind_param("ss", $courseID, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    $stmt->close();
}

// Fetch all students in the selected course, year, intake, and semester
$studentsSql = "
    SELECT DISTINCT admission_number, student_name 
    FROM exam_marks 
    WHERE course_name = ? 
    AND semester = ?
    AND year = ?
    AND intake = ?
";
$students = [];
if ($stmt = $conn->prepare($studentsSql)) {
    $stmt->bind_param("ssss", $course, $semester, $year, $intake);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Check if no students found
if (empty($students)) {
    echo "<h3>No students found for the selected criteria.</h3>";
    exit; // Stop further processing
}

// Fetch category name based on category ID
$categoryName = '';
$categorySql = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
if ($stmt = $conn->prepare($categorySql)) {
    $stmt->bind_param("i", $categoryID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $categoryName = $result->fetch_assoc()['CategoryName'];
    }
    $stmt->close();
}

// Determine the year of study based on the category and semester
$yearOfStudy = 'Unknown';
$displaySemester = $semester; // Default display semester

if ($categoryName === 'Diploma') {
    if ($semester >= 1 && $semester <= 3) {
        $yearOfStudy = 'Year 1';
    } elseif ($semester >= 4 && $semester <= 6) {
        $yearOfStudy = 'Year 2';
        $displaySemester = $semester - 3; // Adjust semester display for Year 2
    }
} elseif ($categoryName === 'Certificate') {
    if ($semester >= 1 && $semester <= 3) {
        $yearOfStudy = 'Year 1';
    }
}

// Get the current year
$currentYear = date('Y');

// Calculate academic year based on the current year
$academicYear = $currentYear . '/' . ($currentYear + 1);
// Generate result slips for each student
foreach ($students as $student) {
    $admission_number = $student['admission_number'];
    $studentName = $student['student_name'];

    // Fetch the student's grades
    $marksSql = "
        SELECT unit_code, grade 
        FROM exam_marks 
        WHERE admission_number = ? 
        AND course_name = ? 
        AND semester = ?
    ";
    $marksData = [];
    if ($stmt = $conn->prepare($marksSql)) {
        $stmt->bind_param("sss", $admission_number, $course, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $marksData[$row['unit_code']] = $row['grade'];
        }
        $stmt->close();
    }

    // Display the result slip for the student
    echo '<div style="text-align: center;">'; 
    echo "<h3><img src='assets/images/ikigai-college-logo-1.png' alt='Ikigai College Logo' style='width:150px;'><br>IKIGAI COLLEGE OF INTERIOR DESIGN</h3>";
    echo "<h4>EXAMINATION RESULT SLIP</h4>";
    echo "</div>"; // End centering

    echo "<strong>Student Name:</strong> $studentName<br>";
    echo "<strong>Admission No:</strong> $admission_number<br>";
    echo "<strong>Course:</strong> $course<br>";
    echo "<strong>Academic Year:</strong> $academicYear<br>";
    echo "<strong>Year of Study:</strong> $yearOfStudy<br>";
    echo "<strong>Semester:</strong> $displaySemester<br><br>";


    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Unit Code</th><th>Unit Name</th><th>Grade</th></tr>";
    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $unitName = $unit['UnitName'];
        $grade = isset($marksData[$unitCode]) ? $marksData[$unitCode] : 'E'; // Assign 'E' if no grade is found
        echo "<tr><td>$unitCode</td><td>$unitName</td><td>$grade</td></tr>";
    }
    echo "</table>";

    // Signature and Date Section
    echo "<br><br><br>";
    echo "<table border='0' cellpadding='5'>";
    echo "<tr><td><strong>Signature:</strong> ______________________</td></tr>";
    echo "<tr><td><strong>Date:</strong> ______________________</td></tr>";
    echo "</table>";

    echo "<hr style='page-break-before: always;'>";
}

// Close database connection
$conn->close();
?>
