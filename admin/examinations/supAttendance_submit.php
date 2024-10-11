<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

      // Retrieve the single date value
      $attendanceDate = isset($_POST['date']) ? $_POST['date'] : '';

      if (empty($attendanceDate)) {
          echo "The attendance date is required.";
          exit;
      }

    if (isset($_POST['attendance'])) {
        $success = true;

        foreach ($_POST['attendance'] as $admissionNumber => $attendanceData) {
            $studentName = $attendanceData['name'];
            $courseName = $attendanceData['course'];
            $year = $attendanceData['year'];
            $intake = $attendanceData['intake'];
            $semester = $attendanceData['semester'];
            $unitCode = $attendanceData['unit'];
            // $attendanceDate = $attendanceData['date'];
            $attendanceStatus = isset($attendanceData['present']) ? 'Present' : 'Absent';

            // Prepare the SQL statement
            $sql = "INSERT INTO supp_attendance (admission_number, student_name, course_name, year, intake, semester, unit_code, attendance_date, attendance_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $admissionNumber, $studentName, $courseName, $year, $intake, $semester, $unitCode, $attendanceDate, $attendanceStatus);

            if (!$stmt->execute()) {
                $success = false;
                echo "Error saving attendance for student with admission number $admissionNumber.<br>";
            }
        }

        if ($success) {
            echo "Attendance records updated successfully!";
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/supp_report">';

        }
    } else {
        echo "No attendance data to process.";
    }
} else {
    echo "Invalid request method.";
}
?>
