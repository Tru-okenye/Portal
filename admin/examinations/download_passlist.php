<?php
// Include necessary configuration and TCPDF library
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Start output buffering
ob_start();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the pass and supplementary lists from the POST data
    $passList = json_decode($_POST['passList'], true);
    $suppList = json_decode($_POST['suppList'], true);

    // Create a new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your School');
    $pdf->SetTitle('Pass and Supplementary List');
    $pdf->SetSubject('Exam Pass and Supplementary Lists');
    $pdf->SetKeywords('TCPDF, PDF, exam, pass, supplementary');

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add title
    $pdf->Cell(0, 10, 'IKIGAI COLLEGE OF INTERIOR DESIGN', 0, 1, 'C');
    $pdf->Cell(0, 10, 'Pass and Supplementary List', 0, 1, 'C');
    $pdf->Ln(10);

    // Write the Pass List
    $pdf->Cell(0, 10, 'Pass List', 0, 1);
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', '', 10);

    // Write header for the table
    $pdf->Cell(50, 10, 'Admission Number', 1);
    $pdf->Cell(80, 10, 'Student Name', 1);
    $pdf->Ln();

    // Add students in the Pass List
    foreach ($passList as $student) {
        $pdf->Cell(50, 10, $student['AdmissionNumber'], 1);
        $pdf->Cell(80, 10, $student['StudentName'], 1);
        $pdf->Ln();
    }

    // Add space before Supplementary list
    $pdf->Ln(10);

    // Write the Supplementary List
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Supplementary List', 0, 1);
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', '', 10);

    // Write header for the table
    $pdf->Cell(50, 10, 'Admission Number', 1);
    $pdf->Cell(80, 10, 'Student Name', 1);
    $pdf->Cell(50, 10, 'Failed Units', 1);
    $pdf->Ln();

    // Add students in the Supplementary List
    foreach ($suppList as $student) {
        $pdf->Cell(50, 10, $student['AdmissionNumber'], 1);
        $pdf->Cell(80, 10, $student['StudentName'], 1);
        // Use MultiCell for Failed Units to allow text to wrap to the next line
        $pdf->MultiCell(50, 10, implode(', ', $student['FailedUnits']), 1, 'L', false, 1);
    }

    // Create a unique filename for the PDF
    $pdfFilePath = __DIR__ . '/../../uploads/pass_supplementary_list_' . time() . '.pdf';

    // Output the PDF to a file
    $pdf->Output($pdfFilePath, 'F');

    // Redirect to the download page after PDF creation
    ob_end_flush(); // Flush output buffer
    echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/download_pdf&file=' . urlencode(basename($pdfFilePath)) . '">';
    exit();
} else {
    // Redirect to a main page or display an error if accessed directly
    echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/exam_pass_list">';
    exit();
}
?>
