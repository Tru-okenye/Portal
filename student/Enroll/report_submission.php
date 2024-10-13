<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/config.php';

if (isset($_POST['report'])) {
    if (isset($_POST['admission_number'], $_POST['academic_year'], $_POST['current_semester'], $_POST['year_of_study'])) {
        $admission_number = $_POST['admission_number'];
        $academic_year = $_POST['academic_year'];
        $current_semester = (int)$_POST['current_semester'];
        $year_of_study = (int)$_POST['year_of_study'];

        // Update student's status to 'Enrolled'
        $update_query = "UPDATE students SET status = 'Enrolled' WHERE AdmissionNumber = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("s", $admission_number);

        if ($stmt_update->execute()) {
            // Insert into semester reporting history
            $insert_history = "INSERT INTO semester_reporting_history (admission_number, semester, academic_year, year_of_study, report_date) VALUES (?, ?, ?, ?, NOW())";
            $stmt_history = $conn->prepare($insert_history);
            $stmt_history->bind_param("sisi", $admission_number, $current_semester, $academic_year, $year_of_study);
            $stmt_history->execute();

            // Show success alert using JavaScript
            echo "<script>alert('You have successfully reported for Semester $current_semester!');</script>";
            echo '<meta http-equiv="refresh" content="2;url=index.php?page=Enroll/session_reporting">';

            exit();
        } else {
            echo "Error reporting: " . $conn->error;
        }

        $stmt_update->close();
    } else {
        echo "Required data missing! Please try again.";
    }
}

$conn->close();
?>
