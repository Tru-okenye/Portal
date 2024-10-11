<?php
include_once __DIR__ . '/../../config/config.php';

if (isset($_POST['download_attendance'])) {
    $course = $_POST['course'];
    $unit = $_POST['unit'];
    $semester = $_POST['semester'];
    $year = $_POST['year'];
    $intake = $_POST['intake'];

    // Fetch the attendance data from the database
    $attendanceData = [];
    $studentSql = "SELECT AdmissionNumber, CONCAT(FirstName, ' ', LastName) AS FullName 
                   FROM Students 
                   WHERE CourseName = ? AND YEAR(RegistrationDate) = ? AND IntakeName = ?";
    $stmt = $conn->prepare($studentSql);
    $stmt->bind_param("sis", $course, $year, $intake);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $attendanceData[] = $row;
    }

    // Create CSV file
    $filename = "attendance_{$course}_{$unit}_{$semester}_{$year}_{$intake}.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Admission Number', 'Full Name', 'Date', 'Attendance Status']); // CSV Header

    foreach ($attendanceData as $attendance) {
        fputcsv($output, [
            $attendance['AdmissionNumber'],
            $attendance['FullName'],
            '', // Date can be filled in the application if you want to track
            ''  // Attendance status can also be tracked
        ]);
    }

    fclose($output);
    exit;
}
?>
