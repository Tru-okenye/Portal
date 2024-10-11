<?php
include_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

if (isset($_POST['generate_pdf'])) {
    // Retrieve the form data
    $course = htmlspecialchars($_POST['course']);
    $unit = htmlspecialchars($_POST['unit']);
    $semester = htmlspecialchars($_POST['semester']);
    $year = htmlspecialchars($_POST['year']);
    $intake = htmlspecialchars($_POST['intake']);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your School');
    $pdf->SetTitle('Exam Attendance Form');
    $pdf->SetSubject('Exam Attendance Form');
    $pdf->SetKeywords('TCPDF, PDF, exam, attendance, form');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Exam Attendance Form', '');

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

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

    // Add title with selected details
    $heading = "$course\n$intake Intake $year\nSemester $semester\nUnit $unit";
    $pdf->Cell(0, 10, $heading, 0, 1, 'C');

    // Add a space before the table
    $pdf->Ln(10);

    // Add table
    $html = '
    <table border="1" cellpadding="4">
        <thead>
            <tr>
                <th>Admission Number</th>
                <th>Full Name</th>
                <th>Date</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($_POST['students'] as $student) {
        // Add fields for date and signature (placeholders for now)
        $html .= '<tr>
            <td>' . htmlspecialchars($student['AdmissionNumber']) . '</td>
            <td>' . htmlspecialchars($student['FullName']) . '</td>
            <td></td>
            <td></td>
        </tr>';
    }

    $html .= '
        </tbody>
    </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Close and output PDF document
    $pdf->Output('exam_attendance_form.pdf', 'D');
} else {
    echo 'No data submitted.';
}
?>
