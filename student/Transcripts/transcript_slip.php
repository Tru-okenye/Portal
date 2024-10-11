<?php
require_once __DIR__ . '/../../config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch student details from session
$admissionNumber = $_SESSION['student_admission_number'];

// Retrieve form data
$yearOfStudy = $_POST['yearOfStudy'] ?? '';

// Determine semester range based on Year of Study
$semesters = [];
if ($yearOfStudy == '1') {
    $semesters = [1, 2, 3]; // Year 1 covers semesters 1 to 3
} elseif ($yearOfStudy == '2') {
    $semesters = [4, 5, 6]; // Year 2 covers semesters 4 to 6
}

// Fetch student details including first and last names
$studentSql = "
    SELECT CourseName, IntakeName, YEAR(RegistrationDate) AS RegistrationYear, FirstName, LastName 
    FROM Students 
    WHERE AdmissionNumber = ?
";
$stmt = $conn->prepare($studentSql);
$stmt->bind_param("s", $admissionNumber);
$stmt->execute();
$result = $stmt->get_result();
$studentDetails = $result->fetch_assoc();

if (!$studentDetails) {
    echo "<h3>Student details not found.</h3>";
    exit;
}

$course = $studentDetails['CourseName'];
$intake = $studentDetails['IntakeName'];
$year = $studentDetails['RegistrationYear'];

// Get student's full name
$fullName = htmlspecialchars($studentDetails['FirstName'] . ' ' . $studentDetails['LastName']);

// Calculate academic year based on year of study
$academicYear = '';
if ($yearOfStudy == '1') {
    $academicYear = $year . '/' . ($year + 1); // Year 1: intake year / next year
} elseif ($yearOfStudy == '2') {
    $academicYear = ($year + 1) . '/' . ($year + 2); // Year 2: next year / following year
}

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

// Fetch all units for the course and semesters
$units = [];
$unitsSql = "SELECT UnitCode, UnitName FROM units WHERE CourseID = ? AND SemesterNumber IN (" . implode(",", $semesters) . ")";
if ($stmt = $conn->prepare($unitsSql)) {
    $stmt->bind_param("s", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    $stmt->close();
}

// Fetch the student's grades for all units in the selected semesters
$marksSql = "
    SELECT unit_code, total_marks, grade 
    FROM exam_marks 
    WHERE admission_number = ? 
    AND course_name = ? 
    AND semester IN (" . implode(",", $semesters) . ")
";
if ($stmt = $conn->prepare($marksSql)) {
    $stmt->bind_param("ss", $admissionNumber, $course);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentMarks = [];
    while ($row = $result->fetch_assoc()) {
        $studentMarks[$row['unit_code']] = $row;
    }
    $stmt->close();
}

// Check for supplementary exams and adjust grades if necessary
$generateTranscript = true; // Flag to track if transcript can be generated

foreach ($units as $unit) {
    $unitCode = $unit['UnitCode'];
    $grade = $studentMarks[$unitCode]['grade'] ?? 'E'; // Default to 'E' if no marks found

    if ($grade == 'E') {
        // Check if the student took a supplementary exam for this unit
        $suppSql = "
            SELECT grade, total_marks 
            FROM supplementary_exam_marks 
            WHERE admission_number = ? 
            AND course_name = ? 
            AND unit_code = ?
            ORDER BY attempt_number DESC 
            LIMIT 1
        ";
        if ($stmt = $conn->prepare($suppSql)) {
            $stmt->bind_param("sss", $admissionNumber, $course, $unitCode);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $suppResult = $result->fetch_assoc();
                $suppGrade = $suppResult['grade'];
                $suppTotalMarks = $suppResult['total_marks'];

                if ($suppTotalMarks >= 40) {
                    // Assign a "D" grade if supplementary was passed
                    $studentMarks[$unitCode]['grade'] = 'D';
                } else {
                    // If the supplementary exam was failed, don't generate the transcript
                    $generateTranscript = false;
                    break;
                }
            } else {
                // If no supplementary exam record is found, don't generate the transcript
                $generateTranscript = false;
                break;
            }
            $stmt->close();
        }
    }
}

// Check if there are no units to display and prevent empty transcript
if (empty($units) || !$generateTranscript) {
    echo "<h3>Transcript cannot be generated for $admissionNumber.</h3>";
    echo "<p>You are not eligible for a transcript.</p><hr>";
} else {
    // If we reach here, the student is eligible for a transcript
    echo '<div style="text-align: center;">'; // Center the following section
    echo "<h3><img src='assets/images/ikigai-college-logo-1.png' alt='Ikigai College Logo' style='width:150px;'><br>IKIGAI COLLEGE OF INTERIOR DESIGN</h3>";
    echo "<h4>ACADEMIC TRANSCRIPT</h4>";
    echo "</div>"; // End centering

    // Display student details (not centered)
    echo "<strong>Student Name:</strong> $fullName<br>"; // Display full name
    echo "<strong>Student Admission Number:</strong> $admissionNumber<br>";
    echo "<strong>Course:</strong> $course<br>";
    echo "<strong>Academic Year:</strong> $academicYear<br>"; // Use the calculated academic year
    echo "<strong>Year of Study:</strong> Year $yearOfStudy<br><br>";

    // Display all units in a single table
    echo "<table border='1' cellpadding='5' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr><th>Unit Code</th><th>Unit Name</th><th>Grade</th></tr>";

    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $unitName = $unit['UnitName'];
        $grade = $studentMarks[$unitCode]['grade'] ?? 'E'; // Default to 'E' if no marks found
        echo "<tr><td>$unitCode</td><td>$unitName</td><td>$grade</td></tr>";
    }

    echo "</table><br><br>";

    // Add the grading system and recommendations section
    echo "<h5>Grading System:</h5>";
    echo "<p>
        A = 70-100% (Distinction)<br>
        B = 60-69% (Credit)<br>
        C = 50-59% (Credit)<br>
        D = 40-49% (Pass)<br>
        E = 39% and below (Fail)
    </p>";

    echo "<h5>Recommendation:</h5>";
    echo "<p>[Recommendation goes here based on the student's performance]</p><br>";

    // Add a signed space and date issued
    echo "<p><strong>Signed:</strong> ___________________________</p>";
    echo "<p><strong>Date Issued:</strong> " . date("d-m-Y") . "</p>";
    echo "<hr>";
}
?>
