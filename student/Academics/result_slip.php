<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the student is logged in
if (!isset($_SESSION['student_admission_number'])) {
    echo "You must be logged in to view your results.";
    exit;
}

$loggedInAdmissionNumber = $_SESSION['student_admission_number']; // Get the logged-in student's admission number

// Retrieve form data
$semester = $_POST['semester'] ?? '';

// Fetch the logged-in student's details including name, category, course, intake, and registration year
$studentDetailsSql = "
    SELECT CONCAT(FirstName, ' ', LastName) AS StudentName, 
           CategoryName, 
           CourseName, 
           IntakeName, 
           YEAR(RegistrationDate) AS RegistrationYear 
    FROM Students 
    WHERE AdmissionNumber = ?
";
$stmt = $conn->prepare($studentDetailsSql);
$stmt->bind_param("s", $loggedInAdmissionNumber);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();

$categoryName = $studentDetails['CategoryName'];
$course = $studentDetails['CourseName'];
$intake = $studentDetails['IntakeName'];
$year = $studentDetails['RegistrationYear'];

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

// Fetch the student's exam marks and count the number of units with grades
$marksCountSql = "
    SELECT COUNT(*) AS units_with_marks
    FROM exam_marks 
    WHERE admission_number = ? 
    AND course_name = ? 
    AND semester = ?
    AND grade IS NOT NULL
";
$marksCount = 0;
if ($stmt = $conn->prepare($marksCountSql)) {
    $stmt->bind_param("sss", $loggedInAdmissionNumber, $course, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $marksCount = $result->fetch_assoc()['units_with_marks'];
    $stmt->close();
}

// Only display the result slip if the student has marks for at least 2 units
if ($marksCount >= 2) {
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

    // Fetch the student's exam marks
    $marksSql = "
        SELECT unit_code, grade 
        FROM exam_marks 
        WHERE admission_number = ? 
        AND course_name = ? 
        AND semester = ?
    ";
    $marksData = [];
    if ($stmt = $conn->prepare($marksSql)) {
        $stmt->bind_param("sss", $loggedInAdmissionNumber, $course, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $marksData[$row['unit_code']] = $row['grade'];
        }
        $stmt->close();
    }

    // Determine the academic year
    $academicYear = $year . '/' . ($year + 1); // Format as "YYYY/YYYY"

    // Determine year of study and display semester
    $yearOfStudy = ($categoryName === 'Diploma' && $semester >= 4) ? 'Year 2' : 'Year 1';
    $displaySemester = ($yearOfStudy === 'Year 2') ? $semester - 3 : $semester; // Adjust semester for Year 2

    // Display the result slip for the logged-in student
    $studentName = $studentDetails['StudentName'];
    $admission_number = $loggedInAdmissionNumber;

    // Centered section
    echo "<div style='text-align: center;'>";
    echo "<h3><img src='assets/images/ikigai-college-logo-1.png' alt='Ikigai College Logo' style='width:150px;'><br>IKIGAI COLLEGE OF INTERIOR DESIGN</h3>";
    echo "<h4>EXAMINATION RESULT SLIP</h4>";
    echo "</div>"; // Close centered section

    echo "<strong>Student Name:</strong> $studentName<br>";
    echo "<strong>Admission No:</strong> $admission_number<br>";
    echo "<strong>Course:</strong> $course<br>";
    echo "<strong>Academic Year:</strong> $academicYear<br>";
    echo "<strong>Year of Study:</strong> $yearOfStudy<br>";
    echo "<strong>Semester:</strong> Semester $displaySemester<br><br>";

    // Table for units and grades
    echo "<table border='1' cellpadding='5' style='width: 100%;'>";
    echo "<tr><th>Unit Code</th><th>Unit Name</th><th>Grade</th></tr>";
    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $unitName = $unit['UnitName'];
        $grade = isset($marksData[$unitCode]) ? $marksData[$unitCode] : 'E'; // Default to 'E' if no grade found
        echo "<tr><td>$unitCode</td><td>$unitName</td><td>$grade</td></tr>";
    }
    echo "</table>";

    // Signature and Date Section
    echo "<br><br><br>";
    echo "<table border='0' cellpadding='5' style='width: 100%;'>";
    echo "<tr><td><strong>Signature:</strong> ______________________</td></tr>";
    echo "<tr><td><strong>Date:</strong> ______________________</td></tr>";
    echo "</table>";
} else {
    // If student has marks for less than 2 units, display a message
    echo "<p>You do not have sufficient exam marks for this semester. At least 2 units must have been graded to view your result slip.</p>";
}

$conn->close();
?>
