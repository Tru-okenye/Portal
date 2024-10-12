<?php
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    $unit = isset($_POST['unit']) ? $_POST['unit'] : '';
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $intake = isset($_POST['intake']) ? $_POST['intake'] : '';
    $mode_of_study = isset($_POST['mode_of_study']) ? $_POST['mode_of_study'] : '';
    $date = isset($_POST['date']) ? $_POST['date'] : ''; 

    if (empty($course) || empty($unit) || empty($semester) || empty($year) || empty($intake) || empty($mode_of_study) || empty($date)) {
        echo "All fields are required.";
        exit;
    }

    // Process attendance data
    if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
        $attendanceData = $_POST['attendance'];
        $insertedCount = 0;

        foreach ($attendanceData as $admissionNumber => $data) {
            // $date = isset($data['date']) ? $data['date'] : '';
            $status = isset($data['status']) ? $data['status'] : 'absent'; // Default to absent if not checked
            $fullName = isset($data['full_name']) ? $data['full_name'] : '';

            if (!empty($date)) {
                // Prepare the SQL statement to prevent SQL injection
                $sql = "INSERT INTO attendance (AdmissionNumber, FullName, CourseName, UnitCode, SemesterNumber, Year, IntakeName, ModeOfStudy, Date, AttendanceStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssssisssss", $admissionNumber, $fullName, $course, $unit, $semester, $year, $intake, $mode_of_study, $date, $status);

                    if ($stmt->execute()) {
                        $insertedCount++;
                    } else {
                        echo "Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    echo "Error: " . $conn->error;
                }
            }
        }

        if ($insertedCount > 0) {
            // Redirect to the view attendance page
      
            echo "<h3>Record Updated successfully!</h3>";

            echo '<meta http-equiv="refresh" content="2;url=index.php?page=students/view_attendance">';
            exit;
        } else {
            echo "No attendance records were inserted.";
        }
    } else {
        echo "No attendance data found.";
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
