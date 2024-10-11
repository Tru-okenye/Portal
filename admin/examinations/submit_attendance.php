<?php
include_once __DIR__ . '/../../config/config.php';

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the single date value
    $date = isset($_POST['date']) ? $_POST['date'] : '';

     // Ensure required fields are not empty
     if (empty($date) || empty($_POST['course']) || empty($_POST['semester']) || empty($_POST['unit']) || empty($_POST['year']) || empty($_POST['intake'])) {
        echo "All fields, including the date, are required.";
        exit;
    }
    // Prepare the SQL statement for inserting data into examattendance
    $insertSql = "INSERT INTO examattendance (admission_number, full_name, course_name, semester, unit_name, year, intake_name, date, attendance_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($insertSql)) {
        // Iterate through the attendance data submitted from the form
        foreach ($_POST['attendance'] as $admission_number => $data) {
            // Retrieve the values for the data fields
            $full_name = isset($data['full_name']) ? $data['full_name'] : '';
            $course_name = $_POST['course'] ?? '';
            $semester = $_POST['semester'] ?? '';
            $unit_name = isset($data['unit_name']) ? $data['unit_name'] : '';
            $year = $_POST['year'] ?? '';
            $intake_name = $_POST['intake'] ?? '';
            // $date = isset($data['date']) ? $data['date'] : '';
            $attendance_status = isset($data['present']) ? 'Present' : 'Absent';

        

            // Bind parameters and execute statement
            $stmt->bind_param("sssssssss", $admission_number, $full_name, $course_name, $semester, $unit_name, $year, $intake_name, $date, $attendance_status);
            $stmt->execute();
        }

        // Close the statement
        $stmt->close();

        echo "Attendance submitted successfully!";
        echo '<meta http-equiv="refresh" content="2;url=index.php?page=examinations/view_attendance">';

    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Invalid request method.";
}

// Close the connection
$conn->close();
?>
