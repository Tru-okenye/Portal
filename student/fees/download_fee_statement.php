<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Check if the admission number is set in the session
if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}
$admission_number = $_SESSION['student_admission_number'];

// Retrieve student details
$sql = "
    SELECT FirstName, LastName, CategoryName, IntakeName, RegistrationDate 
    FROM students 
    WHERE AdmissionNumber = ?";
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
    $year = date('Y', strtotime($registration_date));
} else {
    echo "No student found with admission number: $admission_number";
    exit();
}
$stmt->close();

// Create new PDF instance
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Ikigai College');
$pdf->SetTitle('Fee Statement');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Logo
$pdf->Image(__DIR__ . '/../../assets/images/ikigai-college-logo-1.jpg', 80, 10, 50);

// Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Ln(20); // Line break
$pdf->Cell(0, 10, 'IKIGAI COLLEGE OF INTERIOR DESIGN', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Student Fee Statement', 0, 1, 'C');

// Student Info
$pdf->Ln(10); // Line break
$pdf->SetFont('helvetica', '', 10);
$html = "
    <strong>Student:</strong> " . strtoupper($first_name) . " " . strtoupper($last_name) . "<br>
    <strong>Category:</strong> {$category_name}<br>
    <strong>Intake:</strong> {$intake_name}<br>
    <strong>Admission Year:</strong> {$year}<br><br>";
$pdf->writeHTML($html);

// Fetch semester schedules
$schedule_sql = "
    SELECT semester, start_date, end_date 
    FROM semester_schedule
    WHERE category_name = ? AND year = ? AND intake = ?
    ORDER BY start_date";
$stmt_schedule = $conn->prepare($schedule_sql);
$stmt_schedule->bind_param("sis", $category_name, $year, $intake_name);
$stmt_schedule->execute();
$schedule_results = $stmt_schedule->get_result();

while ($schedule = $schedule_results->fetch_assoc()) {
    $semester_number = $schedule['semester'];
    $start_date = $schedule['start_date'];
    $end_date = $schedule['end_date'];

    // Compute academic year
    $start_year = date('Y', strtotime($start_date));
    $next_year = $start_year + 1;
    $academic_year = "{$start_year}/{$next_year}";

    // Semester header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, "Semester {$semester_number} ({$academic_year})", 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 8, "Start Date: {$start_date} | End Date: {$end_date}", 0, 1, 'L');

    // Fetch payments
    $payments_sql = "
        SELECT TransactionReferenceCode, PaymentDate, PaymentAmount, PaymentMode, AdditionalInfo
        FROM payments
        WHERE DocumentReferenceNumber = ? AND PaymentDate BETWEEN ? AND ?";
    $stmt_payments = $conn->prepare($payments_sql);
    $stmt_payments->bind_param("sss", $admission_number, $start_date, $end_date);
    $stmt_payments->execute();
    $payments_result = $stmt_payments->get_result();

     // If there are no payments for the semester, skip to the next
     if ($payments_result->num_rows === 0) {
        continue;
    }

        $html = '<table border="1" cellspacing="0" cellpadding="4">
                    <tr style="background-color:#E39825; color:white;">
                        <th>Transaction Reference</th>
                        <th>Payment Date</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Additional Info</th>
                    </tr>';

        $semester_total_paid = 0;
        while ($payment = $payments_result->fetch_assoc()) {
            $semester_total_paid += $payment['PaymentAmount'];
            $html .= "<tr>
                        <td>{$payment['TransactionReferenceCode']}</td>
                        <td>{$payment['PaymentDate']}</td>
                        <td>{$payment['PaymentAmount']}</td>
                        <td>{$payment['PaymentMode']}</td>
                        <td>{$payment['AdditionalInfo']}</td>
                      </tr>";
        }

        $html .= "
            <tr style='background-color:#E39825; color:white; font-weight:bold;'>
                <td colspan='2'>Total Paid for Semester</td>
                <td colspan='3'>{$semester_total_paid}</td>
                
            </tr>
        </table><br>";
        $pdf->writeHTML($html);
    
}
ob_clean();

// Output PDF for download
$pdf->Output('Fee_Statement.pdf', 'D');
?>
