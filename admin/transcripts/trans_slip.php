<?php
require_once __DIR__ . '/../../config/config.php';
ob_start();

// Retrieve form data
$categoryID = $_POST['category'] ?? '';
$course = $_POST['course'] ?? '';
$yearOfStudy = $_POST['yearOfStudy'] ?? '';
$year = $_POST['year'] ?? ''; // Intake year
$intake = $_POST['intake'] ?? '';

// Determine semester range based on Year of Study
$semesters = [];
if ($yearOfStudy == '1') {
    $semesters = [1, 2, 3]; // Year 1 covers semesters 1 to 3
} elseif ($yearOfStudy == '2') {
    $semesters = [4, 5, 6]; // Year 2 covers semesters 4 to 6
}

// Get the current year
$currentYear = date('Y');

// Calculate academic year based on the current year
$academicYear = $currentYear . '/' . ($currentYear + 1);

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
$unitsSql = "SELECT UnitCode, UnitName, SemesterNumber FROM units WHERE CourseID = ? AND SemesterNumber IN (" . implode(",", $semesters) . ")";
if ($stmt = $conn->prepare($unitsSql)) {
    $stmt->bind_param("s", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    $stmt->close();
}

// Fetch all students for the selected course, intake, and year
$studentsSql = "
    SELECT DISTINCT admission_number, student_name 
    FROM exam_marks 
    WHERE course_name = ? 
    AND year = ?
    AND intake = ?
";
$students = [];
if ($stmt = $conn->prepare($studentsSql)) {
    $stmt->bind_param("sss", $course, $year, $intake);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// If no students are found, display a message
if (empty($students)) {
    echo "<h3>No students found for the selected criteria.</h3>";
    exit; // Stop further processing
}

// Display transcript for each student
foreach ($students as $student) {
    $admission_number = $student['admission_number'];
    $studentName = $student['student_name'];

    // Fetch student's grades for all units in the selected semesters
    $marksSql = "
        SELECT unit_code, total_marks, grade 
        FROM exam_marks 
        WHERE admission_number = ? 
        AND course_name = ? 
        AND semester IN (" . implode(",", $semesters) . ")
    ";
    if ($stmt = $conn->prepare($marksSql)) {
        $stmt->bind_param("ss", $admission_number, $course);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentMarks = [];
        while ($row = $result->fetch_assoc()) {
            $studentMarks[$row['unit_code']] = $row;
        }
        $stmt->close();
    }

    // Check for supplementary exams and adjust grades if necessary
    $generateTranscript = true;

    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $grade = $studentMarks[$unitCode]['grade'] ?? 'E';

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
                $stmt->bind_param("sss", $admission_number, $course, $unitCode);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $suppResult = $result->fetch_assoc();
                    $suppGrade = $suppResult['grade'];
                    $suppTotalMarks = $suppResult['total_marks'];

                    if ($suppTotalMarks >= 40) {
                        $studentMarks[$unitCode]['grade'] = 'D';
                    } else {
                        $generateTranscript = false;
                        break;
                    }
                } else {
                    $generateTranscript = false;
                    break;
                }
                $stmt->close();
            }
        }
    }

    if (!$generateTranscript) {
        echo "<h3>Transcript cannot be generated for $studentName (Admission Number: $admission_number).</h3>";
        echo "<p>The student has failed a unit or supplementary exam and is not eligible for a transcript.</p><hr>";
        continue;
    }

    // Display student transcript details with college logo and header
    echo '<div style="text-align: center;">'; // Center the following section
    echo "<h3><img src='assets/images/ikigai-college-logo-1.png' alt='Ikigai College Logo' style='width:150px;'><br>IKIGAI COLLEGE OF INTERIOR DESIGN</h3>";
    echo "<h4>ACADEMIC TRANSCRIPT</h4>";
    echo "</div>"; // End centering

    echo "<h3>Student Name: $studentName</h3>";
    echo "<p>Admission No: $admission_number</p>";
    echo "<p>Course: $course</p>";
    echo "<p>Academic Year: $academicYear</p>";
    echo "<p>Year of Study: $yearOfStudy</p>";


    // Display units and grades
    echo "<table border='1' cellpadding='4'>
        <thead>
            <tr>
                <th>Unit Code</th>
                <th>Unit Name</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>";
    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $unitName = $unit['UnitName'];
        $grade = isset($studentMarks[$unitCode]) ? $studentMarks[$unitCode]['grade'] : 'E';
        echo "<tr>
            <td>$unitCode</td>
            <td>$unitName</td>
            <td>$grade</td>
        </tr>";
    }
    echo "</tbody></table>";
        // Display the grade scale
        echo '<p><strong>Grade Scale:</strong></p>';
        echo '<p>A = 70-100% (Distinction)<br>B = 60-69% (Credit)<br>C = 50-59% (Credit)<br>D = 40-49% (Pass)<br>E = 39% and below (Fail)</p>';
        echo "<br>";
    
        // Display the recommendation section
        echo '<p><strong>Recommendation:</strong></p>';
        echo '<p>[Recommendation goes here based on the student\'s performance]</p>';
        echo "<br>";
    
        // Add signature space
        echo "<p>Signature: ______________________</p>";
        echo "<p>Date: ______________________</p>";
    
    echo "<hr>";
}

// Close the database connection
$conn->close();
?>
