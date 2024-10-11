<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['student_admission_number'])) {
    echo "Student session not found! Please log in.";
    exit();
}

$admission_number = $_SESSION['student_admission_number'];

// Fetch student details
$sql = "SELECT CourseName, CategoryName FROM students WHERE AdmissionNumber = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $course_name = $student['CourseName'];
    $category_name = $student['CategoryName'];

    // Fetch CourseID from courses table
    $course_id_query = "SELECT CourseID FROM courses WHERE CourseName = ?";
    $stmt_course = $conn->prepare($course_id_query);
    $stmt_course->bind_param("s", $course_name);
    $stmt_course->execute();
    $course_id_result = $stmt_course->get_result();

    if ($course_id_result->num_rows > 0) {
        $course_id = $course_id_result->fetch_assoc()['CourseID'];

        // Fetch units based on CourseID for curriculum display
        $units_query = "SELECT SemesterNumber, UnitCode, UnitName FROM units WHERE CourseID = ? ORDER BY SemesterNumber ASC";
        $stmt_units = $conn->prepare($units_query);
        $stmt_units->bind_param("s", $course_id);
        $stmt_units->execute();
        $units_result = $stmt_units->get_result();
        
        // Prepare units for display
        $units_by_semester = [];
        while ($row = $units_result->fetch_assoc()) {
            $units_by_semester[$row['SemesterNumber']][] = $row;
        }

        // HTML for the tabs
        ?>
        <div class="tabs">
            <ul>
                <li><a href="#exams" class="active">Units Registration</a></li>
                <li><a href="#curriculum">Curriculum</a></li>
                <li><a href="#unithistory">Units History</a></li>
            </ul>
        </div>

        <div class="tab-content">
            <div id="curriculum" class="tab">
                <h2>Curriculum for <?php echo $course_name; ?> (<?php echo $category_name; ?>)</h2>
                <?php
                // Display units grouped by semester
                foreach ($units_by_semester as $semester => $units) {
                    echo "<h3>Semester $semester</h3>";
                    echo "<table>";
                    echo "<tr><th>Unit Code</th><th>Unit Name</th></tr>";
                    foreach ($units as $unit) {
                        echo "<tr>";
                        echo "<td>{$unit['UnitCode']}</td>";
                        echo "<td>{$unit['UnitName']}</td>";
                        echo "</tr>";
                    }
                    echo "</table><br/>";
                }
                ?>
            </div>
            <div id="unithistory" class="tab">
                <h2>Units History</h2>
                <?php
                // Fetch approved units grouped by semester and year of study
                $approved_units_query = "
                    SELECT ur.semester, ur.year_of_study, ur.unit_code, u.UnitName 
                    FROM unit_registrations ur
                    JOIN units u ON ur.unit_code = u.UnitCode
                    WHERE ur.admission_number = ? AND ur.status = 'approved'
                    ORDER BY ur.year_of_study, ur.semester";

                $stmt_approved_units = $conn->prepare($approved_units_query);
                $stmt_approved_units->bind_param("s", $admission_number);
                $stmt_approved_units->execute();
                $approved_units_result = $stmt_approved_units->get_result();

                // Initialize variables for grouping
                $current_year = null;
                $current_semester = null;

                if ($approved_units_result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Year</th><th>Semester</th><th>Unit Code</th><th>Unit Name</th></tr>";
                    while ($unit = $approved_units_result->fetch_assoc()) {
                        // Check if we need to print a new year/semester header
                        if ($current_year !== $unit['year_of_study'] || $current_semester !== $unit['semester']) {
                            $current_year = $unit['year_of_study'];
                            $current_semester = $unit['semester'];
                            echo "<tr><td colspan='4' style='font-weight: bold;'>Year {$current_year}, Semester {$current_semester}</td></tr>";
                        }

                        // Print unit details
                        echo "<tr>";
                        echo "<td>{$current_year}</td>";
                        echo "<td>{$current_semester}</td>";
                        echo "<td>{$unit['unit_code']}</td>";
                        echo "<td>{$unit['UnitName']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "No approved units found for the selected semester.";
                }
                ?>
            </div>

            <div id="exams" class="active">
                <h2>Units Registration</h2>
                <?php
                // Fetch the most recent semester from semester_reporting_history based on report_date
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

                if ($current_semester_result->num_rows > 0) {
                    $current_semester_data = $current_semester_result->fetch_assoc();
                    $current_semester = $current_semester_data['semester'];
                    $year_of_study = $current_semester_data['year_of_study'];

                    // Adjust the semester for unit fetching based on year of study
                    if ($year_of_study == 1) {
                        $units_semester = $current_semester;
                    } else if ($year_of_study == 2) {
                        $units_semester = $current_semester + 3;
                    } else {
                        echo "Invalid year of study!";
                        exit();
                    }

                    // Fetch units for the current semester
                    $units_current_query = "
                        SELECT u.UnitCode, u.UnitName, IFNULL(ur.status, 'Not Registered') AS status 
                        FROM units u
                        LEFT JOIN unit_registrations ur 
                            ON u.UnitCode = ur.unit_code 
                            AND ur.admission_number = ? 
                            AND ur.year_of_study = ? 
                            AND ur.semester = ?
                        WHERE u.CourseID = ? AND u.SemesterNumber = ? 
                        ORDER BY u.UnitCode ASC";

                    $stmt_current_units = $conn->prepare($units_current_query);
                    $stmt_current_units->bind_param("siisi", $admission_number, $year_of_study, $current_semester, $course_id, $units_semester);
                    $stmt_current_units->execute();
                    $units_current_result = $stmt_current_units->get_result();

                    // Display units for the current semester with registration status
                    if ($units_current_result->num_rows > 0) {
                        echo "<h3>Units for Current Semester ($current_semester)</h3>";
                        echo "<form action='index.php?page=Academics/register_units' method='post'>";
                        echo "<input type='hidden' name='admission_number' value='{$admission_number}'>";
                        echo "<input type='hidden' name='year_of_study' value='{$year_of_study}'>";
                        echo "<input type='hidden' name='semester' value='{$current_semester}'>";

                        echo "<table>";
                        echo "<tr><th>Unit Code</th><th>Unit Name</th><th>Status</th><th>Register</th></tr>";
                        $all_registered = true;
                        while ($unit = $units_current_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$unit['UnitCode']}</td>";
                            echo "<td>{$unit['UnitName']}</td>";
                            echo "<td>{$unit['status']}</td>";
                            if ($unit['status'] == 'Not Registered') {
                                echo "<td><input type='checkbox' name='units[]' value='{$unit['UnitCode']}'></td>";
                                $all_registered = false; 
                            } else {
                                echo "<td>Already Registered</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "<br/>";
                        // Disable the button if all units are registered
                        if ($all_registered) {
                            echo "<input type='submit' value='Register Selected Units' class='btn btn-primary' disabled>";
                            echo "<p>All units have been registered.</p>";
                        } else {
                            echo "<input type='submit' value='Register Selected Units' class='btn btn-primary'>";
                        }
                        echo "</form>";
                    } else {
                        echo "No units found for the current semester.";
                    }
                } else {
                    echo "No semester reporting history found!";
                }
                ?>
            </div>
        </div>

        <script>
            const tabs = document.querySelectorAll('.tabs a');
            const tabContents = document.querySelectorAll('.tab-content > div');

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    const target = tab.getAttribute('href');

                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(tc => tc.classList.remove('active'));

                    // Add active class to the clicked tab and corresponding content
                    tab.classList.add('active');
                    document.querySelector(target).classList.add('active');
                });
            });
        </script>

<style>
    .tabs {
        margin-bottom: 20px;
        
    }
    .tabs ul {
        list-style-type: none;
        padding: 0;
        display: flex; /* Use flexbox for horizontal layout */
        flex-wrap: wrap; /* Allow wrapping if necessary */
    }
    .tabs li {
        margin-right: 10px; /* Space between tabs */

        margin-top: 10px;
    
    }
    .tabs a {
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid #E39825;
        border-radius: 5px;
        background-color: #3B2314;
        color: #E39825;
        flex: 1; /* Allow tabs to grow equally */
        min-width: 150px; /* Minimum width for tabs */
        text-align: center; /* Center text in tabs */
    }
    .tabs a.active {
        background-color: #E39825;
        color: white;
    }
    .tab-content > div {
        display: none;
    }
    .tab-content > div.active {
        display: block;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ccc;
    }
    th {
        background-color: #E39825;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    
    /* Media query for smaller screens */
    @media (max-width: 350px) {
        .tabs ul {
            flex-direction: column; /* Stack tabs on smaller screens */
        }
        .tabs li {
            margin-bottom: 10px; /* Space between stacked tabs */
        }
    }
</style>

        <?php
    } else {
        echo "Course not found!";
    }
} else {
    echo "Student record not found!";
}
?>
