<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
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
        $units[] = $row; // Store units without grouping by semester
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

// Create new PDF document
$transcriptGenerated = false; // Flag to track if any transcript is generated
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Ikigai College');
$pdf->SetTitle('Academic Transcript');
$pdf->SetSubject('Transcript');
$pdf->SetKeywords('TCPDF, PDF, transcript');

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add logo
// Get the page width
$pageWidth = $pdf->getPageWidth();

// Define the width of the logo
$logoWidth = 50; // You can adjust this as needed

// Calculate the X position to center the image
$logoX = ($pageWidth - $logoWidth) / 2;

// Add the logo image, positioning it at the center
$imagePath = __DIR__ . '/../../assets/images/ikigai-college-logo-1.jpg';
$pdf->Image($imagePath, $logoX, 10, $logoWidth, '', '', '', '', false, 300, '', false, false, 0, false, false, false);

// Add the centered headings below the logo
$pdf->Ln(20); // Add some space below the image
$pdf->Cell(0, 10, 'IKIGAI COLLEGE OF INTERIOR DESIGN', 0, 1, 'C');
$pdf->Cell(0, 10, 'ACADEMIC TRANSCRIPT', 0, 1, 'C');
$pdf->Ln(10);


// Generate transcript for each student
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
                $stmt->bind_param("sss", $admission_number, $course, $unitCode);
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

    if (!$generateTranscript) {
        // Display a message for students who are not eligible for a transcript
        echo "<h3>Transcript cannot be generated for $studentName (Admission Number: $admission_number).</h3>";
        echo "<p>The student has failed a unit or supplementary exam and is not eligible for a transcript.</p><hr>";
        continue; // Skip to the next student
    }

    // Add student information to PDF
    $pdf->Cell(0, 10, "Student Name: $studentName", 0, 1);
    $pdf->Cell(0, 10, "Admission No: $admission_number", 0, 1);
    $pdf->Cell(0, 10, "Course: $course", 0, 1);
    $pdf->Cell(0, 10, "Academic Year: $academicYear", 0, 1);
    $pdf->Cell(0, 10, "Year of Study: $yearOfStudy", 0, 1);
    $pdf->Ln(5);

    // Add table of units and grades
    $html = '<table border="1" cellpadding="4">
        <thead>
            <tr>
                <th>Unit Code</th>
                <th>Unit Name</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($units as $unit) {
        $unitCode = $unit['UnitCode'];
        $unitName = $unit['UnitName'];
        $grade = isset($studentMarks[$unitCode]) ? $studentMarks[$unitCode]['grade'] : 'E';
        $html .= "<tr>
            <td>$unitCode</td>
            <td>$unitName</td>
            <td>$grade</td>
        </tr>";
    }

    $html .= '</tbody></table>';

    // Write HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    // Add grading system and recommendations section
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Grading System:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 10, "A = 70-100% (Distinction)\nB = 60-69% (Credit)\nC = 50-59% (Credit)\nD = 40-49% (Pass)\nE = 39% and below (Fail)");

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Recommendation:', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 10, "[Recommendation goes here based on the student's performance]");
    // Add signature space
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Signature: ______________________', 0, 1);
    $pdf->Cell(0, 10, 'Date: ______________________', 0, 1);

      // Mark that a transcript was generated
      $transcriptGenerated = true;
    // Add a new page for the next student
    $pdf->AddPage();
}

// Save the transcript as a PDF only if a transcript was generated
if ($transcriptGenerated) {
    $pdfFilePath = __DIR__ . '/../../uploads/transcripts/transcript_' . time() . '.pdf';
    $pdf->Output($pdfFilePath, 'F');

    if (file_exists($pdfFilePath)) {
        ob_end_flush(); // Flush the output buffer first
        echo "<h3>Transcript created successfully!</h3>";
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/transcript_page&file=' . urlencode(basename($pdfFilePath)) . '">';
    } else {
        echo "PDF creation failed.";
        exit;
    }
} else {
    // No transcript was generated for any student
    echo "<h3>No transcript could be generated for the selected criteria.</h3>";
    exit;
}

// Close the database connection
$conn->close();
?>
