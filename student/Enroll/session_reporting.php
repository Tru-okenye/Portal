<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/config.php';

// Check if the admission number is set in the session
if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}

$admission_number = $_SESSION['student_admission_number'];
$current_date = new DateTime(); // Current date

// Fetch student details including name and status
$sql = "SELECT FirstName, LastName, RegistrationDate, IntakeName, CategoryName, CourseName, status FROM students WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $status = $student['status'];

    // If the student is "Differed", show a message and do not allow reporting
    if ($status === 'Differed') {
        echo "<div class='card'>";
        echo "<h3>Dear {$student['FirstName']} {$student['LastName']}</h3>";
        echo "<p>Your status is <strong>Differed</strong>. You are not eligible to report for any sessions at this time. Please contact the administration for further assistance.</p>";
        echo "</div>";
        exit(); // Stop further execution
    }

    $registration_date = new DateTime($student['RegistrationDate']);
    $intake_name = $student['IntakeName'];
    $category_name = $student['CategoryName']; // Fetch category name
    $first_name = $student['FirstName'];
    $last_name = $student['LastName'];

    // Fetch semester based on category, intake, and year
    $year = (int)$registration_date->format('Y');
    $intake = $student['IntakeName'];

    // Prepare SQL statement to get the corresponding semester schedule
    $schedule_sql = "SELECT semester, start_date, end_date FROM semester_schedule WHERE category_name = ? AND year = ? AND intake = ? AND CURDATE() BETWEEN start_date AND end_date";
    $stmt_schedule = $conn->prepare($schedule_sql);
    $stmt_schedule->bind_param("sis", $category_name, $year, $intake);
    $stmt_schedule->execute();
    $schedule_result = $stmt_schedule->get_result();

    if ($schedule_result->num_rows > 0) {
        // Fetch the semester information
        $semester_info = $schedule_result->fetch_assoc();
        $current_semester = $semester_info['semester'];

        // Determine year of study and academic year
        if ($current_semester >= 1 && $current_semester <= 3) {
            $year_of_study = 1;
        } elseif ($current_semester >= 4 && $current_semester <= 6) {
            $year_of_study = 2;
            // Adjust semester format for Year 2
            $current_semester -= 3; // Convert Semester 4, 5, 6 to 1, 2, 3
        } else {
            echo "<div class='card'>Invalid semester information.</div>";
            exit();
        }

        // Calculate academic year
        $academic_year = ($current_date->format('Y')) . '/' . ($current_date->format('Y') + 1);

        // Display card with semester, year of study, and academic year
        echo "<div class='card'>";
        echo "<h3>Current Semester: Semester $current_semester</h3>";
        echo "<p>Year of Study: Year $year_of_study</p>";
        echo "<p>Academic Year: $academic_year</p>";

        // Check if the student has already reported for this semester
        $check_query = "SELECT * FROM semester_reporting_history WHERE admission_number = ? AND semester = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("si", $admission_number, $current_semester);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();

        if ($check_result->num_rows === 0) {
            // Show the report button since the student has not reported for this semester
            echo "<form method='POST'>
                    <input type='hidden' name='admission_number' value='$admission_number'>
                    <input type='hidden' name='academic_year' value='$academic_year'>
                    <button type='submit' name='report'>Report for Semester $current_semester</button>
                  </form>";
        } else {
            echo "<p>You have already reported for Semester $current_semester.</p>";
        }

        // Handle form submission
        if (isset($_POST['report'])) {
            if (isset($_POST['admission_number'], $_POST['academic_year'])) {
                $academic_year = $_POST['academic_year'];

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
                } else {
                    echo "Error reporting: " . $conn->error;
                }

                $stmt_update->close();
            } else {
                echo "Required data missing! Please try again.";
            }
        }

        echo "</div>"; // Close card div

    } else {
        echo "<div class='card'>No current semester available, please contact the admin.</div>";
    }

    // Fetch reporting history
    $history_query = "SELECT semester, academic_year, year_of_study, report_date FROM semester_reporting_history WHERE admission_number = ? ORDER BY report_date DESC";
    $stmt_history = $conn->prepare($history_query);
    $stmt_history->bind_param("s", $admission_number);
    $stmt_history->execute();
    $history_result = $stmt_history->get_result();

    // Display semester reporting history
    echo "<h4>Semester Reporting History</h4>";
    if ($history_result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Semester</th>
                    <th>Academic Year</th>
                    <th>Year of Study</th>
                    <th>Report Date</th>
                </tr>";
        
        while ($row = $history_result->fetch_assoc()) {
            echo "<tr>
                    <td>Semester " . $row['semester'] . "</td>
                    <td>" . $row['academic_year'] . "</td>
                    <td>" . $row['year_of_study'] . "</td>
                    <td>" . $row['report_date'] . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No reporting history found.</p>";
    }

    $stmt_history->close();
} else {
    echo "Student not found!";
}

$conn->close();

?>


<style>
    .card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 20px 0;
    }
    h4 {
        color: #E39825;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ccc;
    }
    th {
        background-color: #f2f2f2;
    }
    button {
        background-color: #E39825;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 10px 15px;
        cursor: pointer;
    }
    button:hover {
        background-color: #3B2314;
    }
</style>
