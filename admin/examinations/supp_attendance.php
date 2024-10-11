<?php
include_once __DIR__ . '/../../config/config.php';

$categories = [];
$courses = [];
$units = [];
$semesters = [];
$years = [];
$intakes = [];
$students = [];  // Initialize students as an empty array

// Fetch categories
$catSql = "SELECT * FROM Categories";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch distinct years
$yearSql = "SELECT DISTINCT YEAR(RegistrationDate) AS Year FROM Students ORDER BY Year DESC";
$yearResult = $conn->query($yearSql);
if ($yearResult->num_rows > 0) {
    while ($row = $yearResult->fetch_assoc()) {
        $years[] = $row['Year'];
    }
}

// Fetch distinct intake options
$intakeSql = "SELECT DISTINCT IntakeName FROM Students";
$intakeResult = $conn->query($intakeSql);
if ($intakeResult->num_rows > 0) {
    while ($row = $intakeResult->fetch_assoc()) {
        $intakes[] = $row['IntakeName'];
    }
}

// Fetch courses, semesters, and units if form is submitted
if (isset($_POST['category'])) {
    $categoryId = $_POST['category'];

    // Fetch courses based on selected category
    $courseSql = "SELECT * FROM Courses WHERE CategoryID = ?";
    $courseStmt = $conn->prepare($courseSql);
    $courseStmt->bind_param("i", $categoryId);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    if ($courseResult->num_rows > 0) {
        while ($row = $courseResult->fetch_assoc()) {
            $courses[] = $row;
        }
    }

    if (isset($_POST['course'])) {
        $courseName = $_POST['course'];

        // Fetch semesters based on selected course
        $semesterSql = "SELECT DISTINCT SemesterNumber FROM Units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?)";
        $semesterStmt = $conn->prepare($semesterSql);
        $semesterStmt->bind_param("s", $courseName);
        $semesterStmt->execute();
        $semesterResult = $semesterStmt->get_result();
        if ($semesterResult->num_rows > 0) {
            while ($row = $semesterResult->fetch_assoc()) {
                $semesters[] = $row['SemesterNumber'];
            }
        }

        if (isset($_POST['semester'])) {
            $semesterNumber = $_POST['semester'];

            // Fetch units based on selected course and semester
            $unitSql = "SELECT * FROM Units WHERE CourseID = (SELECT CourseID FROM Courses WHERE CourseName = ?) AND SemesterNumber = ?";
            $unitStmt = $conn->prepare($unitSql);
            $unitStmt->bind_param("si", $courseName, $semesterNumber);
            $unitStmt->execute();
            $unitResult = $unitStmt->get_result();
            if ($unitResult->num_rows > 0) {
                while ($row = $unitResult->fetch_assoc()) {
                    $units[] = $row;
                }
            }
        }
    }
}

// Fetch students based on selected criteria
if (isset($_POST['category'], $_POST['course'], $_POST['intake'], $_POST['year'], $_POST['semester'], $_POST['unit'])) {
    $categoryId = $_POST['category'];
    $courseName = $_POST['course'];
    $intake = $_POST['intake'];
    $year = $_POST['year'];
    $semesterNumber = $_POST['semester'];
    $unitCode = $_POST['unit'];

    // Fetch the CategoryName based on CategoryID
    $categorySql = "SELECT CategoryName FROM Categories WHERE CategoryID = ?";
    $categoryStmt = $conn->prepare($categorySql);
    $categoryStmt->bind_param("i", $categoryId);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();

    if ($categoryResult->num_rows > 0) {
        $categoryRow = $categoryResult->fetch_assoc();
        $categoryName = $categoryRow['CategoryName'];
    } else {
        die('Invalid Category ID');
    }

    
    // Fetch students based on selected category, course, intake, and year
    $studentSql = "
        SELECT s.AdmissionNumber, CONCAT(s.FirstName, ' ', s.LastName) AS StudentName
        FROM Students s
        WHERE s.CategoryName = ?
          AND s.CourseName = ?
          AND s.IntakeName = ?
          AND YEAR(s.RegistrationDate) = ?
    ";

    $studentStmt = $conn->prepare($studentSql);
    $studentStmt->bind_param("ssss", $categoryName, $courseName, $intake, $year);
    $studentStmt->execute();
    $studentResult = $studentStmt->get_result();

    if ($studentResult->num_rows > 0) {
        while ($row = $studentResult->fetch_assoc()) {
            $students[] = $row;
        }
    } else {
        echo "No students found for the selected criteria.";
    }

    // Initialize an array to store failed students
    $failedStudents = [];

    // Fetch marks for the specified unit and identify students who failed
    if (!empty($students)) {
        foreach ($students as $student) {
            $admissionNumber = $student['AdmissionNumber'];

            // Fetch marks for the student in the specified unit
            $marksSql = "SELECT total_marks FROM exam_marks WHERE admission_number = ? AND unit_code = ? AND category_name = ? AND course_name = ? AND year = ? AND semester = ?";
            $marksStmt = $conn->prepare($marksSql);
            $marksStmt->bind_param("ssssss", $admissionNumber, $unitCode, $categoryName, $courseName, $year, $semesterNumber);
            $marksStmt->execute();
            $marksResult = $marksStmt->get_result();

            if ($marksResult->num_rows > 0) {
                $marksRow = $marksResult->fetch_assoc();
                $totalMarks = $marksRow['total_marks'];

                if ($totalMarks < 40) {
                    // Add student to the failed list
                    $failedStudents[] = $student;
                }
            } else {
                // No marks found for this student in the specified unit
                $failedStudents[] = $student;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
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
            fetchOptions('../IKIGAI/admin/examinations/fetch_courses.php', { categoryId: categoryId }, function(data) {
                updateDropdown('course', data);
                document.getElementById('course').disabled = false;
                document.getElementById('semester').disabled = true;
                document.getElementById('unit').disabled = true;
                document.getElementById('year').disabled = true;
                document.getElementById('intake').disabled = true;
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('../IKIGAI/admin/examinations/fetch_years.php', { courseName: courseName }, function(data) {
                updateDropdown('year', data);
                document.getElementById('year').disabled = false;
                document.getElementById('semester').disabled = true;
                document.getElementById('unit').disabled = true;
                document.getElementById('intake').disabled = true;
            });
        }

        function handleYearChange() {
            const year = document.getElementById('year').value;
            fetchOptions('../IKIGAI/admin/examinations/fetch_intakes.php', { year: year }, function(data) {
                updateDropdown('intake', data);
                document.getElementById('intake').disabled = false;
                document.getElementById('semester').disabled = true;
                document.getElementById('unit').disabled = true;
            });
        }

        function handleIntakeChange() {
            const intake = document.getElementById('intake').value;
            const courseName = document.getElementById('course').value;
            fetchOptions('../IKIGAI/admin/examinations/fetch_semesters.php', { courseName: courseName, intake: intake }, function(data) {
                updateDropdown('semester', data);
                document.getElementById('semester').disabled = false;
                document.getElementById('unit').disabled = true;
            });
        }

        function handleSemesterChange() {
            const courseName = document.getElementById('course').value;
            const semesterNumber = document.getElementById('semester').value;
            fetchOptions('../IKIGAI/admin/examinations/fetch_units.php', { courseName: courseName, semesterNumber: semesterNumber }, function(data) {
                updateDropdown('unit', data);
                document.getElementById('unit').disabled = false;
            });
        }
    </script>

<style>
    form h2, h3 {
        color: #E39825;
    }

    label {
        margin-right: 5px;
        color: #3B2314;
        font-weight: bold;
    }

    select, button {
        width: 15%;
        padding: 8px;
        margin-top: 15px;
        border: 1px solid;
        border-radius: 5px;
        font-size: 14px;
        color: #fff;
    }

    select {
      
      color: black; 
  }

    button {
        background-color: #3B2314;
        color: white;
        border: none;
        cursor: pointer;
        margin-top: 6px;
        border-radius: 10px;
    }

    button:hover {
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
</style>

</head>
<body>
    <form method="POST" action="">
        <h2>Supp Attendance</h2>
        <label for="category">Category:</label>
        <select id="category" name="category" onchange="handleCategoryChange()" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['CategoryID']; ?>"><?php echo $cat['CategoryName']; ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="course">Course:</label>
        <select id="course" name="course" onchange="handleCourseChange()" disabled required>
            <option value="">Select Course</option>
        </select>
        
        <label for="year">Year:</label>
        <select id="year" name="year" onchange="handleYearChange()" disabled required>
            <option value="">Select Year</option>
            <?php foreach ($years as $year): ?>
                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
            <?php endforeach; ?>
        </select>
        
        <label for="intake">Intake:</label>
        <select id="intake" name="intake" onchange="handleIntakeChange()" disabled required>
            <option value="">Select Intake</option>
            <?php foreach ($intakes as $intake): ?>
                <option value="<?php echo $intake; ?>"><?php echo $intake; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="semester">Semester:</label>
        <select id="semester" name="semester" onchange="handleSemesterChange()" disabled required>
            <option value="">Select Semester</option>
        </select>
        
        <label for="unit">Unit:</label>
        <select id="unit" name="unit" required disabled>
            <option value="">Select Unit</option>
        </select>

        <button type="submit">Filter</button>
    </form>
    <?php if (isset($failedStudents) && !empty($failedStudents)): ?>
    <form method="POST" action="index.php?page=examinations/supAttendance_submit">
        <h2>Failed Students</h2>
        
        <!-- Single Date input for all students -->
        <label for="date">Date:</label>
        <input type="date" name="date" id="date" required>
        
        <table border="1">
            <tr>
                <th>Admission Number</th>
                <th>Student Name</th>
                <th>Mark Attendance</th>
            </tr>
            <?php foreach ($failedStudents as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['AdmissionNumber']); ?></td>
                    <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                    <td>
                        <input type="checkbox" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][present]" value="1">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][name]" value="<?php echo htmlspecialchars($student['StudentName']); ?>">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][course]" value="<?php echo htmlspecialchars($_POST['course']); ?>">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][year]" value="<?php echo htmlspecialchars($_POST['year']); ?>">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][intake]" value="<?php echo htmlspecialchars($_POST['intake']); ?>">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][semester]" value="<?php echo htmlspecialchars($_POST['semester']); ?>">
                        <input type="hidden" name="attendance[<?php echo htmlspecialchars($student['AdmissionNumber']); ?>][unit]" value="<?php echo htmlspecialchars($_POST['unit']); ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <input type="submit" value="Submit Attendance">
    </form>
<?php elseif (isset($failedStudents) && empty($failedStudents)): ?>
    <p>No students failed in the selected unit.</p>
<?php endif; ?>

</body>
</html>