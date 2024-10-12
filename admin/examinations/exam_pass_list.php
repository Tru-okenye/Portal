<?php
include_once __DIR__ . '/../../config/config.php';

// Fetch categories, years, and intake options
$categories = [];
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

// Initialize variables for form submission
$units = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = $_POST['course'] ?? '';
    $semesterNumber = $_POST['semester'] ?? '';

    if ($courseName && $semesterNumber) {
        // Fetch units based on course and semester
        $unitSql = "SELECT * FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?) AND SemesterNumber = ?";
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


// Initialize variables for students
$students = [];

// Fetch students based on selected category, course, intake, and year
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = $_POST['category'] ?? '';
    $courseName = $_POST['course'] ?? '';
    $intake = $_POST['intake'] ?? '';
    $year = $_POST['year'] ?? '';

    // Debugging: Output the selected criteria
    // echo "Selected Category ID: " . htmlspecialchars($categoryID) . "<br>";
    // echo "Selected Course: " . htmlspecialchars($courseName) . "<br>";
    // echo "Selected Intake: " . htmlspecialchars($intake) . "<br>";
    // echo "Selected Year: " . htmlspecialchars($year) . "<br>";

    if ($categoryID && $courseName && $intake && $year) {
        // Fetch the CategoryName based on CategoryID
        $categorySql = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
        $categoryStmt = $conn->prepare($categorySql);
        $categoryStmt->bind_param("i", $categoryID);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        if ($categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            $categoryName = $categoryRow['CategoryName'];
        } else {
            die('Invalid Category ID');
        }

        // Debugging: Output the category name
        // echo "Mapped Category Name: " . htmlspecialchars($categoryName) . "<br>";

        // Fetch students based on category name, course, intake, and year
        $studentSql = "
            SELECT s.AdmissionNumber, CONCAT(s.FirstName, ' ', s.LastName) AS StudentName
            FROM students s
            WHERE s.CategoryName = ?
              AND s.CourseName = ?
              AND s.IntakeName = ?
              AND YEAR(s.RegistrationDate) = ?
        ";

        $studentStmt = $conn->prepare($studentSql);

        // Check if the prepare statement was successful
        if ($studentStmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $studentStmt->bind_param("ssss", $categoryName, $courseName, $intake, $year);

        // Check if the bind_param was successful
        if ($studentStmt->execute() === false) {
            die('Execute failed: ' . htmlspecialchars($studentStmt->error));
        }

        $studentResult = $studentStmt->get_result();

        if ($studentResult->num_rows > 0) {
            while ($row = $studentResult->fetch_assoc()) {
                $students[] = $row;
            }
        } else {
            echo "No students found for the selected criteria.";
        }
    }
}



// Initialize variables for pass list and supplementary list
$passList = [];
$suppList = [];

// Fetch exam marks for the selected criteria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = $_POST['category'] ?? '';
    $courseName = $_POST['course'] ?? '';
    $intake = $_POST['intake'] ?? '';
    $year = $_POST['year'] ?? '';
    $semesterNumber = $_POST['semester'] ?? '';

    if ($categoryID && $courseName && $intake && $year && $semesterNumber) {
        // Fetch the CategoryName based on CategoryID
        $categorySql = "SELECT CategoryName FROM categories WHERE CategoryID = ?";
        $categoryStmt = $conn->prepare($categorySql);
        if ($categoryStmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $categoryStmt->bind_param("i", $categoryID);
        if ($categoryStmt->execute() === false) {
            die('Execute failed: ' . htmlspecialchars($categoryStmt->error));
        }
        $categoryResult = $categoryStmt->get_result();
        
        if ($categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            $categoryName = $categoryRow['CategoryName'];
        } else {
            die('Invalid Category ID');
        }

        // Fetch units based on course and semester
        $unitSql = "SELECT UnitCode, UnitName FROM units WHERE CourseID = (SELECT CourseID FROM courses WHERE CourseName = ?) AND SemesterNumber = ?";
        $unitStmt = $conn->prepare($unitSql);
        if ($unitStmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $unitStmt->bind_param("si", $courseName, $semesterNumber);
        if ($unitStmt->execute() === false) {
            die('Execute failed: ' . htmlspecialchars($unitStmt->error));
        }
        $unitResult = $unitStmt->get_result();

        $unitCodes = [];
        $unitNames = []; // To store unit names
        while ($row = $unitResult->fetch_assoc()) {
            $unitCodes[] = $row['UnitCode'];
            $unitNames[$row['UnitCode']] = $row['UnitName']; // Map unit code to unit name
        }

        // Check if students have marks in all units and pass the semester, and collect supplementary students
        foreach ($students as $student) {
            $admissionNumber = $student['AdmissionNumber'];

            // Fetch marks for the student
            $marksSql = "SELECT unit_code, total_marks FROM exam_marks WHERE admission_number = ? AND category_name = ? AND course_name = ? AND year = ? AND semester = ?";
            $marksStmt = $conn->prepare($marksSql);
            if ($marksStmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $marksStmt->bind_param("sssss", $admissionNumber, $categoryName, $courseName, $year, $semesterNumber);
            if ($marksStmt->execute() === false) {
                die('Execute failed: ' . htmlspecialchars($marksStmt->error));
            }
            $marksResult = $marksStmt->get_result();

            $studentMarks = [];
            while ($row = $marksResult->fetch_assoc()) {
                $studentMarks[$row['unit_code']] = $row['total_marks'];
            }

            $hasAllUnits = true;
            $isPassed = true;
            $failedUnits = [];

            foreach ($unitCodes as $unitCode) {
                if (!isset($studentMarks[$unitCode]) || $studentMarks[$unitCode] < 40) {
                    $hasAllUnits = false;
                    $isPassed = false;
                    $failedUnits[] = $unitNames[$unitCode] ?? 'Unknown Unit'; // Use unit name instead of code
                }
            }

            if ($hasAllUnits && $isPassed) {
                $passList[] = $student;
            } else {
                if (!empty($failedUnits)) {
                    // Add to supplementary list with the failed unit names
                    $suppList[] = [
                        'AdmissionNumber' => $student['AdmissionNumber'],
                        'StudentName' => $student['StudentName'],
                        'FailedUnits' => $failedUnits
                    ];
                }
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
    <title>Pass List and Supplementary Exams</title>
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
                document.getElementById('unitsTable').style.display = 'none'; // Hide units table
            });
        }

        function handleCourseChange() {
            const courseName = document.getElementById('course').value;
            fetchOptions('admin/examinations/fetch_semesters.php', { courseName: courseName }, function(data) {
                updateDropdown('semester', data);
                document.getElementById('semester').disabled = false;
            });
        }
    </script>
    <link rel="stylesheet" href="https://ikigaicollege.ac.ke/Portal/assets/css/exam_pass_list.css"> 
   
</head>
<body>
    <form method="post" action="">
        <h2>Pass List and Supplementary Exams</h2>
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

        <!-- Units Table -->
        <!-- <?php if (!empty($units)): ?>
            <table id="unitsTable">
                <thead>
                    <tr>
                        <th>Unit Name</th>
                        <th>Unit Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($unit['UnitName']); ?></td>
                            <td><?php echo htmlspecialchars($unit['UnitCode']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <table id="unitsTable" style="display: none;">
                <thead>
                    <tr>
                        <th>Unit Name</th>
                        <th>Unit Code</th>
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        <?php endif; ?> -->


        <!-- Students Table -->
        <!-- <?php if (!empty($students)): ?>
            <h3>Students</h3>
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>Admission Number</th>
                        <th>Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['AdmissionNumber']); ?></td>
                            <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>No students found for the selected criteria.</p>
        <?php endif; ?> -->



          <!-- Pass List Table -->
          <?php if (!empty($passList)): ?>
            <h3>Pass List</h3>
            <table id="passListTable">
                <thead>
                    <tr>
                        <th>Admission Number</th>
                        <th>Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($passList as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['AdmissionNumber']); ?></td>
                            <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>No students passed for the selected criteria.</p>
        <?php endif; ?>

<!-- Supplementary List Table -->
<?php if (!empty($suppList)): ?>
    <h3>Supplementary List</h3>
    <table id="suppListTable">
        <thead>
            <tr>
                <th>Admission Number</th>
                <th>Student Name</th>
                <th>Failed Units</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suppList as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['AdmissionNumber']); ?></td>
                    <td><?php echo htmlspecialchars($student['StudentName']); ?></td>
                    <td>
                        <?php echo implode(', ', $student['FailedUnits']); // List failed unit names ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <p>No students require supplementary exams for the selected criteria.</p>
<?php endif; ?>
    </form>
    <!-- Add this below the Supplementary List Table -->
<?php if (!empty($passList) || !empty($suppList)): ?>
    <form method="post" action="index.php?page=examinations/download_passlist">
        <input type="hidden" name="passList" value="<?php echo htmlspecialchars(json_encode($passList)); ?>">
        <input type="hidden" name="suppList" value="<?php echo htmlspecialchars(json_encode($suppList)); ?>">
        <input type="submit" value="Download Pass List" class="button">
    </form>
<?php endif; ?>
</body>
</html>
