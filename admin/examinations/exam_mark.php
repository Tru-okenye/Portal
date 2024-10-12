<?php
include_once __DIR__ . '/../../config/config.php';

$categories = [];
$courses = [];
$units = [];
$semesters = [];
$years = [];
$intakes = [];

// Fetch categories
$catSql = "SELECT * FROM categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

// Fetch distinct intake options
$intakeSql = "SELECT DISTINCT IntakeName FROM students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// If form is submitted, fetch the students from the examattendance table
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = $_POST['category'];
    $courseName = $_POST['course'];
    $year = $_POST['year'];
    $intake = $_POST['intake'];
    $semester = $_POST['semester'];
    $unit = $_POST['unit'];

    // Query to get students who attended the selected exam
    $attendanceSql = "SELECT ea.admission_number, ea.full_name
                      FROM examattendance ea
                      JOIN students s ON ea.admission_number = s.AdmissionNumber
                      WHERE ea.unit_name = ? AND ea.course_name = ? AND ea.semester = ? AND ea.year = ? AND ea.intake_name = ? AND ea.attendance_status = 'Present'";

    $stmt = $conn->prepare($attendanceSql);
    $stmt->bind_param('ssiss', $unit, $courseName, $semester, $year, $intake);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Exam Marks</title>
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
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_years.php', {}, function(data) {
                updateDropdown('year', data);
                document.getElementById('year').disabled = false;
            });
        }

        function handleYearChange() {
            const year = document.getElementById('year').value;
            fetchOptions('admin/examinations/fetch_intakes.php', {}, function(data) {
                updateDropdown('intake', data);
                document.getElementById('intake').disabled = false;
            });
        }

        function handleIntakeChange() {
            const intake = document.getElementById('intake').value;
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_semesters.php', { courseName: courseName, intake: intake }, function(data) {
                updateDropdown('semester', data);
                document.getElementById('semester').disabled = false;
            });
        }

        function handleSemesterChange() {
            const courseName = document.getElementById('course').value;
            const semesterNumber = document.getElementById('semester').value;
            fetchOptions('admin/examinations/fetch_units.php', { courseName: courseName, semesterNumber: semesterNumber }, function(data) {
                updateDropdown('unit', data);
                document.getElementById('unit').disabled = false;
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
        width: 100%; /* Full width on small screens */
        padding: 10px; /* Increase padding for better touch targets */
        border-radius: 10px;
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

<form method="post">
    <h2>Enter Exam Marks</h2>
    
    <!-- Category Dropdown -->
    <label for="category">Category:</label>
    <select id="category" name="category" onchange="handleCategoryChange()">
        <option value="">Select Category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo htmlspecialchars($category['CategoryID']); ?>"><?php echo htmlspecialchars($category['CategoryName']); ?></option>
        <?php endforeach; ?>
    </select>

    <!-- Course Dropdown -->
    <label for="course">Course:</label>
    <select id="course" name="course" onchange="handleCourseChange()" disabled>
        <option value="">Select Course</option>
    </select>

    <!-- Year Dropdown -->
    <label for="year">Year:</label>
    <select id="year" name="year" onchange="handleYearChange()" disabled>
        <option value="">Select Year</option>
    </select>

    <!-- Intake Dropdown -->
    <label for="intake">Intake:</label>
    <select id="intake" name="intake" onchange="handleIntakeChange()" disabled>
        <option value="">Select Intake</option>
    </select>

    <!-- Semester Dropdown -->
    <label for="semester">Semester:</label>
    <select id="semester" name="semester" onchange="handleSemesterChange()" disabled>
        <option value="">Select Semester</option>
    </select>

    <!-- Unit Dropdown -->
    <label for="unit">Unit:</label>
    <select id="unit" name="unit" disabled>
        <option value="">Select Unit</option>
    </select>

    <!-- Submit Button -->
    <input type="submit" value="Fetch Students" class="button">
</form>

<?php if (!empty($students)): ?>
    <form method="post" action="index.php?page=examinations/submit_exam_marks">
    <!-- Hidden fields for category, course, year, semester, unit, and intake -->
    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryId); ?>">
    <input type="hidden" name="course" value="<?php echo htmlspecialchars($courseName); ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>">
    <input type="hidden" name="unit" value="<?php echo htmlspecialchars($unit); ?>"> 
    <input type="hidden" name="intake" value="<?php echo htmlspecialchars($intake); ?>">

    <table border="1">
        <tr>
            <th>Admission Number</th>
            <th>Student Name</th>
            <th>CAT Marks (Out of 30)</th>
            <th>Exam Marks (Out of 70)</th>
        </tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($student['admission_number']); ?>
                    <input type="hidden" name="student_id[]" value="<?php echo htmlspecialchars($student['admission_number']); ?>">
                </td>
                <td>
                    <?php echo htmlspecialchars($student['full_name']); ?>
                    <input type="hidden" name="student_name[]" value="<?php echo htmlspecialchars($student['full_name']); ?>">
                </td>
                <td><input type="number" name="cat_marks[]" min="0" max="30" required></td>
                <td><input type="number" name="exam_marks[]" min="0" max="70" required></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <input type="submit" value="Submit Marks">
</form>

<?php endif; ?>

</body>
</html>
