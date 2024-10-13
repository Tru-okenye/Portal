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
    $course_id_query = "SELECT CourseID, CourseOutline FROM courses WHERE CourseName = ?";
    $stmt_course = $conn->prepare($course_id_query);
    $stmt_course->bind_param("s", $course_name);
    $stmt_course->execute();
    $course_id_result = $stmt_course->get_result();

    if ($course_id_result->num_rows > 0) {
        $course = $course_id_result->fetch_assoc();
        $course_id = $course['CourseID'];
        $course_outline = $course['CourseOutline'];

        // Fetch SemestersCount from categories table
        $category_query = "SELECT SemestersCount FROM categories WHERE CategoryName = ?";
        $stmt_category = $conn->prepare($category_query);
        $stmt_category->bind_param("s", $category_name);
        $stmt_category->execute();
        $category_result = $stmt_category->get_result();

        if ($category_result->num_rows > 0) {
            $category = $category_result->fetch_assoc();
            $semesters_count = $category['SemestersCount'];

            // HTML for the tabs
            ?>
            <div class="tabs">
                <ul>
                    <?php
                    // Create tabs dynamically for each semester
                    for ($i = 1; $i <= $semesters_count; $i++) {
                        echo "<li><a href='#semester$i' class='" . ($i == 1 ? 'active' : '') . "'>Semester $i</a></li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="tab-content">
                <?php
                // Fetch and display units for each semester
                for ($i = 1; $i <= $semesters_count; $i++) {
                    ?>
                    <div id="semester<?php echo $i; ?>" class="tab <?php echo ($i == 1 ? 'active' : ''); ?>">
                        <h2>Units for Semester <?php echo $i; ?> - <?php echo $course_name; ?> (<?php echo $category_name; ?>)</h2>
                        <?php if ($i == 1): // Only show course outline on the first tab ?>
                        <h3>Course Outline</h3>
                        <p><?php echo nl2br($course_outline); // Display course outline ?></p>
                        <?php endif; ?>
                        <?php
                        // Fetch units for the current semester, including CourseContent
                        $units_query = "SELECT UnitCode, UnitName, CourseContent, reference_materials FROM units WHERE CourseID = ? AND SemesterNumber = ? ORDER BY UnitCode ASC";
                        $stmt_units = $conn->prepare($units_query);
                        $stmt_units->bind_param("si", $course_id, $i);
                        $stmt_units->execute();
                        $units_result = $stmt_units->get_result();

                        if ($units_result->num_rows > 0) {
                            while ($unit = $units_result->fetch_assoc()) {
                                // Display UnitCode and UnitName as headings
                                echo "<h3>Unit: {$unit['UnitCode']} - {$unit['UnitName']}</h3>";

                                // Display CourseContent and Reference Materials in a table
                                echo "<table>";
                                echo "<tr><th>Course Content</th><th>Reference Materials</th></tr>";
                                echo "<tr>";
                                echo "<td>" . nl2br(htmlspecialchars($unit['CourseContent'])) . "</td>";
                                echo "<td>" . nl2br(htmlspecialchars($unit['reference_materials'])) . "</td>";
                                echo "</tr>";
                                echo "</table><br>"; // Add space between unit entries
                            }
                        } else {
                            echo "No units found for Semester $i.";
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
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
    /* General styling for tabs */
    .tabs {
        margin-bottom: 20px;
    }

    .tabs ul {
        list-style-type: none;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
    }

    .tabs li {
        margin-right: 10px;
    }

    .tabs a {
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #E39825;
        color: white;
        display: inline-block;
        margin-bottom: 5px;
    }

    .tabs a.active {
        background-color: #3B2314;
        color: white;
    }

    .tab-content > div {
        display: none;
    }

    .tab-content > div.active {
        display: block;
        padding: 15px;
        background-color: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: -1px; /* overlap the borders */
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
        background-color: #E39825;
        color: white;
    }

    /* Media query for medium screens (tablets, etc.) */
    @media (max-width: 380px) {
        .tabs ul {
            display: block; /* Stack tabs vertically */
        }

        .tabs li {
            margin-right: 0;
            margin-bottom: 5px;
        }

        .tabs a {
            display: block;
            width: 60%; /* Full width for each tab */
        }

        .tab-content > div {
            padding: 10px; /* Reduce padding on smaller screens */
        }

        table th, table td {
            padding: 8px; /* Adjust table cell padding */
        }
    }

    /* Media query for small screens (mobile phones) */
    @media (max-width: 320px) {
        .tabs ul {
            display: block;
        }

        .tabs li {
            margin-right: 0;
            margin-bottom: 5px;
        }

        .tabs a {
            display: block;
            width: 60%; /* Make each tab full width */
        }

        table th, table td {
            padding: 5px; /* Further reduce table cell padding for mobile */
        }

        .tab-content > div {
            padding: 8px; /* Further reduce padding on mobile */
        }
    }
</style>

            <?php
        } else {
            echo "Category not found!";
        }
    } else {
        echo "Course not found!";
    }
} else {
    echo "Student record not found!";
}
?>
