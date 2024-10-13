<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config/config.php';

// Check if the student session is set
if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}

// Retrieve student admission number from session
$admission_number = $_SESSION['student_admission_number'];

// Fetch student details (name and other details)
$sql = "SELECT FirstName, LastName, CourseName, CategoryName FROM students WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch student data
    $student = $result->fetch_assoc();
    $first_name = $student['FirstName'];
    $last_name = $student['LastName'];
    $course_name = $student['CourseName'];
    $category_name = $student['CategoryName'];
} else {
    echo "Student details not found!";
    exit();
}

// Fetch CourseID from courses table
$course_id_query = "SELECT CourseID FROM courses WHERE CourseName = ?";
$stmt_course = $conn->prepare($course_id_query);
$stmt_course->bind_param("s", $course_name);
$stmt_course->execute();
$course_id_result = $stmt_course->get_result();

if ($course_id_result->num_rows > 0) {
    $course_id = $course_id_result->fetch_assoc()['CourseID'];

    // Fetch the most recent semester from semester_reporting_history
    $current_semester_query = "
        SELECT semester, year_of_study 
        FROM semester_reporting_history 
        WHERE admission_number = ? 
        ORDER BY report_date DESC 
        LIMIT 1";

    $stmt_current_semester = $conn->prepare($current_semester_query);
    $stmt_current_semester->bind_param("s", $admission_number);
    $stmt_current_semester->execute();
    $current_semester_result = $stmt_current_semester->get_result();


} else {
    echo "Course not found!";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/student_dashboard.css"> 
    <style>
        .flex-container {
    display: flex;
    flex-wrap: wrap; /* Allow wrapping */
    justify-content: space-between;
    align-items: flex-start;
}
.welcome-header {
    display: flex; /* Use flexbox for layout */
    justify-content: space-between; /* Space between the header and icon */
    align-items: center; /* Center items vertically */
}

.container {
    padding: 40px 20px; /* Padding for better spacing */
    border-radius: 12px; /* Slightly rounded corners */
    background-color: #fff; /* White background */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Shadow effect */
    margin-bottom: 2rem;
}

.welcome-header i {
    margin-right: 10px; /* Space between icon and text */
}




/* h2, h3 {
    color: #c0852d; 
    color: #a27634; 
} */

.welcome-note {
    font-size: 1.2em;
    color: #555; /* Slightly lighter text for welcome note */
}

.student-info {
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ccc;
}

th {
    background-color: #E39825; /* Use primary color for table header */
    color: #fff; /* White text for contrast */
}

.fee-balance-container, .year-semester-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 30%; /* Set width of the containers */
    margin-right: 40px; 
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #cf881d;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Slightly darker shadow */
    
}
.fee-balance-container{
    height: 35vh;
}

@media (max-width: 908px) {
.container, .fee-balance-container, .year-semester-container, .units-container {
    flex: 1 1 100%; /* Full width on small/medium screens */
    margin-bottom: 2rem; /* Space between containers */
}
}
    </style>
</head>
<body>
    <div class="flex-container">
        <div class="container welcome-container">
            <div class="welcome-header">
                <h2 style="display: inline;">Welcome, <?php echo "$first_name $last_name"; ?>!</h2>
                <i class="fas fa-user-graduate" style="font-size: 40px; color: #E39825; margin-left: 10px;"></i> <!-- Student icon -->
            </div>
            <p class="welcome-note">We are excited to have you onboard as a student of the <br> <strong><?php echo $course_name; ?></strong> course.</p>
        </div>

        <div class="fee-balance-container">
            <h2>
                <i class="fas fa-wallet" style="font-size: 24px; color: #fff; margin-right: 8px;"></i> <!-- Wallet icon -->
                Fee Balance:
            </h2>
            <!-- <h3>Kes 0.00</h3> -->
            <h3>Will be updated</h3>
        </div>

        <?php
        if ($current_semester_result->num_rows > 0) {
            $current_semester_data = $current_semester_result->fetch_assoc();
            $current_semester = $current_semester_data['semester'];
            $year_of_study = $current_semester_data['year_of_study'];

            // Fetch units for the current semester
            $units_current_query = "
                SELECT u.UnitCode, u.UnitName, IFNULL(ur.status, 'Not Registered') AS status 
                FROM units u
                LEFT JOIN unit_registrations ur 
                    ON u.UnitCode = ur.unit_code 
                    AND ur.admission_number = ? 
                    AND ur.year_of_study = ? 
                    AND ur.semester = ?
                WHERE u.CourseID = ? 
                AND u.SemesterNumber = ?
                ORDER BY u.UnitCode ASC";

            $units_semester = ($year_of_study - 1) * 3 + $current_semester; // Adjusting for semester
            $stmt_current_units = $conn->prepare($units_current_query);
            $stmt_current_units->bind_param("siisi", $admission_number, $year_of_study, $current_semester, $course_id, $units_semester);
            $stmt_current_units->execute();
            $units_current_result = $stmt_current_units->get_result();

            // Display units for the current semester
            echo '<div class="container units-container">';
            if ($units_current_result->num_rows > 0) {
                echo "<h3>Units for Current Semester ($current_semester)</h3>";
                echo "<table>";
                echo "<tr><th>Unit Code</th><th>Unit Name</th><th>Status</th></tr>";
                while ($unit = $units_current_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$unit['UnitCode']}</td>";
                    echo "<td>{$unit['UnitName']}</td>";
                    echo "<td>{$unit['status']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "No units found for the current semester.";
            }
            echo '</div>'; // Close the units container

            // Year and semester container
            echo '<div class="year-semester-container">';
            echo "<h2>Course:</h2>";
            echo "<h4> $course_name</h4>";
            echo "<h2>Current Session:</h2>";
            echo "<h3>Year: $year_of_study</h3>";
            echo "<h3>Semester $current_semester</h3>";
            echo '</div>'; // Close the year and semester container
        } else {
            echo "No semester reporting history found!";
        }
        ?>
    </div>
</body>
</html>

