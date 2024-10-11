<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

ob_start();

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

if (empty($students)) {
    echo "<h3>No students found for the selected criteria.</h3>";
    exit;
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
$displaySemester = $semester;

if ($categoryName === 'Diploma') {
    if ($semester >= 1 && $semester <= 3) {
        $yearOfStudy = 'Year 1';
    } elseif ($semester >= 4 && $semester <= 6) {
        $yearOfStudy = 'Year 2';
        $displaySemester = $semester - 3;
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

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your School');
$pdf->SetTitle('Examination Result Slips');
$pdf->SetSubject('Result Slips');
$pdf->SetKeywords('TCPDF, PDF, result, slips');

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set background color (e.g., light blue)
$pdf->SetFillColor(173, 216, 230); // RGB for light blue
$pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F'); // Fill the page with color

// Set font
$pdf->SetFont('helvetica', '', 12);

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
$pdf->Cell(0, 10, 'EXAMINATION RESULT SLIPS', 0, 1, 'C');
$pdf->Ln(10);

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

    // Add student information to PDF
    $pdf->Cell(0, 10, "Student Name: $studentName", 0, 1);
    $pdf->Cell(0, 10, "Admission No: $admission_number", 0, 1);
    $pdf->Cell(0, 10, "Course: $course", 0, 1);
    $pdf->Cell(0, 10, "Academic Year: $academicYear", 0, 1);
    $pdf->Cell(0, 10, "Year of Study: $yearOfStudy", 0, 1);
    $pdf->Cell(0, 10, "Semester: $displaySemester", 0, 1);
    $pdf->Ln(5);

    // Add table header
    $html = '
    <table border="1" cellpadding="4">
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
        $grade = isset($marksData[$unitCode]) ? $marksData[$unitCode] : 'E';
        $html .= "<tr>
            <td>$unitCode</td>
            <td>$unitName</td>
            <td>$grade</td>
        </tr>";
    }

    $html .= '
        </tbody>
    </table>';

    // Write HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Signature and Date Section
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Signature: ______________________', 0, 1);
    $pdf->Cell(0, 10, 'Date: ______________________', 0, 1);
    $pdf->Ln(10);
    $pdf->AddPage();
}

// Create a unique filename for the PDF
$pdfFilePath = __DIR__ . '/../../uploads/examination_result_slips_' . time() . '.pdf';
$pdf->Output($pdfFilePath, 'F');

if (file_exists($pdfFilePath)) {
    ob_end_flush(); // Flush output buffer first
    echo "<h3>PDF created successfully!</h3>";
    echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/results_slip_page&file=' . urlencode(basename($pdfFilePath)) . '">';
} else {
    echo "PDF creation failed.";
    exit;
}


// Close database connection
$conn->close();
?>
