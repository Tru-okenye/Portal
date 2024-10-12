<?php
include_once __DIR__ . '/../../config/config.php';

// Initialize variables to store fetched data
$categories = [];
$years = [];
$intakes = [];
$students = [];

// Fetch categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years and intake options
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

$intakeSql = "SELECT DISTINCT IntakeName FROM students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// Process form submission for supplementary  and retake list
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryId = $_POST['category'] ?? '';
    $courseName = $_POST['course'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $year = $_POST['year'] ?? '';
    $intake = $_POST['intake'] ?? '';

// Fetch students who failed two or fewer units within the first three attempts
$sqlFailingUnits = "
    SELECT admission_number, student_name, COUNT(DISTINCT unit_code) as failed_units_count, 
           GROUP_CONCAT(DISTINCT unit_name SEPARATOR ', ') as failed_units
    FROM supplementary_exam_marks as sem
    WHERE category_name = (SELECT CategoryName FROM categories WHERE CategoryID = ?)
      AND course_name = ?
      AND semester = ?
      AND year = ?
      AND intake = ?
      -- Select only units where the latest attempt (within the first three) was a fail
      AND total_marks < 40 
      AND attempt_number <= 3
      -- Make sure there isn't any pass (marks >= 40) in later attempts
      AND NOT EXISTS (
          SELECT 1 FROM supplementary_exam_marks as sem2
          WHERE sem.admission_number = sem2.admission_number
            AND sem.unit_code = sem2.unit_code
            AND sem2.attempt_number > sem.attempt_number
            AND sem2.total_marks >= 40
      )
    GROUP BY admission_number, student_name
    HAVING failed_units_count <= 2";

if ($stmtFailingUnits = $conn->prepare($sqlFailingUnits)) {
    $stmtFailingUnits->bind_param('sssss', $categoryId, $courseName, $semester, $year, $intake);
    $stmtFailingUnits->execute();
    $resultFailingUnits = $stmtFailingUnits->get_result();
    $studentsFailing2OrLessUnits = [];
    while ($row = $resultFailingUnits->fetch_assoc()) {
        $studentsFailing2OrLessUnits[] = $row;
    }
    $stmtFailingUnits->close();
} else {
    echo "Error: " . $conn->error;
}

    // Fetch students who need a retake (failed more than two units in the first attempt)
    $sqlRetake = "SELECT admission_number, student_name, COUNT(*) as failed_units, GROUP_CONCAT(unit_name SEPARATOR ', ') as failed_units_list
                  FROM supplementary_exam_marks
                  WHERE category_name = (SELECT CategoryName FROM categories WHERE CategoryID = ?)
                    AND course_name = ?
                    AND semester = ?
                    AND year = ?
                    AND intake = ?
                    AND total_marks < 40
                    AND attempt_number = 1
                  GROUP BY admission_number, student_name
                  HAVING failed_units > 2";
    if ($stmtRetake = $conn->prepare($sqlRetake)) {
        $stmtRetake->bind_param('sssss', $categoryId, $courseName, $semester, $year, $intake);
        $stmtRetake->execute();
        $resultRetake = $stmtRetake->get_result();
        $studentsRetake = [];
        while ($row = $resultRetake->fetch_assoc()) {
            $studentsRetake[] = $row;
        }
        $stmtRetake->close();
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplementary and Retake List</title>
    <script>
        function fetchOptions(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => callback(result))
            .catch(error => console.error('Error:', error));
        }

        function updateDropdown(selectId, options) {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">Select</option>';
            options.forEach(option => {
                select.innerHTML += `<option value="${option.value}">${option.text}</option>`;
            });
        }

        function handleCategoryChange() {
            const categoryId = document.getElementById('category').value;
            fetchOptions('admin/examinations/fetch_courses.php', { categoryId: categoryId }, function(data) {
                updateDropdown('course', data);
                document.getElementById('course').disabled = false;
                document.getElementById('semester').disabled = true;
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_semesters.php', { courseName: courseName }, function(data) {
                updateDropdown('semester', data);
                document.getElementById('semester').disabled = false;
            });
        }

        function handleSemesterChange() {
            const semesterNumber = document.getElementById('semester').value;
            fetchOptions('admin/examinations/fetch_years.php', {}, function(data) {
                updateDropdown('year', data);
                document.getElementById('year').disabled = false;
            });
        }

        function handleYearChange() {
            fetchOptions('admin/examinations/fetch_intakes.php', {}, function(data) {
                updateDropdown('intake', data);
                document.getElementById('intake').disabled = false;
            });
        }
    </script>
    <style>
    form h2, h3 {
        color: #cf881d;
        text-align: center; /* Center align the headings */
    }

    label {
        margin-right: 5px;
        color: #3B2314;
        font-weight: bold;
    }

    select, form button {
        width: 100%; /* Full width on small screens */
        padding: 8px;
        margin-top: 15px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        color: #fff;
        box-sizing: border-box; /* Include padding in width */
    }

    select {
        color: black; 
    }

    .button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        border-radius: 10px;
        padding: 10px; /* Increased padding for better touch targets */
    }

    .button:hover {
        background-color: #E39825;
        color: #3B2314;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    } 

    table, th {
        border: 1px solid #3B2314;
    }

    th {
        padding: 12px;
        text-align: left;
        background-color: #E39825;
        color: white;
    }

    /* Responsive Styles */
    @media screen and (min-width: 481px) and (max-width: 768px) {
        select, .button {
            width: 48%; /* Adjust width for medium screens */
            display: inline-block; /* Display buttons and selects side by side */
            margin-right: 4%; /* Space between elements */
        }

        .button {
            margin-top: 0; /* Remove margin on button for better alignment */
        }

        /* Reset the right margin for the last button */
        .button:last-of-type, select:last-of-type {
            margin-right: 0;
        }
    }

    @media screen and (min-width: 769px) {
        select, .button {
            width: 15%; /* Keep original width for large screens */
        }
    }
</style>

</head>
<body>
    <form method="post" action="">
        <h2>Supplementary and Retake List</h2>
        <!-- Category Dropdown -->
        <label for="category">Category:</label>
        <select id="category" name="category" required onchange="handleCategoryChange()">
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['CategoryID']); ?>"><?php echo htmlspecialchars($category['CategoryName']); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Course Dropdown -->
        <label for="course">Course:</label>
        <select id="course" name="course" required onchange="handleCourseChange()" disabled>
            <option value="">Select Course</option>
        </select>



        <!-- Year Dropdown -->
        <label for="year">Year:</label>
        <select id="year" name="year" required>
            <option value="">Select Year</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Intake Dropdown -->
        <label for="intake">Intake:</label>
        <select id="intake" name="intake" required>
            <option value="">Select Intake</option>
            <?php foreach ($intakes as $intake): ?>
                <option value="<?php echo htmlspecialchars($intake); ?>"><?php echo htmlspecialchars($intake); ?></option>
            <?php endforeach; ?>
        </select>

                <!-- Semester Dropdown -->
                <label for="semester">Semester:</label>
        <select id="semester" name="semester" required>
            <option value="">Select Semester</option>
        </select>
        <input type="submit" value="Submit" class="button">
    </form>


    <!-- Retake List Display -->
    <?php if (isset($studentsRetake) && !empty($studentsRetake)): ?>
        <h3>Retake List for Students with More Than Two Supplementary Failures</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Admission Number</th>
                    <th>Student Name</th>
                    <th>Failed Units Count</th>
                    <th>Failed Units</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentsRetake as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['failed_units']); ?></td>
                        <td><?php echo htmlspecialchars($student['failed_units_list']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($studentsRetake) && empty($studentsRetake)): ?>
        <p>No students need a retake in the selected semester.</p>
    <?php endif; ?>


     <!-- Failing 2 or Less Units List Display -->
     <?php if (isset($studentsFailing2OrLessUnits) && !empty($studentsFailing2OrLessUnits)): ?>
        <h3>Students Failing Two or Fewer Units Within Their First Three Attempts</h3>
        <table border="1">
            <thead>
                <tr>
                    <th>Admission Number</th>
                    <th>Student Name</th>
                    <th>Failed Units Count</th>
                    <th>Failed Units</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($studentsFailing2OrLessUnits as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['failed_units_count']); ?></td>
                        <td><?php echo htmlspecialchars($student['failed_units']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (isset($studentsFailing2OrLessUnits) && empty($studentsFailing2OrLessUnits)): ?>
        <p>No students have failed two or fewer units within their first three attempts in the selected semester.</p>
    <?php endif; ?>

</body>
</html>
