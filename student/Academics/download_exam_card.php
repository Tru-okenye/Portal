<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}

$admission_number = $_SESSION['student_admission_number'];

// Fetch student details
$sql = "SELECT FirstName, LastName, CategoryName, IntakeName, RegistrationDate, CourseName 
        FROM students WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $first_name = $student['FirstName'];
    $last_name = $student['LastName'];
    $category_name = $student['CategoryName'];
    $intake_name = $student['IntakeName'];
    $registration_date = $student['RegistrationDate'];
    $course_name = $student['CourseName'];
    $year = date('Y', strtotime($registration_date));
} else {
    echo "No student found with admission number: $admission_number";
    exit();
}
$stmt->close();

// Step 2: Fetch semester schedules based on category and intake
$schedule_sql = "
    SELECT semester, start_date, end_date 
    FROM semester_schedule
    WHERE category_name = ? AND intake = ?
    ORDER BY start_date";
$stmt_schedule = $conn->prepare($schedule_sql);
$stmt_schedule->bind_param("ss", $category_name, $intake_name);
$stmt_schedule->execute();
$schedule_results = $stmt_schedule->get_result();


while ($schedule = $schedule_results->fetch_assoc()) {
    $semester_number = $schedule['semester'];
    $start_date = $schedule['start_date'];
    $end_date = $schedule['end_date'];

    // Compute academic year from the start date
    $start_year = date('Y', strtotime($start_date));
    $next_year = $start_year + 1;
    $academic_year = "{$start_year}/{$next_year}";
    // Determine year_of_study and SemesterNumber for the current semester
    $year_of_study = ($semester_number <= 3) ? 1 : 2;

}



$stmt_schedule->close();


// Step 3: Fetch the most recent reported semester and year of study from semester_reporting_history
$report_sql = "
    SELECT semester, academic_year, year_of_study 
    FROM semester_reporting_history
    WHERE admission_number = ?
    ORDER BY report_date DESC LIMIT 1";
$stmt_report = $conn->prepare($report_sql);
$stmt_report->bind_param("s", $admission_number);
$stmt_report->execute();
$report_result = $stmt_report->get_result();

if ($report_result->num_rows > 0) {
    $report = $report_result->fetch_assoc();
    $reported_semester = $report['semester'];
    $reported_year_of_study = $report['year_of_study'];
} else {
    echo "No reporting history found for this student.";
    exit();
}

$stmt_report->close();

// Step 4: Fetch the units registered under the reported semester and year_of_study
$unit_registration_sql = "
    SELECT unit_code 
    FROM unit_registrations
    WHERE admission_number = ? AND semester = ? AND year_of_study = ? AND status = 'approved'";
$stmt_unit_registration = $conn->prepare($unit_registration_sql);
$stmt_unit_registration->bind_param("sii", $admission_number, $reported_semester, $reported_year_of_study);
$stmt_unit_registration->execute();
$unit_registration_results = $stmt_unit_registration->get_result();
// Deduce start month, end month, and current year
$start_month = date('F', strtotime($start_date)); // Full month name for start date
$end_month = date('F', strtotime("+3 months", strtotime($start_date))); // Full month name, 3 months after start date
$current_year = date('Y');

// Store units in an array
$units = [];
while ($unit = $unit_registration_results->fetch_assoc()) {
    $units[] = $unit;
}
$stmt_unit_registration->close();

// Generate PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Ikigai College');
$pdf->SetTitle('Student Exam Card');
$pdf->SetSubject('Examination Card');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);
$imagePath = __DIR__ . '/../../assets/images/ikigai-college-logo-1.jpg';
$imgPath = __DIR__ . '/../../assets/images/IMG-20241125-WA0003(1).jpg';
// HTML content
$html = '
     <div style="text-align: center; margin-bottom: 12px;">
        <img src="' . $imagePath . '" alt="Ikigai College Logo" style="width: 150px; height: auto;">
    </div>
     <h2 style="text-align:center; color:#3B2314;">IKIGAI COLLEGE OF INTERIOR DESIGN</h2>
    <h3 style="text-align:center; color:#3B2314;">Student Examination Card</h3>
    <p><strong style="color:#E39825;">STUDENT’S NAME:</strong> ' . $first_name . ' ' . $last_name . '</p>
    <p><strong style="color:#E39825;">SCHOOL ID NUMBER:</strong> ' . $admission_number . '</p>
    <p><strong style="color:#E39825;">COURSE:</strong> ' . $course_name . '</p>
    <p><strong style="color:#E39825;">SEMESTER:</strong> ' . $semester_number . '</p>
    <p><strong style="color:#E39825;">Year of Study:</strong> ' . $year_of_study . '</p>
    <p><strong style="color:#E39825;">ACADEMIC YEAR:</strong> ' . $academic_year . '</p>
    <br>
    <table border="1" cellpadding="3">
        <thead>
            <tr style="background-color:#3B2314; color:white;">
                <th>Unit Code</th>
                <th>Unit Name</th>
                <th>Exam Type</th>
            </tr>
        </thead>
        <tbody>';

if (count($units) > 0) {
    foreach ($units as $unit) {
        $unit_code = $unit['unit_code'];
        $unit_name_sql = "SELECT UnitName FROM units WHERE UnitCode = ?";
        $stmt_unit_name = $conn->prepare($unit_name_sql);
        $stmt_unit_name->bind_param("s", $unit_code);
        $stmt_unit_name->execute();
        $unit_name_result = $stmt_unit_name->get_result();
        $unit_name = $unit_name_result->num_rows > 0 ? $unit_name_result->fetch_assoc()['UnitName'] : "Unknown Unit Name";
        $stmt_unit_name->close();

        $html .= '
            <tr>
                <td>' . $unit_code . '</td>
                <td>' . $unit_name . '</td>
                <td>Regular</td>
            </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="3" style="text-align:center;">No approved units found.</td>
        </tr>';
}

$html .= '
        </tbody>
    </table>
    <p><strong>Instructions:</strong></p>
    <ol>
        <li>For a card to be genuine, the student MUST appear in the course examination register with the invigilator.</li>
        <li>This card is confidential, and it must be produced to the invigilator when required at each examination.</li>
        <li>The name which appears on this card is the name which will appear on the degree/diploma certificate. Any errors must be communicated immediately. Initials are not allowed.</li>
        <li>Mobile phones, notes, and other communication devices are NOT allowed in the examination room.</li>
        <li>Refer to the cover of the answer booklet for more instructions on student conduct during examinations.</li>
        <li>Cheating in examinations is a serious offence PUNISHABLE BY EXPULSION.</li>
        </ol>
       <p><strong>Signed:</strong> ___________________________</p>
        <p><strong>Date:</strong> ___________________________</p>
     
       
       ';
       
       $pdf->writeHTML($html);

ob_clean();
$pdf->Output('exam_card_' . $admission_number . '.pdf', 'D');
exit();
?>
